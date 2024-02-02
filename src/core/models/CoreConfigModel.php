<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Core Config Model
 * @author dev@maarch.org
 * @ingroup core
 */

namespace SrcCore\models;

use Configuration\models\ConfigurationModel;
use Exception;

class CoreConfigModel
{
    protected static $customId;

    public static function getCustomId()
    {
        // Use for script
        if (!empty($GLOBALS['customId'])) {
            self::$customId = $GLOBALS['customId'];
            return self::$customId;
        }

        if (self::$customId !== null) {
            return self::$customId;
        }

        if (!is_file('custom/custom.json') || empty($_SERVER['SCRIPT_NAME']) || empty($_SERVER['SERVER_ADDR'])) {
            self::$customId = '';
            return self::$customId;
        }

        $explodeUrl = explode('/', $_SERVER['SCRIPT_NAME']);

        $path = $explodeUrl[count($explodeUrl) - 3];

        $jsonFile = file_get_contents('custom/custom.json');
        $jsonFile = json_decode($jsonFile, true);
        foreach ($jsonFile as $value) {
            if (!empty($value['path']) && $value['path'] == $path) {
                self::$customId = $value['id'];
                return self::$customId;
            } elseif ($value['uri'] == $_SERVER['HTTP_HOST'] || ($_SERVER['HTTP_HOST'] == $_SERVER['SERVER_ADDR'] && $value['uri'] == $_SERVER['SERVER_ADDR'])) {
                self::$customId = $value['id'];
                return self::$customId;
            }
        }

        self::$customId = '';
        return self::$customId;
    }

    public static function getConfigPath(): string
    {
        $customId = CoreConfigModel::getCustomId();
        if (!empty($customId) && is_file("custom/{$customId}/config/config.json")) {
            $path = "custom/{$customId}/config/config.json";
        } else {
            $path = 'config/config.json';
        }

        return $path;
    }

    /**
     * @throws Exception
     */
    public static function getApplicationName()
    {
        static $applicationName;

        if ($applicationName !== null) {
            return $applicationName;
        }

        $file = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);

        if (!empty($file['config']['applicationName'])) {
            $applicationName = $file['config']['applicationName'];
            return $applicationName;
        }

