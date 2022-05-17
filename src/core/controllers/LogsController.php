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
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Formatter\LineFormatter;

class LogsController
{
    public static function add(array $args){
        
        $logLine   = LogsController::prepareLogLine($args);
        $logConfig = LogsController::getLogConfig();

        if ($args['level'] == $logConfig['queries']['level']) {
            LogsController::logWithMonolog([
                'name'      => $logConfig['customId'] ?? 'SCRIPT',
                'path'      => $logConfig['queries']['file'],
                'level'     => "INFO",
                'maxSize'   => $logConfig['queries']['maxSize'],
                'maxFiles'  => $logConfig['queries']['maxFiles'],
                'line'      => $logLine
            ]);
            return;
        }
        LogsController::logWithMonolog([
            'name'      => $logConfig['customId'] ?? 'SCRIPT',
            'path'      => empty($args['isTech']) ? $logConfig['logFontionnel']['file'] : $logConfig['logTechnique']['file'],
            'level'     => $args['level'],
            'maxSize'   => empty($args['isTech']) ? $logConfig['logFontionnel']['maxSize'] : $logConfig['logTechnique']['maxSize'],
            'maxFiles'  => empty($args['isTech']) ? $logConfig['logFontionnel']['maxFiles'] : $logConfig['logTechnique']['maxFiles'],
            'line'      => $logLine
        ]);
    }

    private static function prepareLogLine(array $args) {
        if ($args['level'] == "SQL") {
            $logLine = "[" . $args['sqlQuery'] . "][" . $args['sqlData'] ?? '' . "][" . $args['sqlException'] ?? '' . "]" ;
            // $logLine = TextFormatModel::htmlWasher($logLine);
            // $logLine = TextFormatModel::removeAccent(['string' => $logLine]);

            return $logLine;
        }
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
                $args['tableName'],
                $args['recordId'],
                $args['eventType'],
                $GLOBALS['login'] ?? '',
                $args['eventId'],
                $args['moduleId'],
                $_SERVER['REMOTE_ADDR'] ?? ''
            ],
            "[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]"
        );
        $logLine    = TextFormatModel::htmlWasher($logLine);
        $logLine    = TextFormatModel::removeAccent(['string' => $logLine]);
        return $logLine;
    }

    private static function getLogConfig() {
        $path = null;
        $customId = CoreConfigModel::getCustomId();
        if (file_exists("custom/{$customId}/config/config.json")) {
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
            $logConfig['logFormat']    = "[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]";
            $logConfig['businessCode'] = 'MAARCH';
            $logConfig['logFontionnel']['file']           = 'fonctionnel.log';
            $logConfig['logFontionnel']['maxFileSize']    = '10MB';
            $logConfig['logFontionnel']['maxBackupFiles'] = '10';
            $logConfig['logTechnique']['file']            = 'technique.log';
            $logConfig['logTechnique']['maxFileSize']     = '10MB';
            $logConfig['logTechnique']['maxBackupFiles']  = '10';
            $logConfig['queries']['file']            = 'queries_error.log';
            $logConfig['queries']['maxFileSize']     = '10MB';
            $logConfig['queries']['maxBackupFiles']  = '10';
        }
        $logConfig['customId'] = $customId;
        return $logConfig;
    }

    protected static function logWithMonolog(array $log) {
        ValidatorModel::notEmpty($log, ['name', 'path', 'level', 'line']);
        ValidatorModel::stringType($log, ['name', 'path', 'line']);

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

        $streamHandler = new StreamHandler($log['path'], $log['level']);
        $streamHandler->setFormatter($formatter);

        $logger = new Logger($log['name']);
        $logger->pushHandler($streamHandler);

        switch ($log['level']) {
            case 'DEBUG':
                $logger->debug($log['line']);
                break;
            case 'NOTICE':
                $logger->notice($log['line']);
                break;
            case 'INFO':
                $logger->info($log['line']);
                break;
            case 'WARNING':
                $logger->warning($log['line']);
                break;
            case 'ERROR':
                $logger->error($log['line']);
                break;
            case 'CRITICAL':
                $logger->critical($log['line']);
                break;
            case 'ALERT':
                $logger->alert($log['line']);
                break;
            case 'EMERGENCY':
                $logger->emergency($log['line']);
                break;
        }
        $logger->close();
    }

    private static function rotateLogByFileSize(array $file) {
        ValidatorModel::notEmpty($file, ['path']);
        ValidatorModel::intVal($file, ['maxSize', 'maxFiles']);
        ValidatorModel::stringType($file, ['path']);

        if (file_exists($log['path']) && !empty($log['maxSize']) && $log['maxSize'] > 0 && filesize($log['path']) > $log['maxSize']) {
            $path_parts = pathinfo($log['path']);
            $pattern = $path_parts['dirname']. '/'. $path_parts['filename']. "-%d.". $path_parts['extension'];

            // delete last log
            $fn = sprintf($pattern, $log['maxFiles']);
            if (file_exists($fn)) { unlink($fn);}

            // shift file names (add '-%index' before the extension)
            if (!empty($file['maxFiles'])) {
                for ($i = $log['maxFiles']-1; $i > 0; $i--) {
                    $fn = sprintf($pattern, $i);
                    if(file_exists($fn)) { 
                        rename($fn, sprintf($pattern, $i+1)); 
                    }
                }
            }
            rename($log['path'], sprintf($pattern, 1));
        }
    }
}
