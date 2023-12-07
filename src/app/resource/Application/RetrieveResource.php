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
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Exceptions\ExecptionConvertedResult;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceConverted;

class RetrieveResource
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;
    private RetrieveDocserverAndFilePath $retrieveResourceDocserverAndFilePath;

    public function __construct (
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
     * @return  ResourceFileInfo
     * @throws ExceptionParameterMustBeGreaterThan
     * @throws ExceptionResourceDoesNotExist
     * @throws ExceptionResourceHasNoFile
     * @throws ExceptionResourceFingerPrintDoesNotMatch
     * @throws ExceptionResourceFailedToGetDocumentFromDocserver
     * @throws ExceptionParameterCanNotBeEmptyAndShould
     * @throws ExecptionConvertedResult
     * @throws ExceptionResourceDocserverDoesNotExist
     * @throws ExceptionResourceNotFoundInDocserver
     */
    public function getResourceFile(int $resId): ResourceFileInfo
    {
        if ($resId <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('resId', 0);
        }

        $document = $this->resourceData->getMainResourceData($resId);

        if ($document == null) {
            throw new ExceptionResourceDoesNotExist();
        } elseif (empty($document->getFilename())) {
            throw new ExceptionResourceHasNoFile();
        }

        $format     = $document->getFormat();
        $subject    = $document->getSubject();
        $creatorId  = $document->getTypist();

        try {
            $document = $this->getConvertedResourcePdfById($resId);
        } catch (ExceptionParameterCanNotBeEmptyAndShould|ExecptionConvertedResult $e) {
            throw $e;
        }

        $docserverAndFilePath= $this->retrieveResourceDocserverAndFilePath->getDocserverAndFilePath($document);

        $fingerPrint = $this->resourceFile->getFingerPrint($docserverAndFilePath->getDocserver()->getDocserverTypeId(), $docserverAndFilePath->getFilePath());
        if (!empty($fingerPrint) && empty($document->getFingerprint())) {
            $this->resourceData->updateFingerprint($resId, $fingerPrint);
        }

        if ($document->getFingerprint() != $fingerPrint) {
            throw new ExceptionResourceFingerPrintDoesNotMatch();
        }

        $fileContentWithNoWatermark = $this->resourceFile->getFileContent(
            $docserverAndFilePath->getFilePath(),
            $docserverAndFilePath->getDocserver()->getIsEncrypted()
        );

        $fileContent = $this->resourceFile->getWatermark($resId, $fileContentWithNoWatermark);
        if (empty($fileContent) || $fileContent === 'null') {
            $fileContent = $fileContentWithNoWatermark;
        }

        if ($fileContent === 'false') {
            throw new ExceptionResourceFailedToGetDocumentFromDocserver();
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
     * @throws  ExceptionParameterCanNotBeEmptyAndShould|ExecptionConvertedResult
     */
    private function getConvertedResourcePdfById(int $resId, string $collId = 'letterbox_coll'): ResourceConverted
    {
        if (empty($collId) || ($collId !== 'letterbox_coll' && $collId !== 'attachments_coll')) {
            throw new ExceptionParameterCanNotBeEmptyAndShould('collId', "'letterbox_coll' or 'attachments_coll'");
        }

        $document = $this->resourceData->getConvertedPdfById($resId, $collId);

        if (!empty($document['errors'])) {
            throw new ExecptionConvertedResult('Conversion error', $document['errors']);
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
