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

use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmptyAndShould;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceFailedToGetDocumentFromDocserver;
use Resource\Domain\Exceptions\ExceptionResourceFingerPrintDoesNotMatch;
use Resource\Domain\Exceptions\ExceptionResourceHasNoFile;
use Resource\Domain\Exceptions\ExceptionResourceIncorrectVersion;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Interfaces\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Interfaces\ResourceFileInterface;
use Resource\Domain\ResourceConverted;

class RetrieveVersionResource
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
     * Retrieves the main file info with watermark.
     * 
     * @param   int     $resId      The ID of the resource.
     * @param   int     $version    Resource version.
     * @param   string  $type       ['PDF', 'SIGN', 'NOTE']
     *
     * @return  ResourceFileInfo
     * 
     * @throws  \Exception
     */
    public function getResourceFile(int $resId, int $version, string $type): ResourceFileInfo
    {
        if ($resId <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('resId', 0);
        }
        if ($version <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('version', 0);
        }
        if (empty($type) || (!in_array($type, $this->resourceData::ADR_RESOURCE_TYPES))) {
            throw new ExceptionParameterCanNotBeEmptyAndShould('type', implode(', ', $this->resourceData::ADR_RESOURCE_TYPES));
        }

        $document = $this->resourceData->getMainResourceData($resId);

        if ($document == null) {
            throw new ExceptionResourceDoesNotExist();
        } elseif (empty($document->getFilename())) {
            throw new ExceptionResourceHasNoFile();
        } elseif ($document != null && $version > $document->getVersion()) {
            throw new ExceptionResourceIncorrectVersion();
        }

        $format = $document->getFormat();
        $subject = $document->getSubject();
        $document = $this->getResourceVersion($resId, $type, $version);
        
        $docserver = $this->resourceData->getDocserverDataByDocserverId($document->getDocserverId());
        if ($docserver == null || !$this->resourceFile->folderExists($docserver->getPathTemplate())) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        $filePath = $this->resourceFile->buildFilePath($docserver->getPathTemplate(), $document->getPath(), $document->getFilename());
        if (!$this->resourceFile->fileExists($filePath)) {
            throw new ExceptionResourceNotFoundInDocserver();
        }

        $fingerprint = $this->resourceFile->getFingerPrint($docserver->getDocserverTypeId(), $filePath);
        if (!empty($fingerprint) && empty($document->getFingerprint())) {
            $this->resourceData->updateFingerprint($resId, $fingerprint);
        }

        if ($document->getFingerprint() != $fingerprint) {
            throw new ExceptionResourceFingerPrintDoesNotMatch();
        }

        $filename = $this->resourceData->formatFilename($subject);

        $fileContentWithNoWatermark = $this->resourceFile->getFileContent($filePath, $docserver->getIsEncrypted());

        $fileContent = $this->resourceFile->getWatermark($resId, $fileContentWithNoWatermark);
        if (empty($fileContent) || $fileContent === 'null') {
            $fileContent = $fileContentWithNoWatermark;
        }
        
        if ($fileContent === 'false') {
            throw new ExceptionResourceFailedToGetDocumentFromDocserver();
        }

        return new ResourceFileInfo(
            null,
            null,
            pathInfo($filePath),
            $fileContent,
            $filename,
            $format
        );
    }

    /**
     * @throws ExceptionResourceDoesNotExist
     */
    private function getResourceVersion(int $resId, string $type, int $version): ResourceConverted
    {
        $document = $this->resourceData->getResourceVersion($resId, $type, $version);

        if ($document == null) {
            throw new ExceptionResourceDoesNotExist();
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