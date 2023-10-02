<?php
namespace Migration\_2301_2_0;

require 'vendor/autoload.php';

use SrcCore\interfaces\AutoUpdateInterface;
use VersionUpdate\controllers\VersionUpdateController;
use SrcCore\models\CoreConfigModel;
use SrcCore\controllers\PasswordController;
use Configuration\models\ConfigurationModel;
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Shipping\models\ShippingTemplateModel;
use SrcCore\controllers\LogsController;

class MigrateSecretKey implements AutoUpdateInterface
{
    private $backupFolderPath       = null;
    private $backupConfigFileName   = 'config.json.backup';
    private $logHeader      = "Migration de la clé privée";
    private $rollbackSteps  = [];

    public function backup(): void
    {
        try {
            $this->backupFolderPath = VersionUpdateController::getMigrationTagFolderPath('2301.2.0');

            if (file_exists($this->backupFolderPath . '/' . $this->backupConfigFileName)) {
                unlink($this->backupFolderPath . '/' . $this->backupConfigFileName);
            }

            $configPath = CoreConfigModel::getConfigPath();
            $config     = CoreConfigModel::getJsonLoaded(['path' => $configPath]);
            $config     = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            file_put_contents($this->backupFolderPath . '/' . $this->backupConfigFileName, $config);

            LogsController::add([
                'isTech'    => true,
                'moduleId'  => 'Migrate Secret Key',
                'level'     => 'INFO',
                'eventType' => $this->logHeader . " [backup] : Backup config '$configPath' to '" . $this->backupFolderPath . '/' . $this->backupConfigFileName . "'",
                'eventId'   => 'Execute Backup'
            ]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function update(): void
    {
        try {
            $configPath = CoreConfigModel::getConfigPath();
            $customConfig = CoreConfigModel::getJsonLoaded(['path' => $configPath]);

            if (empty($customConfig)) {
                throw new \Exception($this->logHeader . " [update] : configuration file '$configPath' not found.");
            }

            // Move vHost encrypt key to secret key file
            $vhostEncryptKey = $this->getVhostEncryptKey();

            $customConfigPath = explode('/', $configPath);
            array_pop($customConfigPath);
            $customConfigPath = implode('/', $customConfigPath);
            $secretKeyPath = getcwd() . '/' . $customConfigPath . '/mc_secret.key';

            if (!file_exists($secretKeyPath)) {
                file_put_contents($secretKeyPath, $vhostEncryptKey);
                LogsController::add([
                    'isTech'    => true,
                    'moduleId'  => 'Migrate Secret Key',
                    'level'     => 'INFO',
                    'eventType' => $this->logHeader . " [update] : Create secret key file at '$secretKeyPath'",
                    'eventId'   => 'Execute Update'
                ]);
            }

            $customConfig['config']['privateKeyPath'] = $secretKeyPath;
            file_put_contents($configPath, json_encode($customConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            $this->rollbackSteps['configFile'] = true;


            // Update the password encryption with new private key
            $result = $this->changeServerMailPassword($vhostEncryptKey);
            if (!empty($result['errors'])) {
                throw new \Exception($this->logHeader . " [update] : " . $result['errors']);
            }
            $this->rollbackSteps['serverMailPassword'] = true;
            
            $this->changeContactPasswords($vhostEncryptKey);
            $this->rollbackSteps['contactPasswords'] = true;

            $this->changeEntitiesExternalIdPasswords($vhostEncryptKey);
            $this->rollbackSteps['entitiesExternalIdPasswords'] = true;

            $result = $this->changeOutlookPasswords($vhostEncryptKey);
            if (!empty($result['errors'])) {
                throw new \Exception($this->logHeader . " [update] : " . $result['errors']);
            }
            $this->rollbackSteps['outlookPasswords'] = true;

            $this->changeShippingTemplateAccountPasswords($vhostEncryptKey);
            $this->rollbackSteps['shippingTemplateAccountPasswords'] = true;

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage() . ". Trace : " . $th->getTraceAsString());
        }
    }

    public function rollback(): void
    {
        try {
            // Rollback passwords (depending where the update function stopped)
            if (!empty($this->rollbackSteps['serverMailPassword'] ?? null)) {
                $this->undoServerMailPasswordChanges();
            }
            if (!empty($this->rollbackSteps['contactPasswords'] ?? null)) {
                $this->undoContactPasswordChanges();
            }
            if (!empty($this->rollbackSteps['entitiesExternalIdPasswords'] ?? null)) {
                $this->undoEntitiesExternalIdPasswordChanges();
            }
            if (!empty($this->rollbackSteps['outlookPasswords'] ?? null)) {
                $this->undoOutlookPasswordChanges();
            }
            if (!empty($this->rollbackSteps['shippingTemplateAccountPasswords'] ?? null)) {
                $this->undoShippingTemplateAccountPasswordChanges();
            }

            // Rollback config
            if (!empty($this->rollbackSteps['configFile'] ?? null)) {
                $configPath = CoreConfigModel::getConfigPath();
                $config = CoreConfigModel::getJsonLoaded(['path' => $configPath]);

                unlink($config['config']['privateKeyPath']);

                $configBackup   = CoreConfigModel::getJsonLoaded(['path' => $this->backupFolderPath . '/' . $this->backupConfigFileName]);
                $configBackup   = json_encode($configBackup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                file_put_contents($configPath, $configBackup);
            }
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Get Encrypted key from vHost
     * 
     * @deprecated  In version 2401.x.x or higher MaarchCourrier wont fetch encrypted key from the vHost configuration.
     */
    private function getVhostEncryptKey(): string
    {
        if (isset($_SERVER['MAARCH_ENCRYPT_KEY'])) {
            $encryptKey = $_SERVER['MAARCH_ENCRYPT_KEY'];
        } elseif (isset($_SERVER['REDIRECT_MAARCH_ENCRYPT_KEY'])) {
            $encryptKey = $_SERVER['REDIRECT_MAARCH_ENCRYPT_KEY'];
        } else {
            $encryptKey = "Security Key Maarch Courrier #2008";
        }

        return $encryptKey;
    }

    /**
     * Encrypt data using old cypher method
     * 
     * @param   string  $password  Data to encrypt
     */
    public static function oldEncrypt(string $password): string
    {
        $enc_key = CoreConfigModel::getEncryptKey();

        $cipher_method = 'AES-128-CTR';
        $enc_iv        = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
        $crypted_token = openssl_encrypt($password, $cipher_method, $enc_key, 0, $enc_iv) . "::" . bin2hex($enc_iv);

        return $crypted_token;
    }

    /**
     * Decrypt an encrypted data using old cypher method
     * 
     * @param   string  $encryptedPassword  Encrypted data
     * @param   string  $privateKey         Key for decryption
     * 
     * @return  array|string    ['errors' => string] | string
     */
    function oldDecrypt(string $encryptedPassword, string $privateKey)
    {
        $cipher_method = 'AES-128-CTR';

        $password = null;
        try {
            $cryptedPasswordParts = explode("::", $encryptedPassword);
            if (count($cryptedPasswordParts) !== 2) {
                return ['errors' => "Invalid format: cryptedPassword should contain two parts separated by '::'"];
            }
            list($crypted_token, $enc_iv) = $cryptedPasswordParts;

            $password = openssl_decrypt($crypted_token, $cipher_method, $privateKey, 0, hex2bin($enc_iv));
        } catch (\Throwable $th) {
            return ['errors' => $th->getMessage()];
        }

        return $password;
    }

    /**
     * Change Email Server password
     * 
     * @param   string  $oldEncryptKey
     * 
     * @return  array['errors'] | true
     */
    private function changeServerMailPassword(string $oldEncryptKey)
    {
        // Get server mail info
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_email_server']);
        if (empty($configuration)) {
            return ['errors' => 'Server Mail configuration is missing'];
        }

        // Change password encryption
        $configuration = json_decode($configuration['value'], true);

        $password = $this->oldDecrypt($configuration['password'], $oldEncryptKey);
        if (!empty($password['errors'])) {
            $this->rollbackSteps['configFile'] = true;
            return ['errors' => $password['errors']];
        }

        $configuration['password'] = PasswordController::encrypt(['dataToEncrypt' => $password]);

        // Update config
        ConfigurationModel::update([
            'set' => [
                'value' => json_encode($configuration)
            ],
            'where' => ['privilege = ?'],
            'data' => ['admin_email_server']
        ]);

        return true;
    }

    /**
     * Change Email Server password
     * 
     * @return  array['errors'] | true
     */
    private function undoServerMailPasswordChanges()
    {
        // Get server mail info
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_email_server']);
        if (empty($configuration)) {
            return ['errors' => 'Server Mail configuration is missing'];
        }

        // Change password encryption
        $configuration = json_decode($configuration['value'], true);

        $configuration['password'] = PasswordController::decrypt(['encryptedData' => $configuration['password']]);
        $configuration['password'] = $this->oldEncrypt($configuration['password']);

        // Update config
        ConfigurationModel::update([
            'set' => [
                'value' => json_encode($configuration)
            ],
            'where' => ['privilege = ?'],
            'data' => ['admin_email_server']
        ]);

        return true;
    }

    /**
     * Change Contact password for MAARCH 2 MAARCH
     * 
     * @param   string  $oldEncryptKey
     * 
     * @return  array['errors'] | true
     */
    function changeContactPasswords(string $oldEncryptKey)
    {
        // Get contacts info
        $contacts = ContactModel::get([
            'select'    => ['id', 'communication_means'],
            'where'     => ['communication_means IS NOT NULL', "external_id != '{}'"],
        ]);

        foreach ($contacts as $contact) {
            $communicationMeans = json_decode($contact['communication_means'], true);

            // Change contact password encryption
            if (!empty($communicationMeans['password'])) {
                $password = $this->oldDecrypt($communicationMeans['password'], $oldEncryptKey);
                if (!empty($password['errors'])) {
                    $this->rollbackSteps['configFile'] = true;
                    return ['errors' => $password['errors']];
                }
                $communicationMeans['password'] = PasswordController::encrypt(['dataToEncrypt' => $password]);

                // Update contact
                ContactModel::update([
                    'set' => [
                        'communication_means' => json_encode($communicationMeans)
                    ],
                    'where' => ['id = ?'],
                    'data'  => [$contact['id']]
                ]);
            }
        }

        return true;
    }

    /**
     * Change Contact password for MAARCH 2 MAARCH
     */
    function undoContactPasswordChanges(): void
    {
        // Get contacts info
        $contacts = ContactModel::get([
            'select'    => ['id', 'communication_means'],
            'where'     => ['communication_means IS NOT NULL', "external_id != '{}'"],
        ]);

        foreach ($contacts as $contact) {
            $communicationMeans = json_decode($contact['communication_means'], true);

            // Change contact password encryption
            if (!empty($communicationMeans['password'])) {
                $communicationMeans['password'] = PasswordController::decrypt(['encryptedData' => $communicationMeans['password']]);
                $communicationMeans['password'] = $this->oldEncrypt($communicationMeans['password']);

                // Update contact
                ContactModel::update([
                    'set' => [
                        'communication_means' => json_encode($communicationMeans)
                    ],
                    'where' => ['id = ?'],
                    'data'  => [$contact['id']]
                ]);
            }
        }
    }

    /**
     * Change Entities external passwords (alfresco, multigest)
     * 
     * @param   string  $oldEncryptKey
     * 
     * @return  array['errors'] | true
     */
    function changeEntitiesExternalIdPasswords(string $oldEncryptKey)
    {
        // Get entities info
        $entities = EntityModel::get([
            'select'    => ['id', 'external_id'],
            'where'     => ['external_id IS NOT NULL', "external_id != '{}'"],
        ]);

        foreach ($entities as $entity) {
            $externalId = json_decode($entity['external_id'], true);
            $needToUpdate = false;

            // Change alfresco and multigest password encryption
            if (!empty($externalId['alfresco'] ?? null)) {
                $password = $this->oldDecrypt($externalId['alfresco']['password'], $oldEncryptKey);
                if (!empty($password['errors'])) {
                    $this->rollbackSteps['configFile'] = true;
                    return ['errors' => $password['errors']];
                }

                $externalId['alfresco']['password'] = PasswordController::encrypt(['dataToEncrypt' => $password]);
                $needToUpdate = true;
            }
            if (!empty($externalId['multigest'] ?? null)) {
                $password = $this->oldDecrypt($externalId['multigest']['password'], $oldEncryptKey);
                if (!empty($password['errors'])) {
                    $this->rollbackSteps['configFile'] = true;
                    return ['errors' => $password['errors']];
                }

                $externalId['multigest']['password'] = PasswordController::encrypt(['dataToEncrypt' => $password]);
                $needToUpdate = true;
            }
            if (!empty($needToUpdate)) {
                // Update entity
                EntityModel::update([
                    'set' => [
                        'external_id' => json_encode($externalId)
                    ],
                    'where' => ['id = ?'],
                    'data'  => [$entity['id']]
                ]);
            }
        }

        return true;
    }

    /**
     * Change Entities external passwords (alfresco, multigest)
     */
    function undoEntitiesExternalIdPasswordChanges(): void
    {
        // Get entities info
        $entities = EntityModel::get([
            'select'    => ['id', 'external_id'],
            'where'     => ['external_id IS NOT NULL', "external_id != '{}'"],
        ]);

        foreach ($entities as $entity) {
            $externalId = json_decode($entity['external_id'], true);
            $needToUpdate = false;

            // Change alfresco and multigest password encryption
            if (!empty($externalId['alfresco'] ?? null)) {
                $externalId['alfresco']['password'] = PasswordController::decrypt(['encryptedData' => $externalId['alfresco']['password']]);
                $externalId['alfresco']['password'] = $this->oldEncrypt($externalId['alfresco']['password']);
                $needToUpdate = true;
            }
            if (!empty($externalId['multigest'] ?? null)) {
                $externalId['multigest']['password'] = PasswordController::decrypt(['encryptedData' => $externalId['multigest']['password']]);
                $externalId['multigest']['password'] = $this->oldEncrypt($externalId['multigest']['password']);
                $needToUpdate = true;
            }
            if (!empty($needToUpdate)) {
                // Update entity
                EntityModel::update([
                    'set' => [
                        'external_id' => json_encode($externalId)
                    ],
                    'where' => ['id = ?'],
                    'data'  => [$entity['id']]
                ]);
            }
        }
    }

    /**
     * Change Outlook connection information (tenantId, clientId and clientSecret)
     * 
     * @param   string  $oldEncryptKey
     * 
     * @return  array['errors'] | true
     */
    function changeOutlookPasswords(string $oldEncryptKey)
    {
        // Get addin outlook info
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_addin_outlook']);
        if (empty($configuration)) {
            return ['errors' => 'Addin Outlook configuration is missing'];
        }
        $needToUpdate = false;

        // Change tenantId, clientId and clientSecret encryption
        $configuration = json_decode($configuration['value'], true);

        if (!empty($configuration['tenantId'] ?? null)) {
            $password = $this->oldDecrypt($configuration['tenantId'], $oldEncryptKey);
            if (!empty($password['errors'])) {
                $this->rollbackSteps['configFile'] = true;
                return ['errors' => $password['errors']];
            }

            $configuration['tenantId'] = PasswordController::encrypt(['dataToEncrypt' => $password]);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientId'] ?? null)) {
            $password = $this->oldDecrypt($configuration['clientId'], $oldEncryptKey);
            if (!empty($password['errors'])) {
                $this->rollbackSteps['outlookPasswords'] = true;
                return ['errors' => $password['errors']];
            }

            $configuration['clientId'] = PasswordController::encrypt(['dataToEncrypt' => $password]);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientSecret'] ?? null)) {
            $password = $this->oldDecrypt($configuration['clientSecret'], $oldEncryptKey);
            if (!empty($password['errors'])) {
                $this->rollbackSteps['outlookPasswords'] = true;
                return ['errors' => $password['errors']];
            }

            $configuration['clientSecret'] = PasswordController::encrypt(['dataToEncrypt' => $password]);
            $needToUpdate = true;
        }

        if (!empty($needToUpdate)) {
            // Update config
            ConfigurationModel::update([
                'set' => [
                    'value' => json_encode($configuration)
                ],
                'where' => ['privilege = ?'],
                'data' => ['admin_addin_outlook']
            ]);
        }

        return true;
    }

    /**
     * Change Outlook connection information (tenantId, clientId and clientSecret)
     * 
     * @return  array['errors'] | true
     */
    function undoOutlookPasswordChanges()
    {
        // Get addin outlook info
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_addin_outlook']);
        if (empty($configuration)) {
            return ['errors' => 'Addin Outlook configuration is missing'];
        }
        $needToUpdate = false;

        // Change tenantId, clientId and clientSecret encryption
        $configuration = json_decode($configuration['value'], true);

        if (!empty($configuration['tenantId'] ?? null)) {
            $configuration['tenantId'] = PasswordController::decrypt(['encryptedData' => $configuration['tenantId']]);
            $configuration['tenantId'] = $this->oldEncrypt($configuration['tenantId']);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientId'] ?? null)) {
            $configuration['clientId'] = PasswordController::decrypt(['encryptedData' => $configuration['clientId']]);
            $configuration['clientId'] = $this->oldEncrypt($configuration['clientId']);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientSecret'] ?? null)) {
            $configuration['clientSecret'] = PasswordController::decrypt(['encryptedData' => $configuration['clientSecret']]);
            $configuration['clientSecret'] = $this->oldEncrypt($configuration['clientSecret']);
            $needToUpdate = true;
        }

        if (!empty($needToUpdate)) {
            // Update config
            ConfigurationModel::update([
                'set' => [
                    'value' => json_encode($configuration)
                ],
                'where' => ['privilege = ?'],
                'data' => ['admin_addin_outlook']
            ]);
        }

        return true;
    }

    /**
     * Change Shipphinh template account password
     * 
     * @param   string  $oldEncryptKey
     * 
     * @return  array['errors'] | true
     */
    function changeShippingTemplateAccountPasswords(string $oldEncryptKey)
    {
        $shippingTemplates = ShippingTemplateModel::get([
            'select' => ['id', 'account'],
            'where'  => ["account->>'password' IS NOT NULL"]
        ]);

        foreach ($shippingTemplates as $shippingTemplate) {
            $account = json_decode($shippingTemplate['account'], true);
            $needToUpdate = false;

            // Change users outlook password encryption
            if (!empty($account['password'] ?? null)) {
                $password = $this->oldDecrypt($account['password'], $oldEncryptKey);
                if (!empty($password['errors'])) {
                    $this->rollbackSteps['outlookPasswords'] = true;
                    return ['errors' => $password['errors']];
                }

                $account['password'] = PasswordController::encrypt(['dataToEncrypt' => $password]);
                $needToUpdate = true;
            }

            if (!empty($needToUpdate)) {
                // Update user
                ShippingTemplateModel::update([
                    'set' => [
                        'account' => json_encode($account)
                    ],
                    'where' => ['id = ?'],
                    'data'  => [$shippingTemplate['id']]
                ]);
            }
        }

        return true;
    }

    /**
     * Change Shipphinh template account password
     */
    function undoShippingTemplateAccountPasswordChanges(): void
    {
        $shippingTemplates = ShippingTemplateModel::get([
            'select' => ['id', 'account'],
            'where'  => ["account->>'password' IS NOT NULL"]
        ]);

        foreach ($shippingTemplates as $shippingTemplate) {
            $account = json_decode($shippingTemplate['account'], true);
            $needToUpdate = false;

            // Change users outlook password encryption
            if (!empty($account['password'] ?? null)) {
                $account['password'] = PasswordController::decrypt(['encryptedData' => $account['password']]);
                $account['password'] = $this->oldEncrypt($account['password']);
                $needToUpdate = true;
            }

            if (!empty($needToUpdate)) {
                // Update user
                ShippingTemplateModel::update([
                    'set' => [
                        'account' => json_encode($account)
                    ],
                    'where' => ['id = ?'],
                    'data'  => [$shippingTemplate['id']]
                ]);
            }
        }
    }
}
return MigrateSecretKey::class; // The file return the class name