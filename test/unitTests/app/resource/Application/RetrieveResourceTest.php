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
use Resource\Domain\ResourceDataInterface;
use Resource\Domain\ResourceDataType;

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
    public function testGetResourceDataByTypeWithResId0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->retrieveResource->getResourceDataByType(0, '', 0);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'resId' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetResourceDataByTypeWithResourceDataTypeIsEmptyExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->retrieveResource->getResourceDataByType(1, '');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'resourceDataType' parameter should be : " . implode(', ', ResourceDataType::TYPES));
    }

    /**
     * @return void
     */
    public function testGetResourceDataByDataTypeWithVersionIs0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->retrieveResource->getResourceDataByType(1, ResourceDataType::DEFAULT, 0);

        // Assert
        $this->assertArrayNotHasKey('error', $result);
    }

    /**
     * @return void
     */
    public function testGetResourceDataByDataTypeWithDataTypeIsSignedAndNoVersionParamExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->retrieveResource->getResourceDataByType(1, ResourceDataType::SIGNED);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'version' parameter must be greater than 0 for ResourceDataType is signed");
    }

    /**
     * @return void
     */
    public function testGetResourceDataByTypeWithDataTypeIsVersionAndNoVersionParamExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->retrieveResource->getResourceDataByType(1, ResourceDataType::VERSION);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'version' parameter must be greater than 0 for ResourceDataType is version");
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
        $this->resourceFileMock->doesFileExist = false;
        
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
        $this->resourceFileMock->doesRessourceFileGetContentFail = true;
        
        // Act
        $result = $this->retrieveResource->getOriginalMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER);
    }

    /**
     * @return void
     */
    public function testGetMainFileWithResourceDoesNotExistExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceExist = false;

        // Act
        $result = $this->retrieveResource->getMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_DOES_NOT_EXIST);
    }

    /**
     * @return void
     */
    public function testGetMainFileWithResourceHasNoFileReferenceInDatabaseExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceFileExistInDatabase = false;
        
        // Act
        $result = $this->retrieveResource->getMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_HAS_NO_FILE);
    }

    /**
     * @return void
     */
    public function testGetMainFileWithResourceUnknownDocserverReferenceInDatabaseExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceDocserverExist = false;
        
        // Act
        $result = $this->retrieveResource->getMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST);
    }

    /**
     * @return void
     */
    public function testGetMainFileWithResourceFileDoesNotExistInDocserverExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->doesFileExist = false;
        
        // Act
        $result = $this->retrieveResource->getMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER);
    }

    /**
     * @return void
     */
    public function testGetMainFileWithResourceFingerprintDoesNotMatchExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->documentFingerprint = 'other fingerprint';
        
        // Act
        $result = $this->retrieveResource->getMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH);
    }

    /**
     * @return void
     */
    public function testGetMainFileWithResourceFailedToGetContentFromDocserverExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail =true;
        $this->resourceFileMock->doesRessourceFileGetContentFail = true;
        
        // Act
        $result = $this->retrieveResource->getMainFile(1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER);
    }







    /**
     * @return void
     */
    public function testGetVersionMainFileWithResIdNotValidParamExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->retrieveResource->getVersionMainFile(0, 0, '');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'resId' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceDoesNotExistExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceExist = false;

        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 0, '');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_DOES_NOT_EXIST);
    }

    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceHasNoFileReferenceInDatabaseExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceFileExistInDatabase = false;
        
        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 0, 'smt');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_HAS_NO_FILE);
    }

    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceDataTypeIsVersionAndVersionNotValidParamExpectError(): void
    {
        // Arrange
        
        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 0, 'smt');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'version' parameter must be greater than 0 for ResourceDataType is version");
    }

    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceDataTypeIsVersionVersionIsValidParamAndParamTypeIsNotValidExpectError(): void
    {
        // Arrange
        
        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 1, 'smt');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'type' parameter must be one of theses types: " . implode(', ', ResourceDataInterface::ADR_RESOURCE_TYPES));
    }


    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceUnknownDocserverReferenceInDatabaseExpectError(): void
    {
        // Arrange
        $this->resourceDataMock->doesRessourceDocserverExist = false;
        
        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 1, 'PDF');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceDataMock::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST);
    }

    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceFileDoesNotExistInDocserverExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->doesFileExist = false;
        
        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 1, 'PDF');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER);
    }

    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceFingerprintDoesNotMatchExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->documentFingerprint = 'other fingerprint';
        
        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 1, 'PDF');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH);
    }

    /**
     * @return void
     */
    public function testGetVersionMainFileWithResourceFailedToGetContentFromDocserverExpectError(): void
    {
        // Arrange
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail =true;
        $this->resourceFileMock->doesRessourceFileGetContentFail = true;
        
        // Act
        $result = $this->retrieveResource->getVersionMainFile(1, 1, 'PDF');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], $this->resourceFileMock::ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER);
    }
}