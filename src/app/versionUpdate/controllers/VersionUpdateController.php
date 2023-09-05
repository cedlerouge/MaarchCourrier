<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Version Update Controller
 * @author dev@maarch.org
 */

namespace VersionUpdate\controllers;

use Docserver\controllers\DocserverController;
use Gitlab\Client;
use Group\controllers\PrivilegeController;
use Parameter\models\ParameterModel;
use Slim\Psr7\Request;
use SrcCore\http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use History\controllers\HistoryController;
use User\models\UserModel;
use Respect\Validation\Validator;
use SrcCore\controllers\LogsController;
use SrcCore\interfaces\AutoUpdateInterface;

class VersionUpdateController
{
    public const UPDATE_LOCK_FILE = "migration/updating.lck";
    public const ROUTES_WITHOUT_MIGRATION = ['GET/languages/{lang}', 'GET/authenticationInformations', 'GET/images'];

    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_update_control', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $client = new Client();
        $client->setUrl('https://labs.maarch.org/api/v4/');
        try {
            $tags = $client->tags()->all('12');
        } catch (\Exception $e) {
            return $response->withJson(['errors' => $e->getMessage()]);
        }

        $applicationVersion = CoreConfigModel::getApplicationVersion();

        if (empty($applicationVersion)) {
            return $response->withStatus(400)->withJson(['errors' => "Can't load package.json"]);
        }

        $currentVersion = $applicationVersion;
        $versions = explode('.', $currentVersion);

        if (count($versions) < 3) {
            return $response->withStatus(400)->withJson(['errors' => "Bad tag format : {$applicationVersion}"]);
        } else if (strlen($versions[0]) !== 4) {
            return $response->withStatus(400)->withJson(['errors' => "Bad tag format : {$applicationVersion}"]);
        }

        $currentVersionBranch = $versions[0];
        $currentMinorVersionTag = $versions[1];
        $currentPatchVersionTag = $versions[2];

        $availableMinorVersions = [];
        $availablePatchVersions = [];
        $availableMajorVersions = [];

        foreach ($tags as $value) {
            if (!preg_match("/^\d{4}\.\d\.\d+$/", $value['name'])) {
                continue;
            }
            $explodedValue = explode('.', $value['name']);

            $branchVersionTag = $explodedValue[0];
            $minorVersionTag = $explodedValue[1];
            $patchVersionTag = $explodedValue[2];


            if ($branchVersionTag > $currentVersionBranch) {
                $availableMajorVersions[] = $value['name'];
            } else if ($branchVersionTag == $currentVersionBranch && $minorVersionTag > $currentMinorVersionTag) {
                $availableMinorVersions[] = $value['name'];
            } else if ($minorVersionTag == $currentMinorVersionTag && $patchVersionTag > $currentPatchVersionTag) {
                $availablePatchVersions[] = $value['name'];
            }
        }

        natcasesort($availableMinorVersions);
        natcasesort($availableMajorVersions);
        natcasesort($availablePatchVersions);

        if (empty($availableMinorVersions)) {
            $lastAvailableMinorVersion = null;
        } else {
            $lastAvailableMinorVersion = end($availableMinorVersions);
        }

        if (empty($availableMajorVersions)) {
            $lastAvailableMajorVersion = null;
        } else {
            $lastAvailableMajorVersion = end($availableMajorVersions);
        }

        if (empty($availablePatchVersions)) {
            $lastAvailablePatchVersion = null;
        } else {
            $lastAvailablePatchVersion = end($availablePatchVersions);
        }

        $output = [];

        exec('git status --porcelain --untracked-files=no 2>&1', $output);

