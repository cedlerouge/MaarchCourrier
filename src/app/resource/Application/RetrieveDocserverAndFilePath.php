<?php

namespace Resource\Application;

use Resource\Domain\Exceptions\ResourceDocserverDoesNotExistException;
use Resource\Domain\Exceptions\ResourceNotFoundInDocserverException;
use Resource\Domain\HasDocserverFileInterface;
use Resource\Domain\Ports\ResourceDataInterface;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\Domain\ResourceDocserverAndFilePath;

class RetrieveDocserverAndFilePath
{
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;

    public function __construct(
        ResourceDataInterface $resourceDataInterface,
        ResourceFileInterface $resourceFileInterface
    ) {
        $this->resourceData = $resourceDataInterface;
        $this->resourceFile = $resourceFileInterface;
    }

    /**
     * @throws ResourceDocserverDoesNotExistException
     * @throws ResourceNotFoundInDocserverException
     */
    public function getDocserverAndFilePath(HasDocserverFileInterface $document): ResourceDocserverAndFilePath
    {
        $docserver = $this->resourceData->getDocserverDataByDocserverId($document->getDocserverId());

        if ($docserver == null || !$this->resourceFile->folderExists($docserver->getPathTemplate())) {
            throw new ResourceDocserverDoesNotExistException();
        }

        $filePath = $this->resourceFile->buildFilePath(
            $docserver->getPathTemplate(),
            $document->getPath(),
            $document->getFilename()
        );

        if (!$this->resourceFile->fileExists($filePath)) {
            throw new ResourceNotFoundInDocserverException();
        }

        return new ResourceDocserverAndFilePath($docserver, $filePath);
    }
}
