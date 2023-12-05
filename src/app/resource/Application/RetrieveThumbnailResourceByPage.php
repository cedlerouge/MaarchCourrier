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

use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Exceptions\ExceptionResourceOutOfPerimeter;
use Resource\Domain\Exceptions\ExceptionResourcePageNotFound;
use Resource\Domain\Exceptions\ExceptionThumbnailNotFoundInDocserverOrNotReadable;
use Resource\Domain\Exceptions\ExeptionSetaPdfResult;
use Resource\Domain\Interfaces\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Interfaces\ResourceFileInterface;
use Resource\Domain\Interfaces\ResourceLogInterface;

class RetrieveThumbnailResourceByPage
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;
    private ResourceLogInterface  $resourceLog;

    public function __construct (
        ResourceDataInterface $resourceDataInterface,
        ResourceFileInterface $resourceFileInterface,
        ResourceLogInterface  $resourceLog
    ) {
        $this->resourceData = $resourceDataInterface;
        $this->resourceFile = $resourceFileInterface;
        $this->resourceLog  = $resourceLog;
    }

    /**
     * Retrieves thumbnail of resource by page number.
     * 
     * @param   int $resId  The ID of the resource.
     * @param   int $page   The ID of the resource.
     * 
     * @return  ResourceFileInfo
     */
    public function getThumbnailFileByPage(int $resId, int $page): ResourceFileInfo
    {
        $document = $this->resourceData->getMainResourceData($resId);

        if (!$this->resourceData->hasRightByResId($resId, $GLOBALS['id'])) {
            throw new ExceptionResourceOutOfPerimeter();
        }

        $this->resourceFile->convertOnePageToThumbnail($resId, 'resource', $page);

        $adr = $this->resourceData->getResourceVersion($resId, "TNL$page", $document->getVersion());
        if (empty($adr)) {
            throw new ExceptionResourceDoesNotExist();
        }

        $adrDocserver = $this->resourceData->getDocserverDataByDocserverId($adr->getDocserverId());
        if (!$this->resourceFile->folderExists($adrDocserver->getPathTemplate())) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        $pathToThumbnail = $this->resourceFile->buildFilePath($adr->getDocserverId(), $adr->getPath(), $adr->getFilename());
        if (!$this->resourceFile->fileExists($pathToThumbnail)) {
            throw new ExceptionThumbnailNotFoundInDocserverOrNotReadable();
        }

        $fileContent = $this->resourceFile->getFileContent($pathToThumbnail, $adrDocserver->getIsEncrypted());
        if ($fileContent === 'false') {
            throw new ExceptionResourcePageNotFound();
        }

        $filename = $this->resourceData->formatFilename($document->getSubject());

        // Get latest pdf version before to get the page count
        $document = $this->resourceData->getLatestResourceVersion($resId, 'PDF');

        $pathToPdfDocument = $this->resourceFile->buildFilePath($document->getDocserverId(), $document->getPath(), $document->getFilename());
        if (!$this->resourceFile->fileExists($pathToPdfDocument)) {
            throw new ExceptionResourceNotFoundInDocserver();
        }

        $pageCount = 0;
        try {
            $pageCount = $this->resourceFile->getTheNumberOfPagesInThePdfFile($pathToPdfDocument);
        } catch (\Throwable $th) {
            $this->resourceLog->logThumbnailEvent('ERROR', $resId, $th->getMessage());
            throw new ExeptionSetaPdfResult($th->getMessage());
        }

        return new ResourceFileInfo(
            null,
            $pageCount,
            pathInfo($pathToThumbnail),
            $fileContent,
            $filename,
            ''
        );
    }
}