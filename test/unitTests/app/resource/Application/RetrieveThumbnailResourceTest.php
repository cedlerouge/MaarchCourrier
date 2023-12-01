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
use Resource\Application\RetrieveThumbnailResource;
use Resource\Domain\Exceptions\ExceptionConvertThumbnail;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;

class RetrieveThumbnailResourceTest extends TestCase
{
    private ResourceDataMock $resourceDataMock;
    private ResourceFileMock $resourceFileMock;
    private RetrieveThumbnailResource $retrieveThumbnailResource;

    protected function setUp(): void
    {
        $this->resourceDataMock = new ResourceDataMock();
        $this->resourceFileMock = new ResourceFileMock();

        $this->retrieveThumbnailResource = new RetrieveThumbnailResource(
            $this->resourceDataMock,
            $this->resourceFileMock
        );
    }

    /**
     * @return void
     */
    public function testCannotGetThumbnailFileBecauseResId0(): void
    {
        // Arrange
        
        // Assert
        $this->expectExceptionObject(new ExceptionParameterMustBeGreaterThan('resId', 0));

        // Act
        $this->retrieveThumbnailResource->getThumbnailFile(0);
    }

    /**
     * @return void
     */
    public function testCannotGetThumbnailFileBecauseResourceHasNoFileExpectNoThumbnailFile(): void
    {
        // Arrange
        $this->resourceDataMock->returnResourceWithoutFile = true;
        $this->resourceFileMock->returnResourceThumbnailFileContent = true;
        
        // Act
        $result = $this->retrieveThumbnailResource->getThumbnailFile(1);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertInstanceOf('\Resource\Domain\Models\ResourceFileInfo', $result);
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertSame($result->getFormatFilename(), 'maarch.png');
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->noThumbnailFileContent);
    }

    /**
     * @return void
     */
    public function testCannotGetThumbnailFileBecauseGlobalUserHasNoRightsExpectNoThumbnailFile(): void
    {
        // Arrange
        $this->resourceDataMock->doesUserHasRights = false;
        $this->resourceFileMock->returnResourceThumbnailFileContent = true;
        
        // Act
        $result = $this->retrieveThumbnailResource->getThumbnailFile(1);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertInstanceOf('\Resource\Domain\Models\ResourceFileInfo', $result);
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertSame($result->getFormatFilename(), 'maarch.png');
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->noThumbnailFileContent);
    }

    /**
     * @return void
     */
    public function testCannotGetThumbnailFileBecauseOfNoDocumentVersionAndThumbnailConvertionFailed(): void
    {
        // Arrange
        $this->resourceDataMock->doesResourceVersionExist = false;
        $this->resourceFileMock->doesResourceConvertToThumbnailFailed = true;

        // Assert
        $this->expectExceptionObject(new ExceptionConvertThumbnail('Convertion to thumbnail failed'));

        // Act
        $this->retrieveThumbnailResource->getThumbnailFile(1);
    }

    /**
     * @return void
     */
    public function testCannotGetThumbnailFileBecauseResourceFailedToGetContentFromDocserverExpectNoThumbnailFile(): void
    {
        // Arrange
        $this->resourceFileMock->doesResourceFileGetContentFail = true;
        $this->resourceFileMock->returnResourceThumbnailFileContent = true;
        
        // Act
        $result = $this->retrieveThumbnailResource->getThumbnailFile(1);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertInstanceOf('\Resource\Domain\Models\ResourceFileInfo', $result);
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertSame($result->getFormatFilename(), 'maarch.png');
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->noThumbnailFileContent);
    }
}