        $applicationName = 'Maarch Courrier';
        return $applicationName;
    }

    public static function getApplicationVersion()
    {
        $file = file_get_contents('package.json');
        $file = json_decode($file, true);

        return $file['version'];
    }

    /**
     * @throws Exception
     */
    public static function getLanguage()
    {
        $availableLanguages = ['fr'];

        $file = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);

        if (!empty($file['config']['lang'])) {
            $lang = $file['config']['lang'];
            if (in_array($lang, $availableLanguages)) {
                return $lang;
            }
        }

        return 'fr';
    }

    public static function getCustomLanguage($aArgs = [])
    {
        $customId = CoreConfigModel::getCustomId();
        if (file_exists('custom/' . $customId . '/lang/lang-' . $aArgs['lang'] . '.ts')) {
            $fileContent = file_get_contents('custom/' . $customId . '/lang/lang-' . $aArgs['lang'] . '.ts');
            $fileContent = str_replace("\n", "", $fileContent);

            $strpos = strpos($fileContent, "=");
            $substr = substr(trim($fileContent), $strpos + 2, -1);

            $trimmed = rtrim($substr, ',}');
            $trimmed .= '}';
            return json_decode($trimmed);
        }

        return '';
    }

    /**
     * Get the timezone
     *
     * @return string
     * @throws Exception
     */
    public static function getTimezone(): string
    {
        $timezone = 'Europe/Paris';

        $file = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);

        if ($file) {
            if (!empty($file['config']['timezone'])) {
                $timezone = $file['config']['timezone'];
            }
        }

        return $timezone;
    }

    /**
     * Get the tmp dir
     *
     * @return string
     */
    public static function getTmpPath(): string
    {
        if (isset($_SERVER['MAARCH_TMP_DIR'])) {
            $tmpDir = $_SERVER['MAARCH_TMP_DIR'];
        } elseif (isset($_SERVER['REDIRECT_MAARCH_TMP_DIR'])) {
            $tmpDir = $_SERVER['REDIRECT_MAARCH_TMP_DIR'];
        } else {
            $tmpDir = sys_get_temp_dir();
        }

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755);
        }

        return $tmpDir . '/';
    }

    /**
     * Get the Encrypt Key
     *
     * @return string
     * @deprecated This function is deprecated and will be removed in future major versions.
     * Please use getEncryptKey() instead.
     *
     */
    public static function getOldEncryptKey(): string
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
     * @return bool
     * @deprecated This function will be removed in future major versions.
     * Please use getEncryptKey() instead.
     *
     */
    public static function useVhostEncryptKey(): bool
    {
        $configPath = CoreConfigModel::getConfigPath();
        $config = json_decode(file_get_contents($configPath), true)['config'];

        return !isset($config['privateKeyPath']) || empty($config['privateKeyPath']);
    }

    /**
     * Get the Encrypt Key
     *
     * @return string
     */
    public static function getEncryptKey(): string
    {
        $configPath = CoreConfigModel::getConfigPath();
        $config = json_decode(file_get_contents($configPath), true)['config'];

        $encryptKeyPath = $config['privateKeyPath'] ?? null;

        if (empty($encryptKeyPath)) {
            $encryptKey = CoreConfigModel::getOldEncryptKey();
        } elseif (!empty($encryptKeyPath) && is_file($encryptKeyPath) && is_readable($encryptKeyPath)) {
            $encryptKey = file_get_contents($encryptKeyPath);
            $encryptKey = trim($encryptKey);
        } else {
            $encryptKey = "Security Key Maarch Courrier 2008";
        }

        return $encryptKey;
    }

    /**
     * @return bool
     */
    public static function hasEncryptKeyChanged(): bool
    {
        $encryptKey = CoreConfigModel::getEncryptKey();

        return $encryptKey !== "Security Key Maarch Courrier #2008" && $encryptKey !== "Security Key Maarch Courrier 2008";
    }

    public static function getLibrariesDirectory()
    {
        if (isset($_SERVER['LIBRARIES_DIR'])) {
            $librariesDirectory = rtrim($_SERVER['LIBRARIES_DIR'], '/') . '/';
        } elseif (isset($_SERVER['REDIRECT_LIBRARIES_DIR'])) {
            $librariesDirectory = rtrim($_SERVER['REDIRECT_LIBRARIES_DIR'], '/') . '/';
        } else {
            $librariesDirectory = null;
        }

        return $librariesDirectory;
    }

    public static function getSetaPdfFormFillerLibrary()
    {
        $libDir = CoreConfigModel::getLibrariesDirectory();
        $libPath = null;

        if (!empty($libDir) && is_file($libDir . 'SetaPDF-FormFiller-Full/library/SetaPDF/Autoload.php')) {
            $libPath = $libDir . 'SetaPDF-FormFiller-Full/library/SetaPDF/Autoload.php';
        }
        return $libPath;
    }

    public static function getFpdiPdfParserLibrary()
    {
        $libDir = CoreConfigModel::getLibrariesDirectory();
        $libPath = null;

        if (!empty($libDir) && is_file($libDir . 'FPDI-PDF-Parser/src/autoload.php')) {
            $libPath = $libDir . 'FPDI-PDF-Parser/src/autoload.php';
        }
        return $libPath;
    }

    public static function getSetaSignFormFillerLibrary()
    {
        $libDir = CoreConfigModel::getLibrariesDirectory();
        $libPath = null;

        if (!empty($libDir)) {
            // old way (before use internal source)
            if (is_file($libDir . 'SetaPDF-FormFiller-Full/library/SetaPDF/Autoload.php')) {
                $libPath = $libDir . 'SetaPDF-FormFiller-Full/library/SetaPDF/Autoload.php';
            } else if (is_file($libDir . 'setapdf-formfiller-full/library/SetaPDF/Autoload.php')) {
                $libPath = $libDir . 'setapdf-formfiller-full/library/SetaPDF/Autoload.php';
            }
        }
        return $libPath;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getLoggingMethod(): array
    {
        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'config/login_method.xml']);

        $loggingMethod = [];
        if ($loadedXml) {
            foreach ($loadedXml->METHOD as $value) {
                if ((string)$value->ENABLED == 'true') {
                    $loggingMethod['id'] = (string)$value->ID;
                }
            }
        }

        return $loggingMethod;
    }

    public static function getMailevaConfiguration(): array
    {
        $mailevaConfig = ['enabled' => false];

        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_shippings']);
        if (!empty($configuration)) {
            $configuration = json_decode($configuration['value'], true);

            $mailevaConfig['enabled'] = $configuration['enabled'];
            $mailevaConfig['connectionUri'] = rtrim($configuration['authUri'], '/');
            $mailevaConfig['uri'] = rtrim($configuration['uri'], '/');

            if (!empty($mailevaConfig['uri']) && $mailevaConfig['uri'] == 'https://api.maileva.com') {
                $mailevaConfig['clientId'] = '69d315c2b3694accbce85f2871add37d';
                $mailevaConfig['clientSecret'] = 'caae36511f324acb9a3419b94ce9cbc6';
            }
            if (!empty($mailevaConfig['uri']) && $mailevaConfig['uri'] == 'https://api.sandbox.maileva.net') {
                $mailevaConfig['clientId'] = 'c42ca6698b5e4008b8ebf84e465ae216';
                $mailevaConfig['clientSecret'] = 'e49ab08848f543678287b5c8f7f79812';
            }
        }

        return $mailevaConfig;
    }

    /**
     * @throws Exception
     */
    public static function getXmlLoaded(array $args)
    {
        ValidatorModel::notEmpty($args, ['path']);
        ValidatorModel::stringType($args, ['path']);

        $customId = CoreConfigModel::getCustomId();

        if (!empty($customId) && is_file("custom/{$customId}/{$args['path']}")) {
            $path = "custom/{$customId}/{$args['path']}";
        } else {
            $path = $args['path'];
        }

        $xmlfile = null;
        if (file_exists($path)) {
            $xmlfile = simplexml_load_file($path);
        }

        return $xmlfile;
    }

    /**
     * @throws Exception
     */
    public static function getJsonLoaded(array $args)
    {
        ValidatorModel::notEmpty($args, ['path']);
        ValidatorModel::stringType($args, ['path']);

        $customId = CoreConfigModel::getCustomId();

        if (!empty($customId) && is_file("custom/{$customId}/{$args['path']}")) {
            $path = "custom/{$customId}/{$args['path']}";
        } else {
            $path = $args['path'];
        }

        $file = null;
        if (file_exists($path)) {
            $file = file_get_contents($path);
            $file = json_decode($file, true);
        }

        return $file;
    }

    /**
     * Database Unique Id Function
     *
     * @return string $uniqueId
     */
    public static function uniqueId(): string
    {
        $parts = explode('.', microtime(true));
        $sec = $parts[0];
        if (!isset($parts[1])) {
            $msec = 0;
        } else {
            $msec = $parts[1];
        }

        $uniqueId = str_pad(base_convert($sec, 10, 36), 6, '0', STR_PAD_LEFT);
        $uniqueId .= str_pad(base_convert($msec, 10, 16), 4, '0', STR_PAD_LEFT);
        $uniqueId .= str_pad(base_convert(mt_rand(), 10, 36), 6, '0', STR_PAD_LEFT);

        return $uniqueId;
    }

    /**
     * @param array $aArgs
     * @return array
     * @throws Exception
     */
    public static function getKeycloakConfiguration(array $aArgs = []): array
    {
        ValidatorModel::stringType($aArgs, ['customId']);

        $customId = CoreConfigModel::getCustomId();
        if (!empty($aArgs['customId'])) {
            $customId = $aArgs['customId'];
        }

        if (file_exists("custom/{$customId}/config/keycloakConfig.xml")) {
            $path = "custom/{$customId}/config/keycloakConfig.xml";
        } else {
            $path = 'config/keycloakConfig.xml';
        }

        $keycloakConfig = [];
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            if ($loadedXml) {
                $keycloakConfig['authServerUrl'] = (string)$loadedXml->AUTH_SERVER_URL;
                $keycloakConfig['realm'] = (string)$loadedXml->REALM;
                $keycloakConfig['clientId'] = (string)$loadedXml->CLIENT_ID;
                $keycloakConfig['clientSecret'] = (string)$loadedXml->CLIENT_SECRET;
                $keycloakConfig['redirectUri'] = (string)$loadedXml->REDIRECT_URI;
                $keycloakConfig['encryptionAlgorithm'] = (string)$loadedXml->ENCRYPTION_ALGORITHM;
                $keycloakConfig['encryptionKeyPath'] = (string)$loadedXml->ENCRYPTION_KEY_PATH;
                $keycloakConfig['encryptionKey'] = (string)$loadedXml->ENCRYPTION_KEY;
                $keycloakConfig['scope'] = (string)$loadedXml->SCOPE;

                if (empty($keycloakConfig['encryptionAlgorithm'])) {
                    $keycloakConfig['encryptionAlgorithm'] = null;
                }
                if (empty($keycloakConfig['encryptionKeyPath'])) {
                    $keycloakConfig['encryptionKeyPath'] = null;
                }
                if (empty($keycloakConfig['encryptionKey'])) {
                    $keycloakConfig['encryptionKey'] = null;
                }
            }
        }

        return $keycloakConfig;
    }

    /**
     * @param array $args
     * @return array
     * @throws Exception
     */
    public static function getColumns(array $args): array
    {
        ValidatorModel::notEmpty($args, ['table']);
        ValidatorModel::stringType($args, ['table']);

        return DatabaseModel::getColumns(['table' => $args['table']]);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function isEnableDocserverEncryption(): bool
    {
        $betaEncryptCheck = CoreConfigModel::getJsonLoaded(['path' => CoreConfigModel::getConfigPath()]);
        return $betaEncryptCheck['config']['enableDocserverEncryption'] ?? false;
    }
}
