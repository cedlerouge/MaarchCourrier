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
use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmptyAndShould;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
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
use Resource\Domain\ResourceConverted;

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
        if ($resId <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('resId', 0);
        }
        if ($page <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('page', 0);
        }

        $document = $this->resourceData->getMainResourceData($resId);
        if ($document == null) {
            throw new ExceptionResourceDoesNotExist();
        }

        if (!$this->resourceData->hasRightByResId($resId, $GLOBALS['id'])) {
            throw new ExceptionResourceOutOfPerimeter();
        }

        $check = $this->resourceFile->convertOnePageToThumbnail($resId, 'resource', $page);
        if (strpos($check, 'errors:') !== false) {
            throw new ExceptionConvertThumbnail($check);
        }

        $adr = $this->getResourceVersionThumbnailByPage($resId, "TNL$page", $document->getVersion());
        if ($adr == null) {
            throw new ExceptionResourceDoesNotExist();
        }

        $adrDocserver = $this->resourceData->getDocserverDataByDocserverId($adr->getDocserverId());
        if ($adrDocserver == null || !$this->resourceFile->folderExists($adrDocserver->getPathTemplate())) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        $pathToThumbnail = $this->resourceFile->buildFilePath($adrDocserver->getPathTemplate(), $adr->getPath(), $adr->getFilename());
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
        if ($document == null) {
            throw new ExceptionResourceDoesNotExist();
        }

        $docserver = $this->resourceData->getDocserverDataByDocserverId($document->getDocserverId());
        if ($docserver == null || !$this->resourceFile->folderExists($docserver->getPathTemplate())) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        $pathToPdfDocument = $this->resourceFile->buildFilePath($docserver->getPathTemplate(), $document->getPath(), $document->getFilename());
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

    private function getResourceVersionThumbnailByPage(int $resId, string $type, int $version): ?ResourceConverted
    {
        $checkThumbnailPageType = ctype_digit(str_replace('TNL', '', $type));
        if (empty($type) || (!in_array($type, $this->resourceData::ADR_RESOURCE_TYPES) && !$checkThumbnailPageType)) {
            throw new ExceptionParameterCanNotBeEmptyAndShould('type', implode(', ', $this->resourceData::ADR_RESOURCE_TYPES) . " or thumbnail page 'TNL*'");
        }

        $document = $this->resourceData->getResourceVersion($resId, $type, $version);

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