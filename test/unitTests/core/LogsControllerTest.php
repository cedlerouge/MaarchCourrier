<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionsControllerTest
* @author  dev <dev@maarch.org>
* @ingroup core
*/

use PHPUnit\Framework\TestCase;

class LogsControllerTest extends TestCase
{
    private static $generalConfigPath = null;
    private static $generalConfigOriginal = null;

    protected function setUp(): void
    {
        self::$generalConfigPath = (file_exists("config/config.json") ? "config/config.json" : "config/config.json.default");
        
        $generalConfig = file_get_contents(self::$generalConfigPath);
        $generalConfig = json_decode($generalConfig, true);
        self::$generalConfigOriginal = $generalConfig;

        $generalConfig['log']['logFonctionnel']['file'] = '/tmp/fonctionnel.log';
        $generalConfig['log']['logTechnique']['file'] = '/tmp/technique.log';
        $generalConfig['log']['queries']['file'] = '/tmp/queries.log';
        file_put_contents(self::$generalConfigPath, json_encode($generalConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function testCheckOriginalConfigIsNotEmpty()
    {
        $this->assertNotEmpty(self::$generalConfigOriginal);
    }

    public function testGetLogConfig()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();
        $this->assertNotEmpty($logConfig);
        $this->assertIsArray($logConfig);
        $this->assertSame($logConfig['logFonctionnel']['file'], '/tmp/fonctionnel.log');
        $this->assertSame($logConfig['logTechnique']['file'], '/tmp/technique.log');
        $this->assertSame($logConfig['queries']['file'], '/tmp/queries.log');
    }

    public function testPrepareLogLine()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'moduleId'  => 'LogModuleId',
            'level'     => 'INFO',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ];

        $logLine = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);

