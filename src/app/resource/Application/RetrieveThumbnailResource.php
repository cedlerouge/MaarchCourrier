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

use Resource\Domain\Exceptions\ExceptionConvertThumbnail;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
use Resource\Domain\Exceptions\ExceptionResourceDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Interfaces\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Interfaces\ResourceFileInterface;
use Resource\Domain\ResourceConverted;

class RetrieveThumbnailResource
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
     * Retrieves the main file thumbnail info with watermark.
     * 
     * @param   int     $resId      The ID of the resource.
     * @param   int     $version    Resource version.
     * @param   string  $type       ['PDF', 'SIGN', 'NOTE']
     *
     * @return  ResourceFileInfo
     * 
     * @throws  ExceptionResourceNotFoundInDocserver
     */
    public function getThumbnailFile(int $resId): ResourceFileInfo
    {
        if ($resId <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('resId', 0);
        }

        $document = $this->resourceData->getMainResourceData($resId);

        if ($document == null) {
            throw new ExceptionResourceDoesNotExist();
        }

        $isDocserverEncrypted = false;
        $noThumbnailPath = 'dist/assets/noThumbnail.png';
        $pathToThumbnail = $noThumbnailPath;

        
        if (!empty($document->getFilename()) && $this->resourceData->hasRightByResId($resId, $GLOBALS['id'])) {
            
            $tnlDocument = $this->getResourceVersion($resId, 'TNL', $document->getVersion());
            
            if ($tnlDocument == null) {
                $check = $this->resourceFile->convertToThumbnail($resId, 'resource');
                if (isset($check['errors'])) {
                    throw new ExceptionConvertThumbnail($check['errors']);
                }
                $tnlDocument = $this->getResourceVersion($resId, 'TNL', $document->getVersion());
            }
            
            if ($tnlDocument != null) {
                $checkDocserver = $this->resourceData->getDocserverDataByDocserverId($tnlDocument->getDocserverId());
                $isDocserverEncrypted = $checkDocserver->getIsEncrypted() ?? false;
                
                $pathToThumbnail = $this->resourceFile->buildFilePath($checkDocserver->getPathTemplate(), $tnlDocument->getPath(), $tnlDocument->getFilename());
                if (!$this->resourceFile->fileExists($pathToThumbnail)) {
                    throw new ExceptionResourceNotFoundInDocserver();
                }
            }
        }

        $pathInfo = pathinfo($pathToThumbnail);
        $fileContent = $this->resourceFile->getFileContent($pathToThumbnail, $isDocserverEncrypted);
        
        if ($fileContent === 'false') {
            $pathInfo = pathinfo($noThumbnailPath);
            $fileContent = $this->resourceFile->getFileContent($noThumbnailPath);
        }
        
        return new ResourceFileInfo(
            null,
            null,
            $pathInfo,
            $fileContent,
            "maarch.{$pathInfo['extension']}",
            ""
        );
    }
    
    private function getResourceVersion(int $resId, string $type, int $version): ?ResourceConverted
    {
        $document = $this->resourceData->getResourceVersion($resId, 'TNL', $version);
        
        if ($document == null) {
            return null;
        }
        
        return new ResourceConverted(
            $document['id'],
            $resId,
            $type,
            $version,
            $document['docserver_id'],
            $document['path'],
            $document['filename'],
            $document['fingerprint']
        );
    }
}