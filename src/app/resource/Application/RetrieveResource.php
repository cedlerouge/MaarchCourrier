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
use Resource\Domain\ResourceFileInterface;
use SrcCore\models\TextFormatModel;

class RetrieveResource
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;

    public function __construct (
        ResourceDataInterface $resourceDataInterface,
        ResourceFileInterface $resourceFileInterface
    ){
        $this->resourceData = $resourceDataInterface;
        $this->resourceFile = $resourceFileInterface;
    }

    /**
     * Retrieves the original main file info.
     *
     * @param int  $resId             The ID of the resource.
     * @param bool $isSignedVersion   (Optional) Whether to retrieve the signed version. Default is false.
     *
     * @return array{
     *     content?:        string, The content of the original file. Present only if no errors occurred.
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
        $document = $this->resourceData->getMainResourceData($resId, ['docserver_id', 'path', 'filename', 'version', 'fingerprint', 'subject']);
        if (empty($document)) {
            return ['error' => $this->resourceData::ERROR_RESOURCE_DOES_NOT_EXIST];
        } elseif (empty($document['filename'])) {
            return ['error' => $this->resourceData::ERROR_RESOURCE_HAS_NO_FILE];
        }

        $signdDocument = null;
        if($isSignedVersion) {
            $signdDocument = $this->resourceData->getSignResourceData(
                $resId, 
                $document['version'], 
                ['docserver_id', 'path', 'filename', 'fingerprint']
            );
            $document = $signdDocument[0] ?? $document;
        }

        $docserver = $this->resourceData->getDocserverDataByDocserverId($document['docserver_id'], ['path_template', 'docserver_type_id']);
        if (empty($docserver['path_template']) || !$this->resourceFile->folderExists($docserver['path_template'])) {
            return ['error' => $this->resourceData::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST];
        }
        
        $filePath = $this->resourceFile->buildFilePath($document['docserver_id'], $document['path'], $document['filename']);
        if (!$this->resourceFile->fileExists($filePath)) {
            return ['error' => $this->resourceFile::ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER];
        }

        $fingerprint = $this->resourceFile->getFingerPrint($docserver['docserver_type_id'], $filePath);
        if (empty($signdDocument) && empty($document['fingerprint'])) {
            $this->resourceData->updateFingerprint($resId, $fingerprint);
        }

        if ($document['fingerprint'] != $fingerprint) {
            return ['error' => $this->resourceFile::ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH];
        }

        $filename = $this->resourceData->formatFilename($document['subject']);

        $fileContent = $this->resourceFile->getFileContent($filePath);
        if ($fileContent === false) {
            return ['error' => $this->resourceFile::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER];
        }

        return ['formatFilename' => $filename, 'pathInfo' => pathinfo($filePath), 'fileContent' => $fileContent];
    }
}