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

use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Interfaces\ResourceDataInterface;
use Resource\Domain\Models\ResourceFileInfo;
use Resource\Domain\Interfaces\ResourceFileInterface;

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
        $document = $this->resourceData->getMainResourceData($resId);

        $isDocserverEncrypted = false;
        $noThumbnailPath = 'dist/assets/noThumbnail.png';
        $pathToThumbnail = $noThumbnailPath;

        if (!empty($document->getFilename()) && $this->resourceData->hasRightByResId($resId, $GLOBALS['id'])) {

            $tnlDocument = $this->resourceData->getResourceVersion($resId, 'TNL', $document->getVersion());

            if (empty($tnlDocument)) {
                $this->resourceFile->convertToThumbnail($resId, 'resource');
                $tnlDocument = $this->resourceData->getResourceVersion($resId, 'TNL', $document->getVersion());
            }

            if (!empty($tnlDocument)) {
                $checkDocserver = $this->resourceData->getDocserverDataByDocserverId($tnlDocument->getDocserverId());
                $isDocserverEncrypted = $checkDocserver->getIsEncrypted() ?? false;
                
                $pathToThumbnail = $this->resourceFile->buildFilePath($tnlDocument->getDocserverId(), $tnlDocument->getPath(), $tnlDocument->getFilename());
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
}