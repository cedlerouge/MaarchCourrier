<?php
namespace Migration\_2301_2_0;

require 'vendor/autoload.php';

use SrcCore\interfaces\AutoUpdateInterface;
use VersionUpdate\controllers\VersionUpdateController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\PasswordModel;
use Configuration\models\ConfigurationModel;
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Shipping\models\ShippingTemplateModel;
use SrcCore\controllers\LogsController;

class MigrateSecretKey implements AutoUpdateInterface
{
    private $backupFolderPath = null;
    private $backupConfigFileName = 'config.json.backup';
    private $logHeader = "Migration de la clé privée";

    public function backup(): void
    {
        try {
            self::$backupFolderPath = VersionUpdateController::getMigrationTagFolderPath('2301.2.0');

            $configPath = CoreConfigModel::getConfigPath();
            $config     = CoreConfigModel::getJsonLoaded(['path' => $configPath]);
            $config     = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            file_put_contents(self::$backupFolderPath . '/' . self::$backupConfigFileName, $config);

            LogsController::add([
                'isTech'    => true,
                'moduleId'  => 'Version Update',
                'level'     => 'CRITICAL',
                'eventType' => self::$logHeader . " [backup] : Backup config '$configPath' to '" . self::$backupFolderPath . '/' . self::$backupConfigFileName . "'",
                'eventId'   => 'Execute Update'
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
                throw new \Exception(self::$logHeader . " [backup] : configuration file '$configPath' not found.");
            }

            $secretKeyPath = $configPath . 'mc_secret.key';
            if (!file_exists($secretKeyPath)) {
                throw new \Exception(self::$logHeader . " [backup] : file '$secretKeyPath' not found.");
            }

            $customConfig['config']['privateKeyPath'] = $secretKeyPath;
            file_put_contents($configPath, json_encode($customConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

            // Update the password encryption with new private key
            $oldEncryptKey = self::getOldEncryptKey();

            $result = self::changeEmailServerPassword($oldEncryptKey);
            if (!empty($result['errors'])) {
                throw new \Exception(self::$logHeader . " [backup] : " . $result['errors']);
            }
            
            self::changeContactPasswords($oldEncryptKey);

            self::changeEntitiesExternalIdPasswords($oldEncryptKey);

            $result = self::changeOutlookPasswords($oldEncryptKey);
            if (!empty($result['errors'])) {
                throw new \Exception(self::$logHeader . " [backup] : " . $result['errors']);
            }

            self::changeShippingTemplateAccountPasswords($oldEncryptKey);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function rollback(): void
    {
        try {
            // Rollback config
            // Rollback password

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Get Encrypted key from vHost
     * 
     * @deprecated  In version 2401.x.x or higher MaarchCourrier wont fetch encrypted key from the vHost configuration.
     */
    private function getOldEncryptKey(): string
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
     * Encrypt data
     * 
     * @param   string  $password  Data to encrypt
     */
    public static function encrypt(string $password): string
    {
        $enc_key = CoreConfigModel::getEncryptKey();

        $cipher_method = 'AES-128-CTR';
        $enc_iv        = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
        $crypted_token = openssl_encrypt($password, $cipher_method, $enc_key, 0, $enc_iv) . "::" . bin2hex($enc_iv);

        return $crypted_token;
    }

    /**
     * Decrypt an encrypted data
     * 
     * @param   string  $encryptedPassword  Encrypted data
     * @param   string  $privateKey         Key for decryption
     */
    function decrypt(string $encryptedPassword, string $privateKey): string
    {
        $cipher_method = 'AES-128-CTR';

        list($crypted_token, $enc_iv) = explode("::", $encryptedPassword);
        $password = openssl_decrypt($crypted_token, $cipher_method, $privateKey, 0, hex2bin($enc_iv));

        return $password;
    }

    /**
     * Change Email Server password
     * 
     * @param   string  $oldEncryptKey
     * 
     * @return  array['errors'] | true
     */
    private function changeEmailServerPassword(string $oldEncryptKey): array|bool
    {
        // Get server mail info
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_email_server']);
        if (empty($configuration)) {
            return ['errors' => 'Server Mail configuration is missing'];
        }

        // Change password encryption
        $configuration = json_decode($configuration, true);
        $configuration = $configuration['value'];
        $configuration['password'] = self::decrypt($configuration['password'], $oldEncryptKey);

        $configuration['password'] = PasswordModel::encrypt(['dataToEncrypt' => $configuration['password']]);

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
                $communicationMeans['password'] = self::decrypt($communicationMeans['password'], $oldEncryptKey);
                $communicationMeans['password'] = PasswordModel::encrypt(['dataToEncrypt' => $communicationMeans['password']]);

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
                $externalId['alfresco']['password'] = self::decrypt($externalId['alfresco']['password'], $oldEncryptKey);
                $externalId['alfresco']['password'] = PasswordModel::encrypt(['dataToEncrypt' => $externalId['alfresco']['password']]);
                $needToUpdate = true;
            }
            if (!empty($externalId['multigest'] ?? null)) {
                $externalId['multigest']['password'] = self::decrypt($externalId['multigest']['password'], $oldEncryptKey);
                $externalId['multigest']['password'] = PasswordModel::encrypt(['dataToEncrypt' => $externalId['multigest']['password']]);
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
    function changeOutlookPasswords(string $oldEncryptKey): array|bool
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
            $configuration['tenantId'] = self::decrypt($configuration['tenantId'], $oldEncryptKey);
            $configuration['tenantId'] = PasswordModel::encrypt(['dataToEncrypt' => $configuration['tenantId']]);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientId'] ?? null)) {
            $configuration['clientId'] = self::decrypt($configuration['clientId'], $oldEncryptKey);
            $configuration['clientId'] = PasswordModel::encrypt(['dataToEncrypt' => $configuration['clientId']]);
            $needToUpdate = true;
        }
        if (!empty($configuration['clientSecret'] ?? null)) {
            $configuration['clientSecret'] = self::decrypt($configuration['clientSecret'], $oldEncryptKey);
            $configuration['clientSecret'] = PasswordModel::encrypt(['dataToEncrypt' => $configuration['clientSecret']]);
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
                $account['password'] = self::decrypt($account['password'], $oldEncryptKey);
                $account['password'] = PasswordModel::encrypt($account['password']);
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
