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
use MaarchCourrier\Tests\app\resource\Mock\ResourceLogMock;
use PHPUnit\Framework\TestCase;
use Resource\Application\RetrieveThumbnailResourceByPage;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;

class RetrieveThumbnailResourceByPageTest extends TestCase
{
    private ResourceDataMock $resourceDataMock;
    private ResourceFileMock $resourceFileMock;
    private ResourceLogMock  $resourceLogMock;
    private RetrieveThumbnailResourceByPage $retrieveThumbnailResourceByPage;

    protected function setUp(): void
    {
        $this->resourceDataMock = new ResourceDataMock();
        $this->resourceFileMock = new ResourceFileMock();
        $this->resourceLogMock  = new ResourceLogMock();

        $this->retrieveThumbnailResourceByPage = new RetrieveThumbnailResourceByPage(
            $this->resourceDataMock,
            $this->resourceFileMock,
            $this->resourceLogMock
        );
    }

    /**
     * @return void
     */
    public function testCannotGetThumbnailFileByPageBecauseResId0(): void
    {
        // Arrange
        
        // Assert
        $this->expectExceptionObject(new ExceptionParameterMustBeGreaterThan('resId', 0));

        // Act
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(0 ,0);
    }

    /**
     * @return void
     */
    public function testCannotGetThumbnailFileByPageBecausePage0(): void
    {
        // Arrange
        
        // Assert
        $this->expectExceptionObject(new ExceptionParameterMustBeGreaterThan('page', 0));

        // Act
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,0);
    }

    /**
     * @return void
     */
    public function testGetThumbnailFileByPage1(): void
    {
        // Arrange
        $this->resourceFileMock->returnResourceThumbnailFileContent = true;
        
        // Act
        $result = $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,1);
        
        // Assert
        $this->assertNotEmpty($result->getPathInfo());
        $this->assertNotEmpty($result->getFileContent());
        $this->assertNotEmpty($result->getFormatFilename());
        $this->assertSame($result->getFormatFilename(), "Maarch Courrier Test");
        $this->assertSame($result->getFileContent(), $this->resourceFileMock->resourceThumbnailFileContent);
    }
}