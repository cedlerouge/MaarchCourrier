<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve from Docserver
 * @author dev@maarch.org
 */

namespace Resource\Application;

use Resource\Domain\ResourceDataInterface;
use Resource\Domain\ResourceDataType;
use Resource\Domain\ResourceFileInterface;

class RetrieveResource
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;

    public function __construct (
        ResourceDataInterface $resourceDataInterface,
        ResourceFileInterface $resourceFileInterface
    ) {
        $this->resourceData = $resourceDataInterface;
        $this->resourceFile = $resourceFileInterface;
    }

    /**
     * Get resource data based on the specified type.
     *
     * @param int    $resId            The ID of the resource.
     * @param string $resourceDataType The type of resource data (e.g., 'DEFAULT', 'CONVERTED', 'SIGNED', 'VERSION').
     * @param int    $version          (Optional) The version of the resource data. Required for 'SIGNED' and 'VERSION' types.
     * @param string $type             (Optional) The type of resource for 'VERSION' type (e.g., 'PDF', 'TNL', 'SIGN', 'NOTE').
     *
     * @return array An associative array containing resource data or an error message.
     *               The structure of the array depends on the specified 'resourceDataType'.
     *               Possible keys include: 'error', 'docserver_id', 'path', 'filename', 'version', 'fingerprint', 'subject', 'format', 'typist'.
     */
    public function getResourceDataByType(int $resId, string $resourceDataType, int $version = 0, string $type = 'PDF'): array
    {
        if ($resId <= 0) {
            return ['error' => "The 'resId' parameter must be greater than 0"];
        }
        if (empty($resourceDataType) || !in_array($resourceDataType, ResourceDataType::TYPES)) {
            return ['error' => "The 'resourceDataType' parameter should be : " . implode(', ', ResourceDataType::TYPES)];
        }
        if (ResourceDataType::SIGNED === $resourceDataType && $version <= 0) {
            return ['error' => "The 'version' parameter must be greater than 0 for ResourceDataType is signed"];
        }
        if (ResourceDataType::VERSION === $resourceDataType && $version <= 0) {
            return ['error' => "The 'version' parameter must be greater than 0 for ResourceDataType is version"];
        }
        if (ResourceDataType::VERSION === $resourceDataType && !in_array($type, ResourceDataInterface::ADR_RESOURCE_TYPES)) {
            return ['error' => "The 'type' parameter must be one of theses types: " . implode(', ', ResourceDataInterface::ADR_RESOURCE_TYPES)];
        }

        $document = [];

        switch ($resourceDataType) {
            case ResourceDataType::DEFAULT:
                $document = $this->resourceData->getMainResourceData(
                    $resId,
                    ['docserver_id', 'path', 'filename', 'version', 'fingerprint', 'subject', 'format', 'typist']
                );
                break;
            case ResourceDataType::CONVERTED:
                $document = $this->resourceData->getConvertedPdfById($resId, 'letterbox_coll');
                if (!empty($document['errors'])) {
                    return ['error' => $document['errors']];
                }
                break;
            case ResourceDataType::SIGNED:
                $document = $this->resourceData->getSignResourceData(
                    $resId,
                    $version,
                    ['docserver_id', 'path', 'filename', 'fingerprint']
                );
                break;
            case ResourceDataType::VERSION:
                $document = $this->resourceData->getResourceVersion($resId, $type, $version);
                break;
        }

        return $document;
    }

    /**
     * Retrieves the original main file info.
     *
     * @param int  $resId             The ID of the resource.
     * @param bool $isSignedVersion   (Optional) Whether to retrieve the signed version. Default is false.
     *
     * @return array{
     *     fileContent?:    string, The content of the original file. Present only if no errors occurred.
     *     pathInfo?:       string, Information about the file path. Present only if no errors occurred.
     *     formatFilename?: string, Formated filename. Present only if no errors occurred.
     *     error?:          string, If an error occurs. Possible values include ERROR_RESOURCE_DOES_NOT_EXIST,
     *                              ERROR_RESOURCE_HAS_NO_FILE, ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST,
     *                              ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER, ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH,
     *                              ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER.
     * }
     */
    public function getOriginalMainFile(int $resId, bool $isSignedVersion = false): array
    {
        $document = $this->getResourceDataByType($resId, ResourceDataType::DEFAULT);
        if (!empty($document['error'])) {
            return ['code' => 400, 'error' => $document['error']];
        } elseif (empty($document)) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOES_NOT_EXIST];
        } elseif (empty($document['filename'])) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_HAS_NO_FILE];
        }

        $signdDocument = null;
        if($isSignedVersion) {
            $signdDocument = $this->getResourceDataByType($resId, ResourceDataType::SIGNED, $document['version']);
            $subject  = $document['subject'];
            $document = $signdDocument[0] ?? $document;
            $document['subject'] = $subject;
        }

        $docserver = $this->resourceData->getDocserverDataByDocserverId($document['docserver_id'], ['path_template', 'docserver_type_id']);
        if (empty($docserver['path_template']) || !$this->resourceFile->folderExists($docserver['path_template'])) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST];
        }

        $filePath = $this->resourceFile->buildFilePath($document['docserver_id'], $document['path'], $document['filename']);
        if (!$this->resourceFile->fileExists($filePath)) {
            return ['code' => 404, 'error' => $this->resourceFile::ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER];
        }

        $fingerprint = $this->resourceFile->getFingerPrint($docserver['docserver_type_id'], $filePath);
        if (empty($signdDocument) && empty($document['fingerprint'])) {
            $this->resourceData->updateFingerprint($resId, $fingerprint);
        }

        if ($document['fingerprint'] != $fingerprint) {
            return ['code' => 403, 'error' => $this->resourceFile::ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH];
        }

        $filename = $this->resourceData->formatFilename($document['subject']);

        $fileContent = $this->resourceFile->getFileContent($filePath);
        if ($fileContent === 'false') {
            return ['code' => 404, 'error' => $this->resourceFile::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER];
        }

        return ['formatFilename' => $filename, 'pathInfo' => pathinfo($filePath), 'fileContent' => $fileContent];
    }

    /**
     * Retrieves the main file info with watermark.
     * 
     * @param   int $resId  The ID of the resource.
     * @return  array{
     *     creatorId?:              int,    The creator id.
     *     fileContent?:            string, The content of the original file. Present only if no errors occurred.
     *     pathInfo?:               string, Information about the file path. Present only if no errors occurred.
     *     formatFilename?:         string, Formated filename. Present only if no errors occurred.
     *     originalFormatFilename?: string, Original filename. Present only if no errors occurred.
     *     error?:                  string, If an error occurs. Possible values include ERROR_RESOURCE_DOES_NOT_EXIST,
     *                              ERROR_RESOURCE_HAS_NO_FILE, ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST,
     *                              ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER, ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH,
     *                              ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER.
     * }
     */
    public function getMainFile(int $resId): array
    {
        $document = $this->getResourceDataByType($resId, ResourceDataType::DEFAULT);
        if (!empty($document['error'])) {
            return ['code' => 400, 'error' => $document['error']];
        } elseif (empty($document)) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOES_NOT_EXIST];
        } elseif (empty($document['filename'])) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_HAS_NO_FILE];
        }

        $format     = $document['format'];
        $subject    = $document['subject'];
        $creatorId  = $document['typist'];

        $document = $this->getResourceDataByType($resId, ResourceDataType::CONVERTED);
        if (!empty($document['error'])) {
            return ['code' => 400, 'error' => $document['error']];
        }

        $docserver = $this->resourceData->getDocserverDataByDocserverId($document['docserver_id'], ['path_template', 'docserver_type_id']);
        if (empty($docserver['path_template']) || !$this->resourceFile->folderExists($docserver['path_template'])) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST];
        }

        $filePath = $this->resourceFile->buildFilePath($document['docserver_id'], $document['path'], $document['filename']);
        if (!$this->resourceFile->fileExists($filePath)) {
            return ['code' => 404, 'error' => $this->resourceFile::ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER];
        }

        $fingerprint = $this->resourceFile->getFingerPrint($docserver['docserver_type_id'], $filePath);
        if (empty($signdDocument) && empty($document['fingerprint'])) {
            $this->resourceData->updateFingerprint($resId, $fingerprint);
        }

        if ($document['fingerprint'] != $fingerprint) {
            return ['code' => 403, 'error' => $this->resourceFile::ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH];
        }

        $fileContent = $this->resourceFile->getWatermark($resId, $filePath);
        if (empty($fileContent) || $fileContent === 'null') {
            $fileContent = $this->resourceFile->getFileContent($filePath);
        }
        
        if ($fileContent === 'false') {
            return ['code' => 404, 'error' => $this->resourceFile::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER];
        }

        $filename = $this->resourceData->formatFilename($subject);

        return [
            'creatorId'         => $creatorId,
            'originalFormat'    => $format,
            'formatFilename'    => $filename,
            'pathInfo'          => pathinfo($filePath),
            'fileContent'       => $fileContent
        ];
    }

    /**
     * Retrieves the main file info with watermark.
     * 
     * @param   int     $resId      The ID of the resource.
     * @param   int     $version    Resource version.
     * @param   string  $type       ['PDF', 'SIGN', 'NOTE']
     * 
     * @return  array{
     *     fileContent?:    string, The content of the original file. Present only if no errors occurred.
     *     formatFilename?: string, Formated filename. Present only if no errors occurred.
     *     error?:          string, If an error occurs. Possible values include ERROR_RESOURCE_DOES_NOT_EXIST,
     *                      ERROR_RESOURCE_HAS_NO_FILE, ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST,
     *                      ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER, ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH,
     *                      ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER.
     * }
     */
    public function getVersionMainFile(int $resId, int $version, string $type): array
    {
        $document = $this->getResourceDataByType($resId, ResourceDataType::DEFAULT);
        if (!empty($document['error'])) {
            return ['code' => 400, 'error' => $document['error']];
        } elseif (empty($document)) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOES_NOT_EXIST];
        } elseif (empty($document['filename'])) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_HAS_NO_FILE];
        } elseif (!empty($document) && $version > $document['version']) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_INCORRECT_VERSION];
        }

        $subject = $document['subject'];

        $document = $this->getResourceDataByType($resId, ResourceDataType::VERSION, $version, $type);
        if (!empty($document['error'])) {
            return ['code' => 400, 'error' => $document['error']];
        }
        if (empty($document)) {
            return ['code' => 400, 'error' => 'Type has no file'];
        }

        $docserver = $this->resourceData->getDocserverDataByDocserverId($document['docserver_id'], ['path_template', 'docserver_type_id']);
        if (empty($docserver['path_template']) || !$this->resourceFile->folderExists($docserver['path_template'])) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST];
        }

        $filePath = $this->resourceFile->buildFilePath($document['docserver_id'], $document['path'], $document['filename']);
        if (!$this->resourceFile->fileExists($filePath)) {
            return ['code' => 404, 'error' => $this->resourceFile::ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER];
        }

        $fingerprint = $this->resourceFile->getFingerPrint($docserver['docserver_type_id'], $filePath);
        if (empty($signdDocument) && empty($document['fingerprint'])) {
            $this->resourceData->updateFingerprint($resId, $fingerprint);
        }

        if ($document['fingerprint'] != $fingerprint) {
            return ['code' => 403, 'error' => $this->resourceFile::ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH];
        }

        $fileContent = $this->resourceFile->getWatermark($resId, $filePath);
        if (empty($fileContent) || $fileContent === 'null') {
            $fileContent = $this->resourceFile->getFileContent($filePath);
        }
        
        if ($fileContent === 'false') {
            return ['code' => 404, 'error' => $this->resourceFile::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER];
        }

        $filename = $this->resourceData->formatFilename($subject);

        return ['formatFilename' => $filename, 'pathInfo' => pathinfo($filePath), 'fileContent' => $fileContent];
    }

    public function getThumbnailFile(int $resId): array
    {
        $document = $this->getResourceDataByType($resId, ResourceDataType::DEFAULT);
        if (!empty($document['error'])) {
            return ['code' => 400, 'error' => $document['error']];
        } elseif (empty($document)) {
            return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOES_NOT_EXIST];
        }

        $noThumbnailPath = 'dist/assets/noThumbnail.png';
        $pathToThumbnail = $noThumbnailPath;

        if (!empty($document['filename']) && $this->resourceData->hasRightByResId($resId, $GLOBALS['id'])) {

            $tnlDocument = $this->getResourceDataByType($resId, ResourceDataType::VERSION, $document['version'], 'TNL');

            if (empty($tnlDocument)) {
                $control = $this->resourceFile->convertToThumbnail($resId, 'resource');
                if (!empty($control['error'] ?? null)) {
                    return ['code' => 400, 'error' => $control['error']];
                }
                $tnlDocument = $this->getResourceDataByType($resId, ResourceDataType::VERSION, $document['version'], 'TNL');
            }

            if (!empty($tnlDocument)) {
                $docserver = $this->resourceData->getDocserverDataByDocserverId($tnlDocument['docserver_id'], ['path_template']);
                if (empty($docserver['path_template']) || !$this->resourceFile->folderExists($docserver['path_template'])) {
                    return ['code' => 400, 'error' => $this->resourceData::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST];
                }
                $pathToThumbnail = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $tnlDocument['path']) . $tnlDocument['filename'];
            }
        }

        $pathInfo = pathinfo($pathToThumbnail);
        $fileContent = $this->resourceFile->getFileContent($pathToThumbnail);

        if ($fileContent === 'false') {
            $pathInfo = pathinfo($noThumbnailPath);
            $fileContent = $this->resourceFile->getFileContent($noThumbnailPath);
        }

        return ['formatFilename' => "maarch.{$pathInfo['extension']}", 'fileContent' => $fileContent];
    }
}