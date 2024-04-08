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

use Resource\Domain\Exceptions\ParameterCanNotBeEmptyException;
use Resource\Domain\Exceptions\ParameterMustBeGreaterThanZeroException;
use Resource\Domain\Exceptions\ResourceDocserverDoesNotExistException;
use Resource\Domain\Exceptions\ResourceDoesNotExistException;
use Resource\Domain\Exceptions\ResourceFailedToGetDocumentFromDocserverException;
use Resource\Domain\Exceptions\ResourceFingerPrintDoesNotMatchException;
use Resource\Domain\Exceptions\ResourceHasNoFileException;
use Resource\Domain\Exceptions\ResourceIncorrectVersionException;
use Resource\Domain\Exceptions\ResourceNotFoundInDocserverException;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceConverted;

class RetrieveVersionResource
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;
    private RetrieveDocserverAndFilePath $retrieveResourceDocserverAndFilePath;

    public function __construct(
        ResourceDataInterface $resourceDataInterface,
        ResourceFileInterface $resourceFileInterface,
        RetrieveDocserverAndFilePath $retrieveResourceDocserverAndFilePath
    ) {
        $this->resourceData = $resourceDataInterface;
        $this->resourceFile = $resourceFileInterface;
        $this->retrieveResourceDocserverAndFilePath = $retrieveResourceDocserverAndFilePath;
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
     * @throws ParameterMustBeGreaterThanZeroException
     * @throws ParameterCanNotBeEmptyException
     * @throws ResourceDoesNotExistException
     * @throws ResourceHasNoFileException
     * @throws ResourceIncorrectVersionException
     * @throws ResourceFingerPrintDoesNotMatchException
     * @throws ResourceFailedToGetDocumentFromDocserverException
     * @throws ResourceDocserverDoesNotExistException
     * @throws ResourceNotFoundInDocserverException
     * */
    public function getResourceFile(int $resId, int $version, string $type): ResourceFileInfo
    {
        if ($resId <= 0) {
            throw new ParameterMustBeGreaterThanZeroException('resId');
        }
        if ($version <= 0) {
            throw new ParameterMustBeGreaterThanZeroException('version');
        }
        if (empty($type) || (!in_array($type, $this->resourceData::ADR_RESOURCE_TYPES))) {
            throw new ParameterCanNotBeEmptyException(
                'type',
                implode(', ', $this->resourceData::ADR_RESOURCE_TYPES)
            );
        }

        $document = $this->resourceData->getMainResourceData($resId);

        if ($document == null) {
            throw new ResourceDoesNotExistException();
        } elseif (empty($document->getFilename())) {
            throw new ResourceHasNoFileException();
        } elseif ($version > $document->getVersion()) {
            throw new ResourceIncorrectVersionException();
        }

        $format = $document->getFormat();
        $subject = $document->getSubject();
        $document = $this->getResourceVersion($resId, $type, $version);

        $docserverAndFilePath = $this->retrieveResourceDocserverAndFilePath->getDocserverAndFilePath($document);

        $fingerPrint = $this->resourceFile->getFingerPrint(
            $docserverAndFilePath->getDocserver()->getDocserverTypeId(),
            $docserverAndFilePath->getFilePath()
        );
        if (!empty($fingerPrint) && empty($document->getFingerprint())) {
            $this->resourceData->updateFingerprint($resId, $fingerPrint);
        }

        if ($document->getFingerprint() != $fingerPrint) {
            throw new ResourceFingerPrintDoesNotMatchException();
        }

        $filename = $this->resourceData->formatFilename($subject);

        $fileContentWithNoWatermark = $this->resourceFile->getFileContent(
            $docserverAndFilePath->getFilePath(),
            $docserverAndFilePath->getDocserver()->getIsEncrypted()
        );

        $fileContent = $this->resourceFile->getWatermark($resId, $fileContentWithNoWatermark);
        if (empty($fileContent) || $fileContent === 'null') {
            $fileContent = $fileContentWithNoWatermark;
        }

        if ($fileContent === null) {
            throw new ResourceFailedToGetDocumentFromDocserverException();
        }

        return new ResourceFileInfo(
            null,
            null,
            pathInfo($docserverAndFilePath->getFilePath()),
            $fileContent,
            $filename,
            $format
        );
    }

    /**
     * @throws ResourceDoesNotExistException
     */
    private function getResourceVersion(int $resId, string $type, int $version): ResourceConverted
    {
        $document = $this->resourceData->getResourceVersion($resId, $type, $version);

        if ($document == null) {
            throw new ResourceDoesNotExistException();
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
