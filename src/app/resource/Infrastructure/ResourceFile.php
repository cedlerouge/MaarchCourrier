<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource file
 * @author dev@maarch.org
 */

namespace Resource\Infrastructure;

use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use Resource\Domain\ResourceFileInterface;
use Resource\controllers\StoreController;
use Resource\models\ResModel;

class ResourceFile implements ResourceFileInterface
{
    public function buildFilePath(string $docserverId, string $documentPath, string $documentFilename): string
    {
        if (empty($docserverId)) {
            return 'Error: Parameter docserverId can not be empty';
        }
        if (empty($documentPath)) {
            return 'Error: Parameter documentPath can not be empty';
        }
        if (empty($documentFilename)) {
            return 'Error: Parameter documentFilename can not be empty';
        }

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $docserverId, 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return 'Error: ' . $this::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST;
        }

        return $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $documentPath) . $documentFilename;
    }

    /**
     * Check if folder exists 
     * 
     * @param   string  $folderPath
     * 
     * @return  bool
     */
    public function folderExists(string $folderPath): bool
    {
        if (empty($folderPath)) {
            return false;
        }
        return is_dir($folderPath);
    }

    /**
     * Check if file exists 
     * 
     * @param   string  $filePath
     * 
     * @return  bool
     */
    public function fileExists(string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }
        return file_exists($filePath);
    }

    /**
     * Get file fingerprint
     * 
     * @param   string  $docserverTypeId
     * @param   string  $filePath
     * 
     * @return  string
     */
    public function getFingerPrint(string $docserverTypeId, string $filePath): string
    {
        if (empty($docserverTypeId)) {
            return 'Error: Parameter docserverId can not be empty';
        }
        if (empty($filePath)) {
            return 'Error: Parameter documentPath can not be empty';
        }

        $docserverType  = DocserverTypeModel::getById(['id' => $docserverTypeId, 'select' => ['fingerprint_mode']]);
        $fingerprint    = StoreController::getFingerPrint(['filePath' => $filePath, 'mode' => $docserverType['fingerprint_mode']]);
        return $fingerprint;
    }

    /**
     * Update resource fingerprint
     * 
     * @param   int     $resId
     * @param   string  $fingerprint
     * 
     * @return  void
     */
    public function updateFingerprint(int $resId, string $fingerprint): void
    {
        ResModel::update(['set' => ['fingerprint' => $fingerprint], 'where' => ['res_id = ?'], 'data' => [$resId]]);
    }

    /**
     * Retrieves file content.
     *
     * @param string $filePath The path to the file.
     *
     * @return string|'false' Returns the content of the file as a string if successful, or a string with value 'false' on failure.
     */
    public function getFileContent(string $filePath): string
    {
        if (empty($filePath)) {
            return 'false';
        }

        return file_get_contents($filePath);
    }
}
