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

use Exception;
use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmptyAndShould;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceFailedToGetDocumentFromDocserver;
use Resource\Domain\Exceptions\ExceptionResourceFingerPrintDoesNotMatch;
use Resource\Domain\Exceptions\ExceptionResourceHasNoFile;
use Resource\Domain\Exceptions\ExceptionResourceIncorrectVersion;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceConverted;

class RetrieveVersionResource
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;
    private RetrieveDocserverFilePathAndFingerPrint $retrieveResourceDocserverFilePathFingerPrint;

    public function __construct (
        ResourceDataInterface $resourceDataInterface,
        ResourceFileInterface $resourceFileInterface,
        RetrieveDocserverFilePathAndFingerPrint $retrieveResourceDocserverFilePathFingerPrint
    ) {
        $this->resourceData = $resourceDataInterface;
        $this->resourceFile = $resourceFileInterface;
        $this->retrieveResourceDocserverFilePathFingerPrint = $retrieveResourceDocserverFilePathFingerPrint;
    }

    /**
     * Retrieves the main file info with watermark.
     *
     * @param int $resId The ID of the resource.
     * @param int $version Resource version.
     * @param string $type ['PDF', 'SIGN', 'NOTE']
     *
     * @return  ResourceFileInfo
     *
     * @throws ExceptionParameterMustBeGreaterThan
     * @throws ExceptionParameterCanNotBeEmptyAndShould
     * @throws ExceptionResourceDoesNotExist
     * @throws ExceptionResourceHasNoFile
     * @throws ExceptionResourceIncorrectVersion
     * @throws ExceptionResourceFingerPrintDoesNotMatch
     * @throws ExceptionResourceFailedToGetDocumentFromDocserver
     * @throws ExceptionResourceDocserverDoesNotExist
     * @throws ExceptionResourceNotFoundInDocserver
     * */
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
        } elseif ($version > $document->getVersion()) {
            throw new ExceptionResourceIncorrectVersion();
        }

        $format = $document->getFormat();
        $subject = $document->getSubject();
        $document = $this->getResourceVersion($resId, $type, $version);

        try {
            $docserverFilePathAndFingerprint = $this->retrieveResourceDocserverFilePathFingerPrint->getDocserverFilePathAndFingerprint($document);
        } catch (ExceptionResourceDocserverDoesNotExist|ExceptionResourceNotFoundInDocserver $e) {
            throw new $e;
        }

        if (!empty($docserverFilePathAndFingerprint->getFingerprint()) && empty($document->getFingerprint())) {
            $this->resourceData->updateFingerprint($resId, $docserverFilePathAndFingerprint->getFingerprint());
        }

        if ($document->getFingerprint() != $docserverFilePathAndFingerprint->getFingerprint()) {
            throw new ExceptionResourceFingerPrintDoesNotMatch();
        }

        $filename = $this->resourceData->formatFilename($subject);

        $fileContentWithNoWatermark = $this->resourceFile->getFileContent($docserverFilePathAndFingerprint->getFilePath(), $docserverFilePathAndFingerprint->getDocserver()->getIsEncrypted());

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
            pathInfo($docserverFilePathAndFingerprint->getFilePath()),
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
