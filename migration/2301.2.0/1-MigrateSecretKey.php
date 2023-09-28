<?php
namespace Migration\_2301_2_0;

require 'vendor/autoload.php';

use SrcCore\interfaces\AutoUpdateInterface;
use VersionUpdate\controllers\VersionUpdateController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\PasswordModel;
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
            self::$backupFolderPath = VersionUpdateController::getMigrationTagFolderPath('2301.2.0');

            if (file_exists(self::$backupFolderPath . '/' . self::$backupConfigFileName)) {
                unlink(self::$backupFolderPath . '/' . self::$backupConfigFileName);
            }

            $configPath = CoreConfigModel::getConfigPath();
            $config     = CoreConfigModel::getJsonLoaded(['path' => $configPath]);
            $config     = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            file_put_contents(self::$backupFolderPath . '/' . self::$backupConfigFileName, $config);

            LogsController::add([
                'isTech'    => true,
                'moduleId'  => 'Migrate Secret Key',
                'level'     => 'CRITICAL',
                'eventType' => self::$logHeader . " [backup] : Backup config '$configPath' to '" . self::$backupFolderPath . '/' . self::$backupConfigFileName . "'",
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

            if (!file_exists($customConfig)) {
                throw new \Exception(self::$logHeader . " [update] : configuration file '$configPath' not found.");
            }

            // Move vHost encrypt key to secret key file
            $vhostEncryptKey = self::getVhostEncryptKey();

            $secretKeyPath = $configPath . 'mc_secret.key';
            if (!file_exists($secretKeyPath)) {
                file_put_contents($secretKeyPath, $vhostEncryptKey);
                LogsController::add([
                    'isTech'    => true,
                    'moduleId'  => 'Migrate Secret Key',
                    'level'     => 'INFO',
                    'eventType' => self::$logHeader . " [update] : Create secret key file at '$secretKeyPath'",
                    'eventId'   => 'Execute Update'
                ]);
            }

            $customConfig['config']['privateKeyPath'] = $secretKeyPath;
            file_put_contents($configPath, json_encode($customConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            self::$rollbackSteps['configFile'] = true;


            // Update the password encryption with new private key
            $result = self::changeServerMailPassword($vhostEncryptKey);
            if (!empty($result['errors'])) {
                throw new \Exception(self::$logHeader . " [update] : " . $result['errors']);
            }
            self::$rollbackSteps['serverMailPassword'] = true;
            
            self::changeContactPasswords($vhostEncryptKey);
            self::$rollbackSteps['contactPasswords'] = true;

            self::changeEntitiesExternalIdPasswords($vhostEncryptKey);
            self::$rollbackSteps['entitiesExternalIdPasswords'] = true;

            $result = self::changeOutlookPasswords($vhostEncryptKey);
            if (!empty($result['errors'])) {
                throw new \Exception(self::$logHeader . " [update] : " . $result['errors']);
            }
            self::$rollbackSteps['outlookPasswords'] = true;

            self::changeShippingTemplateAccountPasswords($vhostEncryptKey);
            self::$rollbackSteps['shippingTemplateAccountPasswords'] = true;

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function rollback(): void
    {
        try {
            $privateKeyData = null;

            // Get private key data, so we can decrypt the reset.
            $configPath     = CoreConfigModel::getConfigPath();
            $config         = CoreConfigModel::getJsonLoaded(['path' => $configPath]);
            $privateKeyData = file_get_contents($config['config']['privateKeyPath']);

            // Rollback passwords (depending where the update function stopped)
            if (!empty(self::$rollbackSteps['serverMailPassword'] ?? null)) {
                self::undoServerMailPasswordChanges($privateKeyData);
            }
            if (!empty(self::$rollbackSteps['contactPasswords'] ?? null)) {
                self::undoContactPasswordChanges($privateKeyData);
            }
            if (!empty(self::$rollbackSteps['entitiesExternalIdPasswords'] ?? null)) {
                self::undoEntitiesExternalIdPasswordChanges($privateKeyData);
            }
            if (!empty(self::$rollbackSteps['outlookPasswords'] ?? null)) {
                self::undoOutlookPasswordChanges($privateKeyData);
            }
            if (!empty(self::$rollbackSteps['shippingTemplateAccountPasswords'] ?? null)) {
                self::undoShippingTemplateAccountPasswordChanges($privateKeyData);
            }

            // Rollback config
            if (!empty(self::$rollbackSteps['configFile'] ?? null)) {
                unlink($config['config']['privateKeyPath']);
                $configBackup   = CoreConfigModel::getJsonLoaded(['path' => self::$backupFolderPath . '/' . self::$backupConfigFileName]);
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
     */
    function oldDecrypt(string $encryptedPassword, string $privateKey): string
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
        $configuration = json_decode($configuration, true);
        $configuration = $configuration['value'];

        $password = self::oldDecrypt($configuration['password'], $oldEncryptKey);
        if (!empty($password['errors'])) {
            self::$rollbackSteps['configFile'] = true;
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
     * @param   string  $newEncryptKey
     * 
     * @return  array['errors'] | true
     */
    private function undoServerMailPasswordChanges(string $newEncryptKey)
    {
        // Get server mail info
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_email_server']);
        if (empty($configuration)) {
            return ['errors' => 'Server Mail configuration is missing'];
        }

        // Change password encryption
        $configuration = json_decode($configuration, true);
        $configuration = $configuration['value'];

        $password = PasswordController::decrypt($configuration['password'], $newEncryptKey);
        if (!empty($password['errors'])) {
            self::$rollbackSteps['configFile'] = true;
            return ['errors' => $password['errors']];
        }

        $configuration['password'] = self::oldEncrypt($password);

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
     */
    function changeContactPasswords(string $oldEncryptKey): void
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
                $communicationMeans['password'] = self::oldDecrypt($communicationMeans['password'], $oldEncryptKey);
                $communicationMeans['password'] = PasswordController::encrypt(['dataToEncrypt' => $communicationMeans['password']]);

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
     * Change Contact password for MAARCH 2 MAARCH
     * 
     * @param   string  $newEncryptKey
     */
    function undoContactPasswordChanges(string $newEncryptKey): void
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
                $communicationMeans['password'] = PasswordController::decrypt($communicationMeans['password'], $newEncryptKey);
                $communicationMeans['password'] = self::oldEncrypt($communicationMeans['password']);

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
     */
    function changeEntitiesExternalIdPasswords(string $oldEncryptKey): void
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
                $externalId['alfresco']['password'] = self::oldDecrypt($externalId['alfresco']['password'], $oldEncryptKey);
                $externalId['alfresco']['password'] = PasswordController::encrypt(['dataToEncrypt' => $externalId['alfresco']['password']]);
                $needToUpdate = true;
            }
            if (!empty($externalId['multigest'] ?? null)) {
                $externalId['multigest']['password'] = self::oldDecrypt($externalId['multigest']['password'], $oldEncryptKey);
                $externalId['multigest']['password'] = PasswordController::encrypt(['dataToEncrypt' => $externalId['multigest']['password']]);
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
     * Change Entities external passwords (alfresco, multigest)
     * 
     * @param   string  $newEncryptKey
     */
    function undoEntitiesExternalIdPasswordChanges(string $newEncryptKey): void
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
                $externalId['alfresco']['password'] = PasswordController::decrypt($externalId['alfresco']['password'], $newEncryptKey);
                $externalId['alfresco']['password'] = self::oldEncrypt($externalId['alfresco']['password']);
                $needToUpdate = true;
            }
            if (!empty($externalId['multigest'] ?? null)) {
                $externalId['multigest']['password'] = PasswordController::decrypt($externalId['multigest']['password'], $newEncryptKey);
                $externalId['multigest']['password'] = self::oldEncrypt($externalId['multigest']['password']);
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
        $configuration = json_decode($configuration, true);
        $configuration = $configuration['value'];

        if (!empty($configuration['tenantId'] ?? null)) {
            $configuration['tenantId'] = self::oldDecrypt($configuration['tenantId'], $oldEncryptKey);
            $configuration['tenantId'] = PasswordController::encrypt(['dataToEncrypt' => $configuration['tenantId']]);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientId'] ?? null)) {
            $configuration['clientId'] = self::oldDecrypt($configuration['clientId'], $oldEncryptKey);
            $configuration['clientId'] = PasswordController::encrypt(['dataToEncrypt' => $configuration['clientId']]);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientSecret'] ?? null)) {
            $configuration['clientSecret'] = self::oldDecrypt($configuration['clientSecret'], $oldEncryptKey);
            $configuration['clientSecret'] = PasswordController::encrypt(['dataToEncrypt' => $configuration['clientSecret']]);
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
     * @param   string  $newEncryptKey
     * 
     * @return  array['errors'] | true
     */
    function undoOutlookPasswordChanges(string $newEncryptKey)
    {
        // Get addin outlook info
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_addin_outlook']);
        if (empty($configuration)) {
            return ['errors' => 'Addin Outlook configuration is missing'];
        }
        $needToUpdate = false;

        // Change tenantId, clientId and clientSecret encryption
        $configuration = json_decode($configuration, true);
        $configuration = $configuration['value'];

        if (!empty($configuration['tenantId'] ?? null)) {
            $configuration['tenantId'] = PasswordController::decrypt($configuration['tenantId'], $newEncryptKey);
            $configuration['tenantId'] = self::oldEncrypt($configuration['tenantId']);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientId'] ?? null)) {
            $configuration['clientId'] = PasswordController::decrypt($configuration['clientId'], $newEncryptKey);
            $configuration['clientId'] = self::oldEncrypt($configuration['clientId']);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientSecret'] ?? null)) {
            $configuration['clientSecret'] = PasswordController::decrypt($configuration['clientSecret'], $newEncryptKey);
            $configuration['clientSecret'] = self::oldEncrypt($configuration['clientSecret']);
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
     */
    function changeShippingTemplateAccountPasswords(string $oldEncryptKey): void
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
                $account['password'] = self::oldDecrypt($account['password'], $oldEncryptKey);
                $account['password'] = PasswordController::encrypt($account['password']);
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

    /**
     * Change Shipphinh template account password
     * 
     * @param   string  $newEncryptKey
     */
    function undoShippingTemplateAccountPasswordChanges(string $newEncryptKey): void
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
                $account['password'] = PasswordController::decrypt($account['password'], $newEncryptKey);
                $account['password'] = self::oldEncrypt($account['password']);
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
