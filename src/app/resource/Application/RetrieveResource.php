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

use Resource\Domain\Exceptions\ParameterMustBeGreaterThanZeroException;
use Resource\Domain\Exceptions\ResourceDocserverDoesNotExistException;
use Resource\Domain\Exceptions\ResourceDoesNotExistException;
use Resource\Domain\Exceptions\ResourceFailedToGetDocumentFromDocserverException;
use Resource\Domain\Exceptions\ResourceFingerPrintDoesNotMatchException;
use Resource\Domain\Exceptions\ResourceHasNoFileException;
use Resource\Domain\Exceptions\ResourceNotFoundInDocserverException;
use Resource\Domain\Exceptions\ConvertedResultException;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceConverted;

class RetrieveResource
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;
    private RetrieveDocserverAndFilePath $retrieveResourceDocserverAndFilePath;

    /**
     * @param ResourceDataInterface $resourceDataInterface
     * @param ResourceFileInterface $resourceFileInterface
     * @param RetrieveDocserverAndFilePath $retrieveResourceDocserverAndFilePath
     */
    public function __construct(
        ResourceDataInterface        $resourceDataInterface,
        ResourceFileInterface        $resourceFileInterface,
        RetrieveDocserverAndFilePath $retrieveResourceDocserverAndFilePath
    )
    {
        $this->resourceData = $resourceDataInterface;
        $this->resourceFile = $resourceFileInterface;
        $this->retrieveResourceDocserverAndFilePath = $retrieveResourceDocserverAndFilePath;
    }

    /**
     * Retrieves the main file info with watermark.
     *
     * @param int $resId The ID of the resource.
     * @return  ResourceFileInfo
     * @throws ParameterMustBeGreaterThanZeroException
     * @throws ResourceDoesNotExistException
     * @throws ResourceHasNoFileException
     * @throws ResourceFingerPrintDoesNotMatchException
     * @throws ResourceFailedToGetDocumentFromDocserverException
     * @throws ConvertedResultException
     * @throws ResourceDocserverDoesNotExistException
     * @throws ResourceNotFoundInDocserverException
     */
    public function getResourceFile(int $resId): ResourceFileInfo
    {
        if ($resId <= 0) {
            throw new ParameterMustBeGreaterThanZeroException('resId');
        }

        $document = $this->resourceData->getMainResourceData($resId);

        if ($document == null) {
            throw new ResourceDoesNotExistException();
        } elseif (empty($document->getFilename())) {
            throw new ResourceHasNoFileException();
        }

        $format = $document->getFormat();
        $subject = $document->getSubject();
        $creatorId = $document->getTypist();

        $document = $this->getConvertedResourcePdfById($resId);

        $docserverAndFilePath = $this->retrieveResourceDocserverAndFilePath->getDocserverAndFilePath($document);

        $fingerPrint = $this->resourceFile->getFingerPrint($docserverAndFilePath->getDocserver()->getDocserverTypeId(), $docserverAndFilePath->getFilePath());
        if (!empty($fingerPrint) && empty($document->getFingerprint())) {
            $this->resourceData->updateFingerprint($resId, $fingerPrint);
            $document->setFingerprint($fingerPrint);
        }

        if ($document->getFingerprint() != $fingerPrint) {
            throw new ResourceFingerPrintDoesNotMatchException();
        }

        $fileContentWithNoWatermark = $this->resourceFile->getFileContent(
            $docserverAndFilePath->getFilePath(),
            $docserverAndFilePath->getDocserver()->getIsEncrypted()
        );

        $fileContent = $this->resourceFile->getWatermark($resId, $fileContentWithNoWatermark);
        if (empty($fileContent)) {
            $fileContent = $fileContentWithNoWatermark;
        }

        if ($fileContent === null) {
            throw new ResourceFailedToGetDocumentFromDocserverException();
        }

        $filename = $this->resourceData->formatFilename($subject);

        return new ResourceFileInfo(
            $creatorId,
            null,
            pathInfo($docserverAndFilePath->getFilePath()),
            $fileContent,
            $filename,
            $format
        );
    }

    /**
     * @param int $resId
     * @return ResourceConverted
     * @throws ConvertedResultException
     */
    private function getConvertedResourcePdfById(int $resId): ResourceConverted
    {
        $document = $this->resourceData->getConvertedPdfById($resId, 'letterbox_coll');

        if (!empty($document['errors'])) {
            throw new ConvertedResultException($document['errors']);
        }

        return new ResourceConverted(
            $document['id'] ?? 0,
            $resId,
            '',
            0,
            $document['docserver_id'],
            $document['path'],
            $document['filename'],
            $document['fingerprint']
        );
    }
}
