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
use Resource\Domain\Models\ResourceFileInfo;
use Resource\Domain\Interfaces\ResourceFileInterface;

class RetrieveOriginalResource
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
     * Retrieves the resource file info.
     *
     * @param   int  $resId             The ID of the resource.
     * @param   bool $isSignedVersion   (Optional) Whether to retrieve the signed version. Default is false.
     *
     * @return  ResourceFileInfo
     * 
     * @throws  \Exception
     */
    public function getResourceFile(int $resId, bool $isSignedVersion = false): ResourceFileInfo
    {
        $document = $this->resourceData->getMainResourceData($resId);

        if (empty($document->getFilename())) {
            throw new ExceptionResourceHasNoFile();
        }

        $format = $document->getFormat();

        $signdDocument = null;
        if($isSignedVersion) {
            $signdDocument = $this->resourceData->getSignResourceData($resId, $document->getVersion());

            if (!empty($signdDocument)) {
                $signdDocument->setSubject($document->getSubject());
                $document = $signdDocument;
            }
        }
        
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

        $filename = $this->resourceData->formatFilename($document->getSubject());

        $fileContent = $this->resourceFile->getFileContent($filePath, $docserver->getIsEncrypted());
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
}