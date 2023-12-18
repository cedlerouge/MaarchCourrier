<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Application;

use Exception;
use MaarchCourrier\Tests\app\resource\Mock\ResourceDataMock;
use MaarchCourrier\Tests\app\resource\Mock\ResourceFileMock;
use PHPUnit\Framework\TestCase;
use Resource\Application\RetrieveDocserverAndFilePath;
use Resource\Application\RetrieveOriginalResource;
use Resource\Domain\Exceptions\ResourceDocserverDoesNotExistException;
use Resource\Domain\Exceptions\ResourceDoesNotExistException;
use Resource\Domain\Exceptions\ResourceFailedToGetDocumentFromDocserverException;
use Resource\Domain\Exceptions\ResourceFingerPrintDoesNotMatchException;
use Resource\Domain\Exceptions\ResourceHasNoFileException;
use Resource\Domain\Exceptions\ResourceNotFoundInDocserverException;

class RetrieveOriginalResourceTest extends TestCase
{
    private ResourceDataMock $resourceDataMock;
    private ResourceFileMock $resourceFileMock;
    private RetrieveOriginalResource $retrieveOriginalResource;

    protected function setUp(): void
    {
        $this->resourceDataMock = new ResourceDataMock();
        $this->resourceFileMock = new ResourceFileMock();

        $this->retrieveOriginalResource = new RetrieveOriginalResource(
            $this->resourceDataMock,
            $this->resourceFileMock,
            new RetrieveDocserverAndFilePath($this->resourceDataMock, $this->resourceFileMock)
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCannotGetOriginalResourceFileBecauseResourceDoesNotExist(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceExist = false;

        // Assert
        $this->expectExceptionObject(new ResourceDoesNotExistException());

        // Act
        $this->retrieveOriginalResource->getResourceFile(1);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCannotGetOriginalResourceFileBecauseResourceHasNoFileReferenceInDatabase(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceFileExistInDatabase = false;

        // Assert
        $this->expectExceptionObject(new ResourceHasNoFileException());

        // Act
        $this->retrieveOriginalResource->getResourceFile(1);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCannotGetOriginalResourceFileBecauseResourceHasUnknownDocserverReferenceInDatabase(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceDocserverExist = false;

        // Assert
        $this->expectExceptionObject(new ResourceDocserverDoesNotExistException());

        // Act
        $this->retrieveOriginalResource->getResourceFile(1);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCannotGetOriginalResourceFileBecauseResourceFileDoesNotExistInDocserver(): void
    {
        // Arrange
        $this->resourceFileMock->doesFileExist = false;

        // Assert
        $this->expectExceptionObject(new ResourceNotFoundInDocserverException());

        // Act
        $this->retrieveOriginalResource->getResourceFile(1);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCannotGetOriginalResourceFileBecauseResourceFingerprintDoesNotMatch(): void
    {
        // Arrange
        $this->resourceFileMock->documentFingerprint = 'other fingerprint';

        // Assert
        $this->expectExceptionObject(new ResourceFingerPrintDoesNotMatchException());

        // Act
        $this->retrieveOriginalResource->getResourceFile(1);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCannotGetOriginalResourceFileBecauseResourceFailedToGetContentFromDocserver(): void
    {
        // Arrange
        $this->resourceFileMock->doesResourceFileGetContentFail = true;

        // Assert
        $this->expectExceptionObject(new ResourceFailedToGetDocumentFromDocserverException());

        // Act
        $this->retrieveOriginalResource->getResourceFile(1);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetOriginalResourceFile(): void
    {
        // Arrange
        $this->resourceFileMock->returnResourceThumbnailFileContent = false;

        // Act
        $result = $this->retrieveOriginalResource->getResourceFile(1);

        // Assert
        $this->assertNotEmpty($result->getPathInfo());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertNotEmpty($result->getOriginalFormat());
        $this->assertSame($result->getFormatFilename(), 'Maarch Courrier Test');
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->mainResourceFileContent);
    }
}