        $this->assertNotEmpty($logLine);
        $this->assertSame("[LogTableName][LogId][LogEvent][" . $GLOBALS['login'] . "][This is a test message][LogModuleId][" . $_SERVER['REMOTE_ADDR'] . "]", $logLine);
    }

    public function testPrepareLogLineSqlSimple()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isSql'         => true,
            'level'         => 'INFO',
            'sqlQuery'      => 'SELECT * FROM logsController',
        ];
        
        $logLine = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);
        $this->assertNotEmpty($logLine);
        $this->assertSame("[SELECT * FROM logsController][][]", $logLine);
    }

    public function testPrepareLogLineSqlWithParamsData()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isSql'         => true,
            'level'         => 'INFO',
            'sqlQuery'      => 'SELECT * FROM logsController WHERE id = ? AND moduleTest = ?',
            'sqlData'       => [10, "LogModuleId"]
        ];
        
        $logLine = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);
        $this->assertNotEmpty($logLine);
        $this->assertSame("[SELECT * FROM logsController WHERE id = ? AND moduleTest = ?][[10,\"LogModuleId\"]][]", $logLine);
    }

    public function testPrepareLogLineSqlWithException()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isSql'         => true,
            'level'         => 'INFO',
            'sqlQuery'      => 'SELECT * FROM logsController WHERE id = ? AND moduleTest = ?',
            'sqlData'       => [10, "LogModuleId"],
            'sqlException'  => "Any sql Exception error goes here..."
        ];
        
        $logLine = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);
        $this->assertNotEmpty($logLine);
        $this->assertSame("[SELECT * FROM logsController WHERE id = ? AND moduleTest = ?][[10,\"LogModuleId\"]][Any sql Exception error goes here...]", $logLine);
    }
    
    public function testLogFonctionnel()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'moduleId'  => 'LogModuleId',
            'level'     => 'ERROR',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ];

        $logsController->add($lineData);
        $logMessage = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);

        // check output
        $this->assertFileExists($logConfig['logFonctionnel']['file'], "Le fichier LogFonctionnel n'est pas créé : " . $logConfig['logFonctionnel']['file']);
        $logFileOutput = file_get_contents($logConfig['logFonctionnel']['file']);

        $this->assertNotEmpty($logFileOutput);
        $this->assertIsInt(strpos($logFileOutput, "[" . getmypid() . "]"));
        $this->assertIsInt(strpos($logFileOutput, "[SCRIPT]"));
        $this->assertIsInt(strpos($logFileOutput, "[ERROR]"));
        $this->assertIsInt(strpos($logFileOutput, "[$logMessage]"));
        $this->assertIsInt(strpos($logFileOutput, "processId"));
        $this->assertIsInt(strpos($logFileOutput, "extraData"));
        $this->assertIsInt(strpos($logFileOutput, "memory_usage"));

        unlink($logConfig['logFonctionnel']['file']);
        $this->assertFileDoesNotExist($logConfig['logFonctionnel']['file'], "File '" . $logConfig['logFonctionnel']['file'] . "' exists");
    }

    public function testLogTechnique()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isTech'    => true,
            'moduleId'  => 'LogModuleId',
            'level'     => 'ERROR',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ];

        $logsController->add($lineData);
        $logMessage = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);

        // check output
        $this->assertFileExists($logConfig['logTechnique']['file'], "Le fichier logTechnique n'est pas créé : " . $logConfig['logTechnique']['file']);
        $logFileOutput = file_get_contents($logConfig['logTechnique']['file']);

        $this->assertNotEmpty($logFileOutput);
        $this->assertIsInt(strpos($logFileOutput, "[" . getmypid() . "]"));
        $this->assertIsInt(strpos($logFileOutput, "[SCRIPT]"));
        $this->assertIsInt(strpos($logFileOutput, "[ERROR]"));
        $this->assertIsInt(strpos($logFileOutput, "[$logMessage]"));
        $this->assertIsInt(strpos($logFileOutput, "processId"));
        $this->assertIsInt(strpos($logFileOutput, "extraData"));
        $this->assertIsInt(strpos($logFileOutput, "memory_usage"));

        unlink($logConfig['logTechnique']['file']);
        $this->assertFileDoesNotExist($logConfig['logTechnique']['file'], "File '" . $logConfig['logTechnique']['file'] . "' exists");
    }

    public function testLogQueries()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isSql'         => true,
            'level'         => 'ERROR',
            'sqlQuery'      => 'SELECT * FROM logsController WHERE id = ? AND moduleTest = ?',
            'sqlData'       => [10, "LogModuleId"],
            'sqlException'  => "Any sql Exception error goes here..."
        ];

        $logsController->add($lineData);
        $logMessage = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);

        // check output
        $this->assertFileExists($logConfig['queries']['file'], "Le fichier queries n'est pas créé : " . $logConfig['queries']['file']);
        $logFileOutput = file_get_contents($logConfig['queries']['file']);

        $this->assertNotEmpty($logFileOutput);
        $this->assertIsInt(strpos($logFileOutput, "[" . getmypid() . "]"));
        $this->assertIsInt(strpos($logFileOutput, "[SCRIPT]"));
        $this->assertIsInt(strpos($logFileOutput, "[ERROR]"));
        $this->assertIsInt(strpos($logFileOutput, "[$logMessage]"));
        $this->assertIsInt(strpos($logFileOutput, "processId"));
        $this->assertIsInt(strpos($logFileOutput, "extraData"));
        $this->assertIsInt(strpos($logFileOutput, "memory_usage"));

        unlink($logConfig['queries']['file']);
        $this->assertFileDoesNotExist($logConfig['queries']['file'], "File '" . $logConfig['queries']['file'] . "' exists");
    }

    public function testLogLevels()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isTech'    => true,
            'moduleId'  => 'LogModuleId',
            'level'     => 'ERROR',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ];

        $logsController->add($lineData);
        $logMessage = $logsController->prepareLogLine(['logConfig' => $logConfig, 'lineData' => $lineData]);
        $logsController->add([
            'isTech'    => true,
            'moduleId'  => 'LogModuleId',
            'level'     => 'DEBUG',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ]);
        $logsController->add([
            'isTech'    => true,
            'moduleId'  => 'LogModuleId',
            'level'     => 'ALERT',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ]);

        // check output
        $this->assertFileExists($logConfig['logTechnique']['file'], "Le fichier logTechnique n'est pas créé : " . $logConfig['logTechnique']['file']);
        $logFileOutput = file_get_contents($logConfig['logTechnique']['file']);

        $this->assertNotEmpty($logFileOutput);
        $this->assertIsInt(strpos($logFileOutput, "[" . getmypid() . "]"));
        $this->assertIsInt(strpos($logFileOutput, "[SCRIPT]"));
        $this->assertIsInt(strpos($logFileOutput, "[ALERT]"));
        $this->assertIsInt(strpos($logFileOutput, "[ERROR]"));
        $this->assertFalse(strpos($logFileOutput, "[DEBUG]"));
        $this->assertIsInt(strpos($logFileOutput, "[$logMessage]"));
        $this->assertIsInt(strpos($logFileOutput, "processId"));
        $this->assertIsInt(strpos($logFileOutput, "extraData"));
        $this->assertIsInt(strpos($logFileOutput, "memory_usage"));

        unlink($logConfig['logTechnique']['file']);
        $this->assertFileDoesNotExist($logConfig['logTechnique']['file'], "File '" . $logConfig['logTechnique']['file'] . "' exists");
    }

    public function testConvertMaxFileSizeToBytes()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $this->assertNotEmpty($logConfig['logFonctionnel']['maxFileSize']);
        $this->assertIsInt($logsController->setMaxFileSize($logConfig['logFonctionnel']['maxFileSize']));
        $this->assertSame(10485760, $logsController->setMaxFileSize($logConfig['logFonctionnel']['maxFileSize']));

        $this->assertNotEmpty($logConfig['logTechnique']['maxFileSize']);
        $this->assertIsInt($logsController->setMaxFileSize($logConfig['logTechnique']['maxFileSize']));
        $this->assertSame(10485760, $logsController->setMaxFileSize($logConfig['logTechnique']['maxFileSize']));

        $this->assertNotEmpty($logConfig['queries']['maxFileSize']);
        $this->assertIsInt($logsController->setMaxFileSize($logConfig['queries']['maxFileSize']));
        $this->assertSame(10485760, $logsController->setMaxFileSize($logConfig['queries']['maxFileSize']));
    }

    public function testRotateLogFileBySize()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isTech'    => true,
            'moduleId'  => 'LogModuleId',
            'level'     => 'CRITICAL',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ];

        $logsController->add($lineData);
        $logsController->rotateLogFileBySize([
            'path'      => $logConfig['logTechnique']['file'],
            'maxSize'   => (filesize($logConfig['logTechnique']['file']) - 10),
            'maxFiles'  => (int)$logConfig['logTechnique']['maxBackupFiles']
        ]);
        $logsController->add($lineData);

        $path_parts = pathinfo($logConfig['logTechnique']['file']);
        $newFilePath = $path_parts['dirname'] . '/' . $path_parts['filename'] . "-1." . $path_parts['extension'];

        $this->assertFileExists($logConfig['logTechnique']['file'], "Le fichier logTechnique n'est pas créé : " . $logConfig['logTechnique']['file']);
        $this->assertFileExists($newFilePath, "Le fichier logTechnique backup n'est pas créé : $newFilePath");

        unlink($newFilePath);
        unlink($logConfig['logTechnique']['file']);
        $this->assertFileDoesNotExist($newFilePath, "File '$newFilePath' exists");
        $this->assertFileDoesNotExist($logConfig['logTechnique']['file'], "File '" . $logConfig['logTechnique']['file'] . "' exists");
    }

    public function testRotateLogFileByMaxFiles()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        $lineData = [
            'isTech'    => true,
            'moduleId'  => 'LogModuleId',
            'level'     => 'CRITICAL',
            'tableName' => 'LogTableName',
            'recordId'  => 'LogId',
            'eventType' => 'LogEvent',
            'eventId'   => 'This is a test message'
        ];

        $filesToDelete = [];
        for ($index=0; $index < ((int)$logConfig['logTechnique']['maxBackupFiles'] + 3); $index++) { 
            $logsController->add($lineData);
            $logsController->rotateLogFileBySize([
                'path'      => $logConfig['logTechnique']['file'],
                'maxSize'   => (filesize($logConfig['logTechnique']['file']) - 10),
                'maxFiles'  => (int)$logConfig['logTechnique']['maxBackupFiles']
            ]);
            $logsController->add($lineData);

            $path_parts = pathinfo($logConfig['logTechnique']['file']);
            $newFilePath  = $path_parts['dirname'] . '/';
            $newFilePath .= ($index == 0 ? $path_parts['filename'] . "." : $path_parts['filename'] . "-$index.");
            $newFilePath .= $path_parts['extension'];

            if ($index <= (int)$logConfig['logTechnique']['maxBackupFiles']) {
                $this->assertFileExists($newFilePath, "Le fichier logTechnique n'existe pas : $newFilePath");
                $filesToDelete[] = $newFilePath;
            } else {
                $this->assertFileDoesNotExist($newFilePath, "Le fichier logTechnique existe : $newFilePath");
            }
        }

        foreach ($filesToDelete as $value) {
            unlink($value);
            $this->assertFileDoesNotExist($newFilePath, "Le fichier logTechnique existe : $value");
        }
    }

    protected function tearDown(): void
    {
        file_put_contents(self::$generalConfigPath, json_encode(self::$generalConfigOriginal, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}