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

use Resource\Domain\Exceptions\ConvertThumbnailException;
use Resource\Domain\Exceptions\ParameterMustBeGreaterThanZeroException;
use Resource\Domain\Exceptions\ResourceDocserverDoesNotExistException;
use Resource\Domain\Exceptions\ResourceDoesNotExistException;
use Resource\Domain\Exceptions\ResourceFailedToGetDocumentFromDocserverException;
use Resource\Domain\Exceptions\ResourceNotFoundInDocserverException;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\ResourceFileInfo;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceConverted;

class RetrieveThumbnailResource
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
     * Retrieves the main file thumbnail info with watermark.
     *
     * @param int $resId The ID of the resource.
     *
     * @return  ResourceFileInfo
     *
     * @throws ResourceNotFoundInDocserverException
     * @throws ParameterMustBeGreaterThanZeroException
     * @throws ResourceDoesNotExistException
     * @throws ConvertThumbnailException
     * @throws ResourceFailedToGetDocumentFromDocserverException
     * @throws ResourceDocserverDoesNotExistException
     */
    public function getThumbnailFile(int $resId): ResourceFileInfo
    {
        if ($resId <= 0) {
            throw new ParameterMustBeGreaterThanZeroException('resId');
        }

        $document = $this->resourceData->getMainResourceData($resId);

        if ($document == null) {
            throw new ResourceDoesNotExistException();
        }

        $isDocserverEncrypted = false;
        $noThumbnailPath = 'dist/assets/noThumbnail.png';
        $pathToThumbnail = $noThumbnailPath;

        if (!empty($document->getFilename()) && $this->resourceData->hasRightByResId($resId, $GLOBALS['id'])) {
            $tnlDocument = $this->getResourceVersion($resId, 'TNL', $document->getVersion());

            if ($tnlDocument == null) {
                $latestPdfVersion = $this->resourceData->getLatestPdfVersion($resId, $document->getVersion());
                if ($latestPdfVersion == null) {
                    throw new ResourceDoesNotExistException();
                }

                $docserverAndFilePath = $this->retrieveResourceDocserverAndFilePath->getDocserverAndFilePath($latestPdfVersion);
                $fileContent = $this->resourceFile->getFileContent(
                    $docserverAndFilePath->getFilePath(),
                    $docserverAndFilePath->getDocserver()->getIsEncrypted()
                );
                if ($fileContent === null) {
                    throw new ResourceFailedToGetDocumentFromDocserverException();
                }

                $check = $this->resourceFile->convertToThumbnail(
                    $resId, $latestPdfVersion->getVersion(),
                    $fileContent, pathinfo($docserverAndFilePath->getFilePath(), PATHINFO_EXTENSION)
                );
                if (isset($check['errors'])) {
                    throw new ConvertThumbnailException($check['errors']);
                }
                $tnlDocument = $this->getResourceVersion($resId, 'TNL', $document->getVersion());
            }

            if ($tnlDocument != null) {
                $checkDocserver = $this->resourceData->getDocserverDataByDocserverId($tnlDocument->getDocserverId());
                $isDocserverEncrypted = $checkDocserver->getIsEncrypted() ?? false;

                $pathToThumbnail = $this->resourceFile->buildFilePath($checkDocserver->getPathTemplate(), $tnlDocument->getPath(), $tnlDocument->getFilename());
                if (!$this->resourceFile->fileExists($pathToThumbnail)) {
                    throw new ResourceNotFoundInDocserverException();
                }
            }
        }

        $pathInfo = pathinfo($pathToThumbnail);
        $fileContent = $this->resourceFile->getFileContent($pathToThumbnail, $isDocserverEncrypted);

        if ($fileContent === null) {
            $pathInfo = pathinfo($noThumbnailPath);
            $fileContent = $this->resourceFile->getFileContent($noThumbnailPath);
        }

        return new ResourceFileInfo(
            null,
            null,
            $pathInfo,
            $fileContent,
            "maarch.{$pathInfo['extension']}",
            ""
        );
    }

    private function getResourceVersion(int $resId, string $type, int $version): ?ResourceConverted
    {
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
