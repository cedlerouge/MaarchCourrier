<?php

require 'vendor/autoload.php';

use SrcCore\interfaces\AutoUpdateInterface;
use SrcCore\models\CoreConfigModel;

return new class implements AutoUpdateInterface
{
    private static $testConfigPath = 'config/config.json.backup';
    private static $originalConfigPath = 'config/config.json';

    public static function backup(): void
    {
        try {
            $config = CoreConfigModel::getJsonLoaded(['path' => self::$originalConfigPath]);
            file_put_contents(self::$testConfigPath, json_encode($config), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public static function update(): void
    {
        try {
            $config = CoreConfigModel::getJsonLoaded(['path' => self::$originalConfigPath]);
            $config['PhpUnitTest'] = [
                'hello'     => 'world',
                'maarch'    => 'courrier'
            ];

            // simulate en error
            file_put_contents(self::$testConfigPath_, json_encode($config), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public static function rollback(): void
    {
        try {
            $config = CoreConfigModel::getJsonLoaded(['path' => self::$testConfigPath]);
            unlink(self::$testConfigPath);
            file_put_contents(self::$originalConfigPath, json_encode($config), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
};