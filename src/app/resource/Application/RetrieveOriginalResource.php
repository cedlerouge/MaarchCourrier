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
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceFailedToGetDocumentFromDocserver;
use Resource\Domain\Exceptions\ExceptionResourceFingerPrintDoesNotMatch;
use Resource\Domain\Exceptions\ExceptionResourceHasNoFile;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Ports\ResourceFileInterface;

class RetrieveOriginalResource
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
     * Retrieves the resource file info.
     *
     * @param int $resId The ID of the resource.
     * @param bool $isSignedVersion (Optional) Whether to retrieve the signed version. Default is false.
     *
     * @return  ResourceFileInfo
     * @throws ExceptionParameterMustBeGreaterThan
     * @throws ExceptionResourceDoesNotExist
     * @throws ExceptionResourceHasNoFile
     * @throws ExceptionResourceFingerPrintDoesNotMatch
     * @throws ExceptionResourceFailedToGetDocumentFromDocserver
     * @throws ExceptionResourceDocserverDoesNotExist
     * @throws ExceptionResourceNotFoundInDocserver
     */
    public function getResourceFile(int $resId, bool $isSignedVersion = false): ResourceFileInfo
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

        $format = $document->getFormat();

        $signdDocument = null;
        if($isSignedVersion) {
            $signdDocument = $this->resourceData->getSignResourceData($resId, $document->getVersion());

            if ($signdDocument != null) {
                $signdDocument->setSubject($document->getSubject());
                $document = $signdDocument;
            }
        }

        try {
            $docserverFilePathAndFingerprint = $this->retrieveResourceDocserverFilePathFingerPrint->getDocserverFilePathAndFingerprint(
                $document
            );
        } catch (ExceptionResourceDocserverDoesNotExist|ExceptionResourceNotFoundInDocserver $e) {
            throw $e;
        }

        if ($signdDocument == null && !empty($docserverFilePathAndFingerprint->getFingerprint()) && empty($document->getFingerprint())) {
            $this->resourceData->updateFingerprint($resId, $docserverFilePathAndFingerprint->getFingerprint());
        }

        if ($document->getFingerprint() != $docserverFilePathAndFingerprint->getFingerprint()) {
            throw new ExceptionResourceFingerPrintDoesNotMatch();
        }

        $filename = $this->resourceData->formatFilename($document->getSubject());

        $fileContent = $this->resourceFile->getFileContent(
            $docserverFilePathAndFingerprint->getFilePath(),
            $docserverFilePathAndFingerprint->getDocserver()->getIsEncrypted()
        );
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
}
