<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Application;

use MaarchCourrier\Tests\app\resource\Mock\ResourceDataMock;
use MaarchCourrier\Tests\app\resource\Mock\ResourceFileMock;
use PHPUnit\Framework\TestCase;
use Resource\Application\RetrieveResource;
use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceFailedToGetDocumentFromDocserver;
use Resource\Domain\Exceptions\ExceptionResourceFingerPrintDoesNotMatch;
use Resource\Domain\Exceptions\ExceptionResourceHasNoFile;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;

class RetrieveResourceTest extends TestCase
{
    private ResourceDataMock $resourceDataMock;
    private ResourceFileMock $resourceFileMock;
    private RetrieveResource $retrieveResource;

    protected function setUp(): void
    {
        $this->resourceDataMock = new ResourceDataMock();
        $this->resourceFileMock = new ResourceFileMock();

        $this->retrieveResource = new RetrieveResource(
            $this->resourceDataMock,
            $this->resourceFileMock
        );
    }

    /**
     * @return void
     */
    public function testCannotGetMainFileBecauseResourceDoesNotExist(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceExist = false;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceDoesNotExist());

        // Act
        $this->retrieveResource->getResourceFile(1);
    }

    /**
     * @return void
     */
    public function testCannotGetMainFileBecauseResourceHasNoFileReferenceInDatabase(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceFileExistInDatabase = false;
        
        // Assert
        $this->expectExceptionObject(new ExceptionResourceHasNoFile());

        // Act
        $this->retrieveResource->getResourceFile(1);
    }

    /**
     * @return void
     */
    public function testCannotGetMainFileBecauseResourceUnknownDocserverReferenceInDatabase(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceDocserverExist = false;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceDocserverDoesNotExist());
        
        // Act
        $this->retrieveResource->getResourceFile(1);
    }

    /**
     * @return void
     */
    public function testCannotGetMainFileBecauseResourceFileDoesNotExistInDocserver(): void
    {
        // Arrange
        $this->resourceFileMock->doesFileExist = false;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceNotFoundInDocserver());
        
        // Act
        $this->retrieveResource->getResourceFile(1);
    }

    /**
     * @return void
     */
    public function testCannotGetMainFileBecauseResourceFingerprintDoesNotMatch(): void
    {
        // Arrange
        $this->resourceFileMock->documentFingerprint = 'other fingerprint';

        // Assert
        $this->expectExceptionObject(new ExceptionResourceFingerPrintDoesNotMatch());
        
        // Act
        $this->retrieveResource->getResourceFile(1);
    }

    /**
     * @return void
     */
    public function testCannotGetMainFileBecaseResourceFailedToGetContentFromDocserver(): void
    {
        // Arrange
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail = true;
        $this->resourceFileMock->doesResourceFileGetContentFail = true;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceFailedToGetDocumentFromDocserver());
        
        // Act
        $this->retrieveResource->getResourceFile(1);
    }
}