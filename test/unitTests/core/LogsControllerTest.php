<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   LogsControllerTest
* @author  dev <dev@maarch.org>
* @ingroup core
*/

use PHPUnit\Framework\TestCase;

class LogsControllerTest extends TestCase
{
    private static $generalConfigPath = null;
    private static $generalConfigOriginal = null;
    private static $filesToDelete = [];

    protected function setUp(): void
    {
        self::$generalConfigPath = (file_exists("config/config.json") ? "config/config.json" : "config/config.json.default");
        
        $generalConfig = file_get_contents(self::$generalConfigPath);
        $generalConfig = json_decode($generalConfig, true);
        self::$generalConfigOriginal = $generalConfig;

        $generalConfig['log']['logFonctionnel']['file'] = '/tmp/fonctionnel.log';
        $generalConfig['log']['logTechnique']['file'] = '/tmp/technique.log';
        $generalConfig['log']['queries']['file'] = '/tmp/queries.log';
        $filesToDelete = ['/tmp/fonctionnel.log', '/tmp/technique.log', '/tmp/queries.log'];

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

    public function testInitMologErrorIfNoConfigFound(): void
    {
        // Arrange
        $logConfig = [];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);

        // Assert
        $this->assertNotEmpty($logger);
        $this->assertSame(['code' => 400, 'errors' => "Log config is empty !"], $logger);
    }

    public function testInitMologErrorIfNoDateTimeIsFound(): void
    {
        // Arrange
        $logConfig = ["toto"];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);

