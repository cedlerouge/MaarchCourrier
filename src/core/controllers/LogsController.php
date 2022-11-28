<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Logs Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace SrcCore\controllers;

use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\processors\LogProcessor;

// using Monolog version 2.6.0
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\MemoryUsageProcessor;

class LogsController
{

    /**
     * @return Logger
     */
    public static function initMonologLogger()
    {
        $logConfig = LogsController::getLogConfig();
        if (empty($logConfig)) {
            return ['code' => 400, 'errors' => "Log config not found!"];
        }

        $dateFormat = $logConfig['dateTimeFormat'];
        $output = $logConfig['lineFormat'];
        $formatter = new LineFormatter($output, $dateFormat);

        $streamHandler = new StreamHandler($logConfig['logTechnique']['file']);
        $streamHandler->setFormatter($formatter);

        $logger = new Logger($logConfig['customId']);
        $filterHandler = new FilterHandler($streamHandler, $logger->toMonologLevel($logConfig['logTechnique']['level']));
        $logger->pushHandler($filterHandler);

        $logger->pushProcessor(new ProcessIdProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        return $logger;
    }

    public static function getLogType($logType){
        $logConfig = LogsController::getLogConfig();
        return $logConfig[$logType] ?? false;
    }


    public static function getMonolevels()
    {
        return Logger::getLevels();
    }

    /**
     * @description Get log config
     * @return  array
     */
    public static function getLogConfig()
    {
        $path = null;
        $customId = CoreConfigModel::getCustomId() ?: null;
        if (!empty($customId) && file_exists("custom/{$customId}/config/config.json")) {
            $path = "custom/{$customId}/config/config.json";
        } elseif (file_exists('config/config.json')) {
            $path = 'config/config.json';
        } else {
            $path = 'config/config.json.default';
        }

        $logConfig = CoreConfigModel::getJsonLoaded(['path' => $path]);
        if (empty($logConfig['log'])) {
            return null;
        }
        $logConfig['log']['customId'] = $customId;
        return $logConfig['log'];
    }

    /**
     * @description Add log line
     * @param   array   $args
     * @return  void
     */
    public static function add(array $args)
    {
        $logConfig = LogsController::getLogConfig();
        if (empty($logConfig)) {
            return ['code' => 400, 'errors' => "Log config not found!"];
        }
        $logLine   = LogsController::prepareLogLine(['logConfig' => $logConfig, 'lineData' => $args]);

        if (!empty($args['isSql'])) {
            LogsController::logWithMonolog([
                'lineFormat'        => $logConfig['lineFormat'],
                'dateTimeFormat'   => $logConfig['dateTimeFormat'],
                'levelConfig'       => $logConfig['queries']['level'],
                'name'              => $logConfig['customId'] ?? 'SCRIPT',
                'path'              => $logConfig['queries']['file'],
                'level'             => $args['level'],
                'maxSize'           => LogsController::calculateFileSizeToBytes( $logConfig['queries']['maxFileSize']),
                'maxFiles'          => $logConfig['queries']['maxBackupFiles'],
                'line'              => $logLine,
                'extraData'         => $args['extraData'] ?? []
            ]);
            return;
        }

        LogsController::logWithMonolog([
            'lineFormat'        => $logConfig['lineFormat'],
            'dateTimeFormat'   => $logConfig['dateTimeFormat'],
            'levelConfig'       => empty($args['isTech']) ? $logConfig['logFonctionnel']['level'] : $logConfig['logTechnique']['level'],
            'name'              => $logConfig['customId'] ?? 'SCRIPT',
            'path'              => empty($args['isTech']) ? $logConfig['logFonctionnel']['file'] : $logConfig['logTechnique']['file'],
            'level'             => $args['level'],
            'maxSize'           => LogsController::calculateFileSizeToBytes(empty($args['isTech']) ? $logConfig['logFonctionnel']['maxFileSize'] : $logConfig['logTechnique']['maxFileSize']),
            'maxFiles'          => empty($args['isTech']) ? $logConfig['logFonctionnel']['maxBackupFiles'] : $logConfig['logTechnique']['maxBackupFiles'],
            'line'              => $logLine,
            'extraData'         => $args['extraData'] ?? []
        ]);
    }

    /**
     * @description Make log line
     * @param   array   $args
     * @return  string
     */
    public static function prepareLogLine(array $args)
    {
        $logLine = str_replace(
            [
                '%WHERE%',
                '%ID%',
                '%HOW%',
                '%USER%',
                '%WHAT%',
                '%ID_MODULE%',
                '%REMOTE_IP%'
            ],
            [
                $args['lineData']['tableName'] ?? '',
                $args['lineData']['recordId'] ?? '',
                $args['lineData']['eventType'] ?? '',
                $GLOBALS['login'] ?? '',
                $args['lineData']['eventId'] ?? '',
                $args['lineData']['moduleId'] ?? '',
                $_SERVER['REMOTE_ADDR'] ?? ''
            ],
            "[%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]"
        );
        if (!empty($args['lineData']['isSql'])) {
            $logLine  = empty($args['lineData']['sqlQuery']) ? '[]' : "[" . $args['lineData']['sqlQuery'] . "]";
            if (empty($args['lineData']['sqlData'])) {
                $logLine .= "[]";
            } elseif (is_array($args['lineData']['sqlData'])) {
                $logLine .= "[" . json_encode($args['lineData']['sqlData']) . "]";
            } else {
                $logLine .= "[" . $args['lineData']['sqlData'] . "]";
            }
            $logLine .= empty($args['lineData']['sqlException']) ? '[]' : "[" . $args['lineData']['sqlException'] . "]";
        }
        $logLine = TextFormatModel::htmlWasher($logLine);
        $logLine = TextFormatModel::removeAccent(['string' => $logLine]);
        return $logLine;
    }

    /**
     * @description     Write prepare log line with monolog
     * @param   array   $log
     * @return  void
     */
    private static function logWithMonolog(array $log)
    {
        ValidatorModel::notEmpty($log, ['lineFormat', 'dateTimeFormat', 'levelConfig', 'name', 'path', 'level', 'line']);
        ValidatorModel::stringType($log, ['lineFormat', 'dateTimeFormat', 'name', 'path', 'line']);
        ValidatorModel::intVal($log, ['maxSize', 'maxFiles']);

        if (Logger::toMonologLevel($log['level']) < Logger::toMonologLevel($log['levelConfig'])) {
            return;
        }
        LogsController::rotateLogFileBySize([
            'path'      => $log['path'],
            'maxSize'   => $log['maxSize'],
            'maxFiles'  => $log['maxFiles']
        ]);

        $dateFormat = $log['dateTimeFormat'];
        $output = $log['lineFormat'];
        $formatter = new LineFormatter($output, $dateFormat);

        $streamHandler = new StreamHandler($log['path']);
        $streamHandler->setFormatter($formatter);

        $logger = new Logger($log['name']);
        $filterHandler = new FilterHandler($streamHandler, $logger->toMonologLevel($log['level']));
        $logger->pushHandler($filterHandler);

        $logger->pushProcessor(new ProcessIdProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new LogProcessor($log['extraData']));

        switch ($log['level']) {
            case 'DEBUG':
                // Use for detailed debug information
                $logger->debug($log['line']);
                break;
            case 'INFO':
                // Use for user logs in, SQL logs
                $logger->info($log['line']);
                break;
            case 'NOTICE':
                // Use for uncommon events
                $logger->notice($log['line']);
                break;
            case 'WARNING':
                // Use for exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
                $logger->warning($log['line']);
                break;
            case 'ERROR':
                // Use for runtime errors
                $logger->error($log['line']);
                break;
            case 'CRITICAL':
                // Use for critical conditions. Example: Application component unavailable, unexpected exception.
                $logger->critical($log['line']);
                break;
            case 'ALERT':
                // Use for actions that must be taken immediately. Example: Entire website down, database unavailable, etc.
                $logger->alert($log['line']);
                break;
            case 'EMERGENCY':
                // Use for urgent alert.
                $logger->emergency($log['line']);
                break;
        }
        $logger->close();
    }

    /**
     * @description Create new log file based on size and number of files to keep, when file size is exceeded
     * @param   array   $file
     * @return  void
     */
    public static function rotateLogFileBySize(array $file)
    {
        ValidatorModel::notEmpty($file, ['path']);
        ValidatorModel::intVal($file, ['maxSize', 'maxFiles']);
        ValidatorModel::stringType($file, ['path']);

        if (file_exists($file['path']) && !empty($file['maxSize']) && $file['maxSize'] > 0 && filesize($file['path']) > $file['maxSize']) {
            $path_parts = pathinfo($file['path']);
            $pattern = $path_parts['dirname'] . '/' . $path_parts['filename'] . "-%d." . $path_parts['extension'];

            // delete last file
            $fn = sprintf($pattern, $file['maxFiles']);
            if (file_exists($fn)) { unlink($fn);}

            // shift file names (add '-%index' before the extension)
            if (!empty($file['maxFiles'])) {
                for ($i = $file['maxFiles'] - 1; $i > 0; $i--) {
                    $fn = sprintf($pattern, $i);
                    if(file_exists($fn)) { 
                        rename($fn, sprintf($pattern, $i + 1)); 
                    }
                }
            }
            rename($file['path'], sprintf($pattern, 1));
        }
    }

    /**
     * @description Convert File size to KB
     * @param   string   $value     The size + prefix (of 2 characters)
     * @return  int
     */
    public static function calculateFileSizeToBytes($value)
    {
		$maxFileSize = null;
		$numpart = substr($value,0, strlen($value) -2);
		$suffix = strtoupper(substr($value, -2));

		switch($suffix) {
			case 'KB': $maxFileSize = (int)((int)$numpart * 1024); break;
			case 'MB': $maxFileSize = (int)((int)$numpart * 1024 * 1024); break;
			case 'GB': $maxFileSize = (int)((int)$numpart * 1024 * 1024 * 1024); break;
			default:
				if (is_numeric($value)) {
					$maxFileSize = (int)$value;
				}
		}
		return $maxFileSize;
	}
}