        return $response->withJson([
            'lastAvailableMinorVersion' => $lastAvailableMinorVersion,
            'lastAvailableMajorVersion' => $lastAvailableMajorVersion,
            'lastAvailablePatchVersion' => $lastAvailablePatchVersion,
            'currentVersion'            => $currentVersion,
            'canUpdate'                 => empty($output),
            'diffOutput'                => $output
        ]);
    }

    /**
        * @codeCoverageIgnore
    */
    public function update(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_update_control', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();
        $targetTag = $body['tag'];
        $targetTagVersions = explode('.', $targetTag);

        if (count($targetTagVersions) < 3) {
            return $response->withStatus(400)->withJson(['errors' => "Bad tag format : {$body['tag']}"]);
        }

        $targetVersionBranch = $targetTagVersions[0];
        $targetMinorVersionTag = $targetTagVersions[2];
        $targetMajorVersionTag = $targetTagVersions[1];

        $applicationVersion = CoreConfigModel::getApplicationVersion();
        if (empty($applicationVersion)) {
            return $response->withStatus(400)->withJson(['errors' => "Can't load package.json"]);
        }

        $currentVersion = $applicationVersion;

        $versions = explode('.', $currentVersion);
        $currentVersionBranch = $versions[0];
        $currentMinorVersionTag = $versions[2];
        $currentMajorVersionTag = $versions[1];

        if ($currentVersionBranch !== $targetVersionBranch) {
            return $response->withStatus(400)->withJson(['errors' => "Target branch version did not match with current branch"]);
        }

        if ($targetMajorVersionTag < $currentMajorVersionTag) {
            return $response->withStatus(400)->withJson(['errors' => "Can't update to previous / same major tag"]);
        } else if ($targetMajorVersionTag == $currentMajorVersionTag && $targetMinorVersionTag <= $currentMinorVersionTag) {
            return $response->withStatus(400)->withJson(['errors' => "Can't update to previous / same minor tag"]);
        }

        $output = [];
        exec('git status --porcelain --untracked-files=no 2>&1', $output);
        if (!empty($output)) {
            return $response->withStatus(400)->withJson(['errors' => 'Some files are modified. Can not update application', 'lang' => 'canNotUpdateApplication']);
        }

        $migrationFolder = DocserverController::getMigrationFolderPath();

        if (!empty($migrationFolder['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $migrationFolder['errors']]);
        }

        $actualTime = date("dmY-His");

        $output = [];
        exec('git fetch');
        exec("git checkout {$targetTag} 2>&1", $output, $returnCode);

        $log = "Application update from {$currentVersion} to {$targetTag}\nCheckout response {$returnCode} => " . implode(' ', $output) . "\n";
        file_put_contents("{$migrationFolder['path']}/updateVersion_{$actualTime}.log", $log, FILE_APPEND);

        if ($returnCode != 0) {
            return $response->withStatus(400)->withJson(['errors' => "Application update failed. Please check updateVersion.log at {$migrationFolder['path']}"]);
        }

        HistoryController::add([
            'tableName' => 'none',
            'recordId'  => $targetTag,
            'eventType' => 'UP',
            'userId'    => $GLOBALS['id'],
            'info'      => _APP_UPDATED_TO_TAG. ' : ' . $targetTag,
            'moduleId'  => null,
            'eventId'   => 'appUpdate',
        ]);

        return $response->withStatus(204);
    }

    private static function executeSQLUpdate(array $args)
    {
        ValidatorModel::arrayType($args, ['sqlFiles']);

        $migrationFolder = DocserverController::getMigrationFolderPath();

        if (!empty($migrationFolder['errors'])) {
            return ['errors' => $migrationFolder['errors']];
        }

        if (!empty($args['sqlFiles'])) {
            $config = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);

            $actualTime = date("dmY-His");
            $tablesToSave = '';
            foreach ($args['sqlFiles'] as $sqlFile) {
                $fileContent = file_get_contents($sqlFile);
                $explodedFile = explode("\n", $fileContent);
                foreach ($explodedFile as $key => $line) {
                    if (strpos($line, '--DATABASE_BACKUP') !== false) {
                        $lineNb = $key;
                    }
                }
                if (isset($lineNb)) {
                    $explodedLine = explode('|', $explodedFile[$lineNb]);
                    array_shift($explodedLine);
                    foreach ($explodedLine as $table) {
                        if (!empty($table)) {
                            $tablesToSave .= ' -t ' . trim($table);
                        }
                    }
                }
            }

            $execReturn = exec("pg_dump --dbname=\"postgresql://{$config['database'][0]['user']}:{$config['database'][0]['password']}@{$config['database'][0]['server']}:{$config['database'][0]['port']}/{$config['database'][0]['name']}\" {$tablesToSave} -a > \"{$migrationFolder['path']}/backupDB_maarchcourrier_{$actualTime}.sql\"", $output, $intReturn);
            if (!empty($execReturn)) {
                return ['errors' => 'Pg dump failed : ' . $execReturn];
            }

            foreach ($args['sqlFiles'] as $sqlFile) {
                $fileContent = file_get_contents($sqlFile);
                DatabaseModel::exec($fileContent);
                $fileName = explode('/', $sqlFile)[1];
                HistoryController::add([
                    'tableName' => 'none',
                    'recordId'  => $fileName,
                    'eventType' => 'UP',
                    'userId'    => $GLOBALS['id'],
                    'info'      => _DB_UPDATED_WITH_FILE. ' : ' . $fileName,
                    'moduleId'  => null,
                    'eventId'   => 'databaseUpdate',
                ]);
            }
        }

        return ['directoryPath' => "{$migrationFolder['path']}"];
    }

    public function updateSQLVersion(Request $request, Response $response)
    {
        $parameter = ParameterModel::getById(['select' => ['param_value_string'], 'id' => 'database_version']);

        $parameter = explode('.', $parameter['param_value_string']);

        if (count($parameter) < 2) {
            return $response->withStatus(400)->withJson(['errors' => "Bad format database_version"]);
        }

        $dbMinorVersion = (int)$parameter[2];

        $dbMajorVersion = (int)$parameter[1];

        $sqlFiles = array_diff(scandir('migration'), array('..', '.', '.gitkeep'));
        natcasesort($sqlFiles);
        $targetedSqlFiles = [];

        foreach ($sqlFiles as $key => $file) {
            $fileVersions = explode('.', $file);
            $fileMinorVersion = (int)$fileVersions[2];
            $fileMajorVersion = (int)$fileVersions[1];
            if ($fileMajorVersion > $dbMajorVersion || ($fileMajorVersion == $dbMajorVersion && $fileMinorVersion > $dbMinorVersion)) {
                if (!is_readable("migration/{$file}")) {
                    return $response->withStatus(400)->withJson(['errors' => "File migration/{$file} is not readable"]);
                }
                $targetedSqlFiles[] = "migration/{$file}";
            }
        }

        if (empty($GLOBALS['id'] ?? null)) {
            $user = UserModel::get([
                'select'    => ['id'],
                'where'     => ['mode = ? OR mode = ?'],
                'data'      => ['root_visible', 'root_invisible'],
                'limit'     => 1
            ]);
            $GLOBALS['id'] = $user[0]['id'];
        }

        if (!empty($targetedSqlFiles)) {
            $control = VersionUpdateController::executeSQLUpdate(['sqlFiles' => $targetedSqlFiles]);
            if (!empty($control['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
            }
            return $response->withJson(['success' => 'Database has been updated']);
        }

        return $response->withStatus(204);
    }

    public static function isMigrating(): bool
    {
        return file_exists(VersionUpdateController::UPDATE_LOCK_FILE);
    }

    public static function autoUpdateLauncher(Request $request, Response $response)
    {
        $availableFolders = VersionUpdateController::getAvailableFolders();
        if (!empty($availableFolders['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $availableFolders['errors']]);
        }

        if (empty($GLOBALS['id'] ?? null)) {
            $user = UserModel::get([
                'select'    => ['id'],
                'where'     => ['mode = ? OR mode = ?'],
                'data'      => ['root_visible', 'root_invisible'],
                'limit'     => 1
            ]);
            $GLOBALS['id'] = $user[0]['id'];
        }

        if (!empty($availableFolders['folders'])) {
            try {
                VersionUpdateController::executeTagFolderFiles($availableFolders['folders']);
            } catch (\Exception $e) {
                return $response->withStatus(400)->withJson(['errors' => $e->getMessage()]);
            }
            return $response->withJson(['success' => 'Database has been updated']);
        }

        return $response->withStatus(204);
    }

    /**
     * Get any tag folders that are superior than the current database version
     * @return  array  Return 'errors' for unexpected errors | Return 'folders' with the list of folders
     */
    public static function getAvailableFolders(): array
    {
        $parameter = ParameterModel::getById(['select' => ['param_value_string'], 'id' => 'database_version']);

        $parameter = explode('.', $parameter['param_value_string']);

        if (count($parameter) < 2) {
            return ['errors' => "Bad format database_version"];
        }

        $dbMajorVersion = (int)$parameter[0];
        $dbMinorVersion = (int)$parameter[1];
        $dbPatchVersion = (int)$parameter[2];

        $folderTags = array_diff(scandir('migration'), array('..', '.', '.gitkeep'));
        natcasesort($folderTags);
        $availableFolders = [];

        foreach ($folderTags as $folder) {
            $folderVersions = explode('.', $folder);
            $folderMajorVersion = (int)$folderVersions[0];
            $folderMinorVersion = (int)$folderVersions[1];
            $folderPatchVersion = (int)$folderVersions[2];

            if (
                $folderMajorVersion > $dbMajorVersion || 
                ($folderMajorVersion == $dbMajorVersion && $folderMinorVersion > $dbMinorVersion) || 
                ($folderMajorVersion == $dbMajorVersion && $folderMinorVersion == $dbMinorVersion && $folderPatchVersion > $dbPatchVersion)
            ) {
                if (is_dir("migration/$folder")) {
                    if (!is_readable("migration/$folder")) {
                        return ['errors' => "Folder 'migration/$folder' is not readable"];
                    }
                    if (count(array_diff(scandir("migration/$folder"), ['.', '..'])) == 0) {
                        return ['errors' => "Folder 'migration/$folder' is empty, no updates are found!"];
                    }
                    $availableFolders[] = "migration/$folder";
                }
            }
        }

        return ['folders' => $availableFolders];
    }

    /**
     * Central function to run different types of files. SQL or PHP
     * @param   array   $tagFolderList  A list of strings
     * @return  Throwable|true  Throwable errors | Return true when successful
     */
    public static function executeTagFolderFiles(array $tagFolderList)
    {
        if (!Validator::arrayType()->notEmpty()->validate($tagFolderList)) {
            throw new \Exception('$tagFolderList must be a non empty array of type string');
        }

        $migrationFolder = DocserverController::getMigrationFolderPath();
        if (!empty($migrationFolder['errors'])) {
            throw new \Exception($migrationFolder['errors']);
        }

        LogsController::add([
            'isTech'    => true,
            'moduleId'  => 'Version Update Controller',
            'eventId'   => "Update",
            'level'     => 'INFO',
            'eventType' => "Begging of the update..."
        ]);

        foreach ($tagFolderList as $tagFolder) {
            $tagVersion      = basename($tagFolder);
            $tagFoldersFiles = scandir($tagFolder);

            if (in_array($tagFoldersFiles, ['.gitkeep', '.', '..'])) {
                continue;
            }

            $sqlFilePath = "$tagFolder/$tagVersion.sql";
            $check = VersionUpdateController::executeTagSqlFile($sqlFilePath, $migrationFolder['path']);
            if (empty($check)) {
                // maybe reload dump db
                continue;
            }

            $sqlFileIndex = array_search("$tagVersion.sql", $tagFoldersFiles);
            if ($sqlFileIndex !== false) {
                unset($tagFoldersFiles[$sqlFileIndex]);
            }

            $runScriptsByTag = VersionUpdateController::runScriptsByTag($tagFoldersFiles, $tagVersion);

            ParameterModel::update(['id' => "database_version", 'param_value_string' => $tagVersion]);

            $info = "Result of {$runScriptsByTag['numberOfFiles']} migration files, success : {$runScriptsByTag['success']}, errors : {$runScriptsByTag['errors']}, rollback : {$runScriptsByTag['rollback']}";
            LogsController::add([
                'isTech'    => true,
                'moduleId'  => 'Version Update',
                'eventId'   => "Tag '{$tagVersion}'",
                'level'     => 'INFO',
                'eventType' => $info
            ]);
        }

        LogsController::add([
            'isTech'    => true,
            'moduleId'  => 'Version Update Controller',
            'eventId'   => "Update",
            'level'     => 'INFO',
            'eventType' => "End of the update"
        ]);

        return true;
    }

    /**
     * Main function to run sql files
     * @param   string  $sqlFilePath
     * @param   string  $docserverMigrationFolderPath
     * @return  Throwable|bool  Throwable errors | return true if postgresql dump and sql file executed with sucess or return false if postgresql dump faild
     */
    public static function executeTagSqlFile(string $sqlFilePath, string $docserverMigrationFolderPath): bool
    {
        if (!Validator::stringType()->notEmpty()->validate($sqlFilePath)) {
            throw new \Exception('$sqlFilePath must be a non empty string');
        }
        if (!Validator::stringType()->notEmpty()->validate($docserverMigrationFolderPath)) {
            throw new \Exception('$docserverMigrationFolderPath must be a non empty string');
        }

        if (file_exists($sqlFilePath)) {

            $config = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);
            $actualTime = date("dmY-His");
            $tablesToSave = '';

            $fileContent = file_get_contents($sqlFilePath);
            $explodedFile = explode("\n", $fileContent);

            foreach ($explodedFile as $key => $line) {
                if (strpos($line, '--DATABASE_BACKUP') !== false) {
                    $lineNb = $key;
                }
            }

            if (isset($lineNb)) {
                $explodedLine = explode('|', $explodedFile[$lineNb]);
                array_shift($explodedLine);

                foreach ($explodedLine as $table) {
                    if (!empty($table)) {
                        $tablesToSave .= ' -t ' . trim($table);
                    }
                }
            }

            $backupFile = $docserverMigrationFolderPath . "/backupDB_maarchcourrier_$actualTime.sql";
            $dbname = "postgresql://{$config['database'][0]['user']}:{$config['database'][0]['password']}@{$config['database'][0]['server']}:{$config['database'][0]['port']}/{$config['database'][0]['name']}";
            exec("pg_dump --dbname=\"$dbname\" $tablesToSave -a > \"$backupFile\"", $output, $intReturn);

            if ($intReturn != 0) {
                LogsController::add([
                    'isTech'    => true,
                    'moduleId'  => 'Version Update',
                    'level'     => 'CRITICAL',
                    'eventType' => '[executeTagSqlFile] : Postgresql dump failed : One or more backup tables does not exist OR the backup path is not reachable',
                    'eventId'   => 'Execute Update'
                ]);
                return false;
            }

            DatabaseModel::exec($fileContent);
            $fileName = explode('/', $sqlFilePath)[1];

            HistoryController::add([
                'tableName' => 'none',
                'recordId'  => $fileName,
                'eventType' => 'UP',
                'userId'    => $GLOBALS['id'],
                'info'      => _DB_UPDATED_WITH_FILE. ' : ' . $fileName,
                'moduleId'  => null,
                'eventId'   => 'databaseUpdate',
            ]);
        }

        return true;
    }

    /**
     * Main function to run php files
     * @param   array   $folderFiles
     * @param   string  $tagVersion
     * @return  Throwable|array Throwable errors | Array with 'numberOfFiles', 'success', 'errors' and 'rollback'
     */
    public static function runScriptsByTag(array $folderFiles, string $tagVersion): array
    {
        if (!Validator::arrayType()->notEmpty()->validate($folderFiles)) {
            throw new \Exception('$folderFiles must be a non empty array');
        }
        if (!Validator::stringType()->notEmpty()->validate($tagVersion)) {
            throw new \Exception('$tagVersion must be a non empty string');
        }

        $numberOfFiles = 0;
        $success = 0;
        $errors = 0;
        $rollback = 0;

        foreach ($folderFiles as $fileName) {
            if (in_array($fileName, ['.', '..'])) {
                continue;
            }

            $numberOfFiles++;
            $filePath = "migration/$tagVersion/$fileName";
            $migrationClass = require $filePath;

            if (empty($migrationClass instanceof AutoUpdateInterface)) {
                LogsController::add([
                    'isTech'    => true,
                    'moduleId'  => 'Version Update',
                    'eventId'   => 'Run Scripts By Tag',
                    'level'     => 'CRITICAL',
                    'eventType' => "Could not find 'AutoUpdateInterface' of an anonymous class from '$filePath'"
                ]);
                $errors++;
                continue;
            }

            try {
                $migrationClass->backup();
            } catch (\Exception $e) {
                LogsController::add([
                    'isTech'    => true,
                    'moduleId'  => 'Version Update',
                    'eventId'   => 'Run Scripts By Tag',
                    'level'     => 'CRITICAL',
                    'eventType' => "Throwable - BACKUP : " . $e->getMessage()
                ]);
                $errors++;
                continue;
            }

            try {
                $migrationClass->update();

                $success++;
            } catch (\Exception $e) {
                $logInfo = "Throwable - UPDATE : " . $e->getMessage();
                $errors++;

                try {
                    $migrationClass->rollback();
                    $rollback++;
                } catch (\Exception $e) {
                    $logInfo .= " | Throwable - ROLLBACK : " . $e->getMessage();
                }

                LogsController::add([
                    'isTech'    => true,
                    'moduleId'  => 'Version Update',
                    'eventId'   => 'Run Scripts By Tag',
                    'level'     => 'CRITICAL',
                    'eventType' => $logInfo
                ]);
                continue;
            }
        }

        return ['numberOfFiles' => $numberOfFiles, 'success' => $success, 'errors' => $errors, 'rollback' => $rollback];
    }
}