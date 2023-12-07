<?php

namespace Resource\Application;

use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceDocserverAndFilePath;

class RetrieveDocserverAndFilePath
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
    public function getDocserverAndFilePath(object $document): ResourceDocserverAndFilePath
    {
        $docserver = $this->resourceData->getDocserverDataByDocserverId($document->getDocserverId());

        if ($docserver == null || !$this->resourceFile->folderExists($docserver->getPathTemplate())) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        $filePath = $this->resourceFile->buildFilePath($docserver->getPathTemplate(), $document->getPath(), $document->getFilename());

        if (!$this->resourceFile->fileExists($filePath)) {
            throw new ExceptionResourceNotFoundInDocserver();
        }

        return new ResourceDocserverAndFilePath($docserver, $filePath);
    }
}
