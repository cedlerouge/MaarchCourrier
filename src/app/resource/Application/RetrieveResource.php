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
use Resource\Domain\Exceptions\ExceptionResourceFailedToGetDocumentFromDocserver;
use Resource\Domain\Exceptions\ExceptionResourceFingerPrintDoesNotMatch;
use Resource\Domain\Exceptions\ExceptionResourceHasNoFile;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Interfaces\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Interfaces\ResourceFileInterface;

class RetrieveResource
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
     * @param   int $resId  The ID of the resource.
     * @return  ResourceFileInfo
     */
    public function getResourceFile(int $resId): ResourceFileInfo
    {
        $document = $this->resourceData->getMainResourceData($resId);

        if (empty($document->getFilename())) {
            throw new ExceptionResourceHasNoFile();
        }

        $format     = $document->getFormat();
        $subject    = $document->getSubject();
        $creatorId  = $document->getTypist();

        $document  = $this->resourceData->getConvertedPdfById($resId, 'letterbox_coll');

        $docserver = $this->resourceData->getDocserverDataByDocserverId($document->getDocserverId());
        if (!$this->resourceFile->folderExists($docserver->getPathTemplate())) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        $filePath = $this->resourceFile->buildFilePath($document->getDocserverId(), $document->getPath(), $document->getFilename());
        if (!$this->resourceFile->fileExists($filePath)) {
            throw new ExceptionResourceNotFoundInDocserver();
        }

        $fingerprint = $this->resourceFile->getFingerPrint($docserver->getDocserverTypeId(), $filePath);
        if (empty($signdDocument) && empty($document->getFingerprint())) {
            $this->resourceData->updateFingerprint($resId, $fingerprint);
        }

        if ($document->getFingerprint() != $fingerprint) {
            throw new ExceptionResourceFingerPrintDoesNotMatch();
        }

        $fileContentWithNoWatermark = $this->resourceFile->getFileContent($filePath, $docserver->getIsEncrypted());

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
            pathInfo($filePath),
            $fileContent,
            $filename,
            $format
        );
    }
}