        // Assert
        $this->assertNotEmpty($logger);
        $this->assertSame(['code' => 400, 'errors' => "dateTimeFormat is empty !"], $logger);
    }

    public function testInitMologErrorIfNoLineFormatIsFound(): void
    {
        // Arrange
        $logConfig = ["dateTimeFormat" => "d/m/Y H:i:s"];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);

        // Assert
        $this->assertNotEmpty($logger);
        $this->assertSame(['code' => 400, 'errors' => "lineFormat is empty !"], $logger);
    }

    public function testInitMologLoggerHasFilterHandlerWithPath(): void
    {
        // Arrange
        $logConfig = [
            "dateTimeFormat" => "d/m/Y H:i:s", 
            "lineFormat"     => "test", 
            "logTechnique"   => ["file" => "test/test", "level" => "INFO"],
            "customId"       => "myCustom"
            
        ];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);
        $handlers = $logger->getHandlers();
        
        // Assert
        $this->assertNotEmpty($logger);
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(Monolog\Handler\FilterHandler::class, $handlers[0]);
        
    }

    public function testInitMologLoggerTechniqueLogFileNotFound(): void
    {
        // Arrange
        $logConfig = [
            "dateTimeFormat" => "d/m/Y H:i:s", 
            "lineFormat"     => "test", 
            
        ];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);
        
        // Assert
        $this->assertNotEmpty($logger);
        
        $this->assertSame(['code' => 400, 'errors' => "file path of LogTechnique is empty !"], $logger);
    }

    public function testInitMologCustomIdNotFound(): void
    {
        // Arrange
        $logConfig = [
            "dateTimeFormat" => "d/m/Y H:i:s", 
            "lineFormat"     => "test", 
            "logTechnique"   => ["file" => "test/test", "level" => "INFO"],
            
        ];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);
        
        // Assert
        $this->assertNotEmpty($logger);
        $this->assertSame(['code' => 400, 'errors' => "customId not found !"], $logger);
    }

    public function testInitMologLoggerTechniqueLogLevelNotFound(): void
    {
        // Arrange
        $logConfig = [
            "dateTimeFormat" => "d/m/Y H:i:s", 
            "lineFormat"     => "test", 
            "logTechnique"   => ["file" => "test/test"],
            "customId"       => "myCustom"
            
        ];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);
        
        // Assert
        $this->assertNotEmpty($logger);
        
        $this->assertSame(['code' => 400, 'errors' => "level of LogTechnique is empty !"], $logger);

    }

    public function testInitMologLoggerHasProcessors(): void
    {
        // Arrange
        $logConfig = [
            "dateTimeFormat" => "d/m/Y H:i:s", 
            "lineFormat"     => "test", 
            "logTechnique"   => ["file" => "test/test", "level" => "INFO"],
            "customId"       => "myCustom"  
        ];
        
        // Act
        $logger = \srcCore\controllers\LogsController::initMonologLogger($logConfig);
        $processors = $logger->getProcessors();
        
        // Assert
        $this->assertNotEmpty($logger);
        $this->assertNotEmpty($processors);
        $this->assertCount(2, $processors);
        $this->assertInstanceOf(Monolog\Processor\MemoryUsageProcessor::class, $processors[0]);
        $this->assertInstanceOf(Monolog\Processor\ProcessIdProcessor::class, $processors[1]);
    }

    public function testGetLogTypeWrongLogType(): void 
    {
        // Arrange
        $logType = "toto";
        
        // Act
        $logConfig = \srcCore\controllers\LogsController::getLogType($logType);

        // Assert
        $this->assertNotEmpty($logConfig);
        $this->assertSame(['code' => 400, 'errors' => "Log config of type '$logType' is empty !"], $logConfig);
    }

    public function testGetLogTypeValidLogType(): void
    {
        // Arrange
        $logType = "logFonctionnel";

        // Act
        $logConfig = \srcCore\controllers\LogsController::getLogType($logType);

        // Assert
        $this->assertNotEmpty($logConfig);
        $this->assertNotEmpty($logConfig["file"]);
        $this->assertSame('/tmp/fonctionnel.log', $logConfig["file"]);
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
        $this->assertStringContainsString("[" . getmypid() . "]", $logFileOutput, "Log file output doesn't contains the current php process ID '[" . getmypid() . "]'");
        $this->assertStringContainsString("[SCRIPT]", $logFileOutput, "Log file output doesn't contains '[SCRIPT]'");
        $this->assertStringContainsString("[ERROR]", $logFileOutput, "Log file output doesn't contains the log level error '[ERROR]'");
        $this->assertStringContainsString("[$logMessage]", $logFileOutput, "Log file output doesn't contains the correct message '[$logMessage]'");
        $this->assertStringContainsString("processId", $logFileOutput, "Log file output doesn't contains processId attribute");
        $this->assertStringContainsString("extraData", $logFileOutput, "Log file output doesn't contains extraData object");
        $this->assertStringContainsString("memory_usage", $logFileOutput, "Log file output doesn't contains memory_usage attribute");
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
        $this->assertStringContainsString("[" . getmypid() . "]", $logFileOutput, "Log file output doesn't contains the current php process ID '[" . getmypid() . "]'");
        $this->assertStringContainsString("[SCRIPT]", $logFileOutput, "Log file output doesn't contains '[SCRIPT]'");
        $this->assertStringContainsString("[ERROR]", $logFileOutput, "Log file output doesn't contains the log level error '[ERROR]'");
        $this->assertStringContainsString("[$logMessage]", $logFileOutput, "Log file output doesn't contains the correct message '[$logMessage]'");
        $this->assertStringContainsString("processId", $logFileOutput, "Log file output doesn't contains processId attribute");
        $this->assertStringContainsString("extraData", $logFileOutput, "Log file output doesn't contains extraData object");
        $this->assertStringContainsString("memory_usage", $logFileOutput, "Log file output doesn't contains memory_usage attribute");
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
        $this->assertStringContainsString("[" . getmypid() . "]", $logFileOutput, "Log file output doesn't contains the current php process ID '[" . getmypid() . "]'");
        $this->assertStringContainsString("[SCRIPT]", $logFileOutput, "Log file output doesn't contains '[SCRIPT]'");
        $this->assertStringContainsString("[ERROR]", $logFileOutput, "Log file output doesn't contains the log level error '[ERROR]'");
        $this->assertStringContainsString("[$logMessage]", $logFileOutput, "Log file output doesn't contains the correct message '[$logMessage]'");
        $this->assertStringContainsString("processId", $logFileOutput, "Log file output doesn't contains processId attribute");
        $this->assertStringContainsString("extraData", $logFileOutput, "Log file output doesn't contains extraData object");
        $this->assertStringContainsString("memory_usage", $logFileOutput, "Log file output doesn't contains memory_usage attribute");
    }

    public function testLogFileOutputWithLogLevelError()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = \SrcCore\controllers\LogsController::getLogConfig();

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
        $this->assertStringContainsString("[" . getmypid() . "]", $logFileOutput, "Log file output doesn't contains the current php process ID '[" . getmypid() . "]'");
        $this->assertStringContainsString("[SCRIPT]", $logFileOutput, "Log file output doesn't contains '[SCRIPT]'");
        $this->assertStringContainsString("[ALERT]", $logFileOutput, "Log file output doesn't contains the log level alert '[ALERT]'");
        $this->assertStringContainsString("[ERROR]", $logFileOutput, "Log file output doesn't contains the log level error '[ERROR]'");
        $this->assertStringNotContainsString("[DEBUG]", $logFileOutput, "Log file output contains the log level debug '[DEBUG]'");
        $this->assertStringContainsString("[$logMessage]", $logFileOutput, "Log file output doesn't contains the correct message '[$logMessage]'");
        $this->assertStringContainsString("processId", $logFileOutput, "Log file output doesn't contains processId attribute");
        $this->assertStringContainsString("extraData", $logFileOutput, "Log file output doesn't contains extraData object");
        $this->assertStringContainsString("memory_usage", $logFileOutput, "Log file output doesn't contains memory_usage attribute");
    }

    /**
     * @dataProvider provideFileSizeData
     */
    public function testCalculateMaxFileSizeToBytes($input, $expectedOutput)
    {
        $logsController = new \SrcCore\controllers\LogsController();

        $this->assertNotEmpty($input);
        $bytes = $logsController->calculateFileSizeToBytes($input);
        $this->assertSame($expectedOutput, $bytes);
    }

    public function provideFileSizeData()
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        return [
            '10000 string value' => [
                "input"             => "10000",
                "expectedOutput"    => 10000
            ],
            '10000 int value' => [
                "input"             => 10000,
                "expectedOutput"    => 10000
            ],
            '1 Kilobyte' => [
                "input"             => "1KB",
                "expectedOutput"    => 1024
            ],
            '1 Megabyte' => [
                "input"             => "1MB",
                "expectedOutput"    => 1048576
            ],
            '1 Gigabyte' => [
                "input"             => "1GB",
                "expectedOutput"    => 1073741824
            ],
            '1 Terabyte' => [
                "input"             => "1TB",
                "expectedOutput"    => null
            ]
        ];
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
        self::$filesToDelete[] = $newFilePath;

        $this->assertFileExists($logConfig['logTechnique']['file'], "Le fichier logTechnique n'est pas créé : " . $logConfig['logTechnique']['file']);
        $this->assertFileExists($newFilePath, "Le fichier logTechnique backup n'est pas créé : $newFilePath");
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
                self::$filesToDelete[] = $newFilePath;
            } else {
                $this->assertFileDoesNotExist($newFilePath, "Le fichier logTechnique existe : $newFilePath");
            }
        }
    }

    protected function tearDown(): void
    {
        $logsController = new \SrcCore\controllers\LogsController();
        $logConfig = $logsController->getLogConfig();

        foreach (self::$filesToDelete as $filePath) {
            if (file_exists($logConfig['logFonctionnel']['file'])) {
                unlink($logConfig['logFonctionnel']['file']);
            }
        }
        file_put_contents(self::$generalConfigPath, json_encode(self::$generalConfigOriginal, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}