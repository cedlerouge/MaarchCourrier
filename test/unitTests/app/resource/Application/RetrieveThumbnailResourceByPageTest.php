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
use Resource\Domain\Exceptions\ConvertThumbnailException;
use Resource\Domain\Exceptions\ParameterMustBeGreaterThanZeroException;
use Resource\Domain\Exceptions\ResourceDoesNotExistException;
use Resource\Domain\Exceptions\ResourceOutOfPerimeterException;
use Resource\Domain\Exceptions\ResourcePageNotFoundException;
use Resource\Domain\Exceptions\ThumbnailNotFoundInDocserverOrNotReadableException;

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
        $this->expectExceptionObject(new ParameterMustBeGreaterThanZeroException('resId'));

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
        $this->expectExceptionObject(new ParameterMustBeGreaterThanZeroException('page'));

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

    public function testGetThumbnailFileByPageReturnAnExceptionWhenTheDocumentIsNull(): void
    {
        $this->resourceDataMock->doesResourceExist = false;

        $this->expectException(ResourceDoesNotExistException::class);
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,1);
    }

    public function testGetThumbnailFileByPageReturnAnExceptionWhenTheResourceIsOutOfThePerimeter(): void
    {
        $this->resourceDataMock->doesUserHasRights = false;

        $this->expectException(ResourceOutOfPerimeterException::class);
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,1);
    }

    public function testGetThumbnailFileByPageReturnAnExceptionWhenTheConvertOnePageToThumbnailReturnAnError(): void
    {
        $this->resourceFileMock->doesResourceConvertOnePageToThumbnailFailed = true;

        $this->expectException(ConvertThumbnailException::class);
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,1);
    }

    public function testGetThumbnailFileByPageReturnAnExceptionWhenTheFileIsNotFoundOrNotReadableInTheDocserver(): void
    {
        $this->resourceFileMock->doesFileExist = false;

        $this->expectException(ThumbnailNotFoundInDocserverOrNotReadableException::class);
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,1);
    }

    public function testGetThumbnailFileByPageReturnAnExceptionWhenTheResourcePageIsNotFound(): void
    {
        $this->resourceFileMock->doesResourceFileGetContentFail = true;

        $this->expectException(ResourcePageNotFoundException::class);
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,1);
    }

//    public function testGetThumbnailFileByPageReturnAnExceptionWhenTheNumberOfPagesIsZero(): void
//    {
//        $this->resourceFileMock->triggerAnExceptionWhenGetTheNumberOfPagesInThePdfFile = true;
//
//        $this->expectException(ResourcePageNotFoundException::class);
//        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1 ,1);
//    }

    /*public function testGetResourceVersionThumbnailByPageReturnAnExceptionWhen(): void
    {



        $this->expectException(ParameterCanNotBeEmptyException::class);
        $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1, 1,);
    }*/

   /* public function testGetResourceVersionThumbnailByPageReturnAnExceptionWhenTheDocumentIsNull(): void
    {
        $this->resourceFileMock->doesFileExist = false;

        $result = $this->retrieveThumbnailResourceByPage->getThumbnailFileByPage(1, 1,);

        $this->assertNull(null, $result);
    }*/

}
