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
use Resource\Application\RetrieveDocserverAndFilePath;
use Resource\Application\RetrieveResource;
use Resource\Domain\Exceptions\ResourceDocserverDoesNotExistException;
use Resource\Domain\Exceptions\ResourceDoesNotExistException;
use Resource\Domain\Exceptions\ResourceFailedToGetDocumentFromDocserverException;
use Resource\Domain\Exceptions\ResourceFingerPrintDoesNotMatchException;
use Resource\Domain\Exceptions\ResourceHasNoFileException;
use Resource\Domain\Exceptions\ResourceNotFoundInDocserverException;

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
            $this->resourceFileMock,
            new RetrieveDocserverAndFilePath($this->resourceDataMock, $this->resourceFileMock)
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
        $this->expectExceptionObject(new ResourceDoesNotExistException());

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
        $this->expectExceptionObject(new ResourceHasNoFileException());

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
        $this->expectExceptionObject(new ResourceDocserverDoesNotExistException());

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
        $this->expectExceptionObject(new ResourceNotFoundInDocserverException());

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
        $this->expectExceptionObject(new ResourceFingerPrintDoesNotMatchException());

        // Act
        $this->retrieveResource->getResourceFile(1);
    }

    /**
     * @return void
     */
    public function testCannotGetMainFileBecauseResourceFailedToGetContentFromDocserver(): void
    {
        // Arrange
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail = true;
        $this->resourceFileMock->doesResourceFileGetContentFail = true;

        // Assert
        $this->expectExceptionObject(new ResourceFailedToGetDocumentFromDocserverException());

        // Act
        $this->retrieveResource->getResourceFile(1);
    }

    /**
     * @return void
     */
    public function testGetResourceFileWithoutWatermarkBecauseAppliedWatermarkFailed(): void
    {
        // Arrange
        $this->resourceFileMock->returnResourceThumbnailFileContent = false;
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail = true;

        // Act
        $result = $this->retrieveResource->getResourceFile(1);

        // Assert
        $this->assertNotEmpty($result->getPathInfo());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertNotEmpty($result->getOriginalFormat());
        $this->assertSame($result->getFormatFilename(), 'Maarch Courrier Test');
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->mainResourceFileContent);
    }

    /**
     * @return void
     */
    public function testGetResourceFileWithWatermarkApplied(): void
    {
        // Arrange
        $this->resourceFileMock->returnResourceThumbnailFileContent = false;

        // Act
        $result = $this->retrieveResource->getResourceFile(1);

        // Assert
        $this->assertNotEmpty($result->getPathInfo());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertNotEmpty($result->getOriginalFormat());
        $this->assertSame($result->getFormatFilename(), 'Maarch Courrier Test');
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->mainWatermarkInResourceFileContent);
    }
}
