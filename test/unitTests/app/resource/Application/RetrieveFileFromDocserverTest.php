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

class RetrieveFileFromDocserverTest extends TestCase
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
    public function testGetOriginalMainFileWithResourceDoesNotExistExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceExist = false;

        // Act
        $result = $this->retrieveResource->getOriginalMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_DOES_NOT_EXIST);
    }

    /**
     * @return void
     */
    public function testGetOriginalMainFileWithResourceHasNoFileReferenceInDatabaseExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceFileExistInDatabase = false;
        
        // Act
        $result = $this->retrieveResource->getOriginalMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_HAS_NO_FILE);
    }

    /**
     * @return void
     */
    public function testGetOriginalMainFileWithResourceUnknownDocserverReferenceInDatabaseExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceDocserverExist = false;
        
        // Act
        $result = $this->retrieveResource->getOriginalMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST);
    }

    /**
     * @return void
     */
    public function testGetOriginalMainFileWithResourceFileDoesNotExistInDocserverExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->doesRessourceFileExistInDocserver = false;
        
        // Act
        $result = $this->retrieveResource->getOriginalMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER);
    }

    /**
     * @return void
     */
    public function testGetOriginalMainFileWithResourceFingerprintDoesNotMatchExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->documentFingerprint = 'other fingerprint';
        
        // Act
        $result = $this->retrieveResource->getOriginalMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH);
    }

    /**
     * @return void
     */
    public function testGetOriginalMainFileWithResourceFailedToGetContentFromDocserverExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->doesRessourceFileGetContentFaile = true;
        
        // Act
        $result = $this->retrieveResource->getOriginalMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER);
    }

    /**
     * @return void
     */
    public function testBuildFilePathWithDocserverIdIsEmptyExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceFileMock->buildFilePath('', 'document/path', 'filename');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, 'Error: Parameter docserverId can not be empty');
    }

    /**
     * @return void
     */
    public function testBuildFilePathWithDocumentPathIsEmptyExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceFileMock->buildFilePath('FASTHD_MAN', '', 'filename');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, 'Error: Parameter documentPath can not be empty');
    }

    /**
     * @return void
     */
    public function testBuildFilePathWithDocumentFileNameIsEmptyExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceFileMock->buildFilePath('FASTHD_MAN', 'document/path', '');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, 'Error: Parameter documentFilename can not be empty');
    }

    /**
     * @return void
     */
    public function testBuildFilePathWithAllParamsNotEmptyExpectNoErrors(): void
    {
        // Arrange
        $documentFilePath = $this->resourceFileMock->documentFilePath;
        $documentFilename = $this->resourceFileMock->documentFilename;
        $this->resourceFileMock->docserverPath = getcwd() . '/' . $this->resourceFileMock->docserverPath;
        $this->resourceFileMock->mainFilePath = $this->resourceFileMock->docserverPath . $documentFilePath . $documentFilename;

        // Act
        $result = $this->resourceFileMock->buildFilePath('FASTHD_MAN', $documentFilePath, $documentFilename);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, $this->resourceFileMock->mainFilePath);
    }
}