<?php

namespace Resource\Application;

use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceDocserverFilePathFingerPrint;

class RetrieveDocserverFilePathAndFingerPrint
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
     * @throws ExceptionResourceDocserverDoesNotExist
     * @throws ExceptionResourceNotFoundInDocserver
     */
    public function getDocserverFilePathAndFingerprint(object $document): ResourceDocserverFilePathFingerPrint
    {
        $docserver = $this->resourceData->getDocserverDataByDocserverId($document->getDocserverId());

        if ($docserver == null || !$this->resourceFile->folderExists($docserver->getPathTemplate())) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        $filePath = $this->resourceFile->buildFilePath($docserver->getPathTemplate(), $document->getPath(), $document->getFilename());

        if (!$this->resourceFile->fileExists($filePath)) {
            throw new ExceptionResourceNotFoundInDocserver();
        }

        $fingerPrint = $this->resourceFile->getFingerPrint($docserver->getDocserverTypeId(), $filePath);

        return new ResourceDocserverFilePathFingerPrint($docserver, $filePath, $fingerPrint);
    }
}
