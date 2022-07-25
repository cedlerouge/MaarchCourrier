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

// using Monolog version 2.6.0
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Formatter\LineFormatter;

class LogsController
{
    public static function add(array $args)
    {
        
        $logConfig = LogsController::getLogConfig();
        $logLine   = LogsController::prepareLogLine(['logConfig' => $logConfig, 'lineData' => $args]);

        if (!empty($args['isSql'])) {
            LogsController::logWithMonolog([
                'levelConfig'   => $logConfig['queries']['level'],
                'name'          => $logConfig['customId'] ?? 'SCRIPT',
                'path'          => $logConfig['queries']['file'],
                'level'         => $args['level'],
                'maxSize'       => $logConfig['queries']['maxFileSize'],
                'maxFiles'      => $logConfig['queries']['maxBackupFiles'],
                'line'          => $logLine
            ]);
            return;
        }

        LogsController::logWithMonolog([
            'levelConfig' => empty($args['isTech']) ? $logConfig['logFonctionnel']['level'] : $logConfig['logTechnique']['level'],
            'name'        => $logConfig['customId'] ?? 'SCRIPT',
            'path'        => empty($args['isTech']) ? $logConfig['logFonctionnel']['file'] : $logConfig['logTechnique']['file'],
            'level'       => $args['level'],
            'maxSize'     => LogsController::setMaxFileSize(empty($args['isTech']) ? $logConfig['logFonctionnel']['maxFileSize'] : $logConfig['logTechnique']['maxFileSize']),
            'maxFiles'    => empty($args['isTech']) ? $logConfig['logFonctionnel']['maxBackupFiles'] : $logConfig['logTechnique']['maxBackupFiles'],
            'line'        => $logLine
        ]);
    }

    private static function prepareLogLine(array $args)
    {
        $logLine = str_replace(
            [
                '%RESULT%',
                '%CODE_METIER%',
                '%WHERE%',
                '%ID%',
                '%HOW%',
                '%USER%',
                '%WHAT%',
                '%ID_MODULE%',
                '%REMOTE_IP%'
            ],
            [
                'OK',
                'MAARCH',
                $args['lineData']['tableName'],
                $args['lineData']['recordId'],
                $args['lineData']['eventType'],
                $GLOBALS['login'] ?? '',
                $args['lineData']['eventId'],
                $args['lineData']['moduleId'],
                $_SERVER['REMOTE_ADDR'] ?? ''
            ],
            "[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]"
        );
        if (!empty($args['lineData']['isSql'])) {
            $logLine  = empty($args['lineData']['sqlQuery']) ? '[]' : "[" . $args['lineData']['sqlQuery'] . "]";
            if (empty($args['lineData']['sqlData'])) {
                $logLine .= "[]";
            } elseif (is_array($args['lineData']['sqlData'])) {
                $logLine .= "[" . implode(', ', $args['lineData']['sqlData']) . "]";
            } else {
                $logLine .= "[" . $args['lineData']['sqlData'] . "]";
            }
            $logLine .= empty($args['lineData']['sqlException']) ? '[]' : "[" . $args['lineData']['sqlException'] . "]";
        }
        $logLine = TextFormatModel::htmlWasher($logLine);
        $logLine = TextFormatModel::removeAccent(['string' => $logLine]);
        return $logLine;
    }

    private static function getLogConfig()
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

        $logConfig = CoreConfigModel::getJsonLoaded(['path' => $path])['log'];
        if (empty($logConfig)) {
            $logConfig = [];
            $logConfig['enable']       = true;
            $logConfig['businessCode'] = 'MAARCH';
            $logConfig['logFonctionnel']['level']          = 'ERROR';
            $logConfig['logFonctionnel']['file']           = 'fonctionnel.log';
            $logConfig['logFonctionnel']['maxFileSize']    = '10MB';
            $logConfig['logFonctionnel']['maxBackupFiles'] = '10';
            $logConfig['logTechnique']['level']            = 'ERROR';
            $logConfig['logTechnique']['file']             = 'technique.log';
            $logConfig['logTechnique']['maxFileSize']      = '10MB';
            $logConfig['logTechnique']['maxBackupFiles']   = '10';
            $logConfig['queries']['level']          = 'ERROR';
            $logConfig['queries']['file']           = 'queries_error.log';
            $logConfig['queries']['maxFileSize']    = '10MB';
            $logConfig['queries']['maxBackupFiles'] = '10';
        }
        $logConfig['customId'] = $customId;
        return $logConfig;
    }

    protected static function logWithMonolog(array $log)
    {
        
        ValidatorModel::notEmpty($log, ['levelConfig', 'name', 'path', 'level', 'line']);
        ValidatorModel::stringType($log, ['name', 'path', 'line']);

        if (Logger::toMonologLevel($log['level']) < Logger::toMonologLevel($log['levelConfig'])) {
            return;
        }
        LogsController::rotateLogByFileSize([
            'path'      => $log['path'],
            'maxSize'   => $log['maxSize'],
            'maxFiles'  => $log['maxFiles']
        ]);

        // the default date format is "Y-m-d\TH:i:sP"
        $dateFormat = "d/m/Y H:i:s";
        // the default format -> "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "[%datetime%][%channel%][%level_name%][%message%]\n";
        $formatter = new LineFormatter($output, $dateFormat);

        $streamHandler = new StreamHandler($log['path']);
        $streamHandler->setFormatter($formatter);

        $logger = new Logger($log['name']);
        $filterHandler = new FilterHandler($streamHandler, $logger->toMonologLevel($log['level']));
        $logger->pushHandler($filterHandler);

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

    private static function rotateLogByFileSize(array $file)
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

    private static function setMaxFileSize(string $value)
    {
		$maxFileSize = null;
		$numpart = substr($value,0, strlen($value) -2);
		$suffix = strtoupper(substr($value, -2));

		switch($suffix) {
			case 'KB': $maxFileSize = (int)((int)$numpart * 1024); break;
			case 'MB': $maxFileSize = (int)((int)$numpart * 1024 * 1024); break;
			case 'GB': $maxFileSize = (int)((int)$numpart * 1024 * 1024 * 1024); break;
			default:
				if(is_numeric($value)) {
					$maxFileSize = (int)$value;
				}
		}
		return $maxFileSize;
	}
}
