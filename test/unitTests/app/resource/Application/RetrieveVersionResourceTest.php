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
use Resource\Application\RetrieveVersionResource;
use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmptyAndShould;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceDoesNotExist;
use Resource\Domain\Exceptions\ExceptionResourceFailedToGetDocumentFromDocserver;
use Resource\Domain\Exceptions\ExceptionResourceFingerPrintDoesNotMatch;
use Resource\Domain\Exceptions\ExceptionResourceHasNoFile;
use Resource\Domain\Exceptions\ExceptionResourceNotFoundInDocserver;

class RetrieveVersionResourceTest extends TestCase
{
    private ResourceDataMock $resourceDataMock;
    private ResourceFileMock $resourceFileMock;
    private RetrieveVersionResource $retrieveVersionResource;

    protected function setUp(): void
    {
        $this->resourceDataMock = new ResourceDataMock();
        $this->resourceFileMock = new ResourceFileMock();

        $this->retrieveVersionResource = new RetrieveVersionResource(
            $this->resourceDataMock,
            $this->resourceFileMock
        );
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseResIdNotValidParam(): void
    {
        // Arrange

        // Assert
        $this->expectExceptionObject(new ExceptionParameterMustBeGreaterThan('resId', 0));

        // Act
        $this->retrieveVersionResource->getResourceFile(0, 0, '');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseVersionNotValidParam(): void
    {
        // Arrange

        // Assert
        $this->expectExceptionObject(new ExceptionParameterMustBeGreaterThan('version', 0));

        // Act
        $this->retrieveVersionResource->getResourceFile(1, 0, '');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseTypeIsEmpty(): void
    {
        // Arrange

        // Assert
        $this->expectExceptionObject(new ExceptionParameterCanNotBeEmptyAndShould('type', implode(', ', $this->resourceDataMock::ADR_RESOURCE_TYPES)));

        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, '');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseTypeNotValidParam(): void
    {
        // Arrange

        // Assert
        $this->expectExceptionObject(new ExceptionParameterCanNotBeEmptyAndShould('type', implode(', ', $this->resourceDataMock::ADR_RESOURCE_TYPES)));

        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, 'TNLL');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseResourceDoesNotExist(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceExist = false;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceDoesNotExist());

        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseResourceHasNoFileReferenceInDatabase(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceFileExistInDatabase = false;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceHasNoFile());
        
        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseResourceUnknownDocserverReferenceInDatabase(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceDocserverExist = false;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceDocserverDoesNotExist());
        
        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseResourceFileDoesNotExistInDocserver(): void
    {
        // Arrange
        $this->resourceFileMock->doesFileExist = false;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceNotFoundInDocserver());
        
        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseResourceFingerprintDoesNotMatch(): void
    {
        // Arrange
        $this->resourceFileMock->documentFingerprint = 'other fingerprint';

        // Assert
        $this->expectExceptionObject(new ExceptionResourceFingerPrintDoesNotMatch());
        
        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');
    }

    /**
     * @return void
     */
    public function testCannotGetVersionResourceFileBecauseResourceFailedToGetContentFromDocserver(): void
    {
        // Arrange
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail =true;
        $this->resourceFileMock->doesResourceFileGetContentFail = true;

        // Assert
        $this->expectExceptionObject(new ExceptionResourceFailedToGetDocumentFromDocserver());
        
        // Act
        $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');
    }

    /**
     * @return void
     */
    public function testGetVersionResourceFileWithoutWatermarkBecauseAppliedWatermarkFailed(): void
    {
        // Arrange
        $this->resourceFileMock->returnResourceThumbnailFileContent = false;
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail = true;

        // Act
        $result = $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');

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
    public function testGetVersionResourceFileWithWatermarkApplied(): void
    {
        // Arrange
        $this->resourceFileMock->returnResourceThumbnailFileContent = false;

        // Act
        $result = $this->retrieveVersionResource->getResourceFile(1, 1, 'PDF');

        // Assert
        $this->assertNotEmpty($result->getPathInfo());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertNotEmpty($result->getOriginalFormat());
        $this->assertSame($result->getFormatFilename(), 'Maarch Courrier Test');
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->mainWatermarkInResourceFileContent);
    }
}