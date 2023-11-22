<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Application;

use MaarchCourrier\Tests\app\resource\Mock\ResourceFileMock;
use PHPUnit\Framework\TestCase;


class RetrieveFileTest extends TestCase
{
    private ResourceFileMock $resourceFileMock;

    protected function setUp(): void
    {
        $this->resourceFileMock = new ResourceFileMock();
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

    /**
     * @return void
     */
    public function testFolderExistsWithEmptyFolderPathExpectErrorReturnFalse(): void
    {
        // Arrange

        // Act
        $result = $this->resourceFileMock->folderExists('');

        // Assert
        $this->assertIsBool($result);
        $this->assertSame($result, false);
    }

    /**
     * @return void
     */
    public function testFileExistsWithEmptyFilePathExpectErrorReturnFalse(): void
    {
        // Arrange

        // Act
        $result = $this->resourceFileMock->fileExists('');

        // Assert
        $this->assertIsBool($result);
        $this->assertSame($result, false);
    }

    /**
     * @return void
     */
    public function testGetFingerPrintWithEmptyDocserverTypeIdExpectError(): void
    {
        // Arrange
        $docserverTypeId = '';
        $filePath = '/some/file/path/sample.pdf';

        // Act
        $result = $this->resourceFileMock->getFingerPrint($docserverTypeId, $filePath);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Error: ', $result);
    }

    /**
     * @return void
     */
    public function testGetFingerPrintWithEmptyFilePathExpectError(): void
    {
        // Arrange
        $docserverTypeId = 'DOC';
        $filePath = '';

        // Act
        $result = $this->resourceFileMock->getFingerPrint($docserverTypeId, $filePath);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Error: ', $result);
    }

    /**
     * @return void
     */
    public function testGetFingerPrintWithDocserverTypeIdAndFilePathNotEmptyExpectFingerPrint(): void
    {
        // Arrange
        $docserverTypeId = 'DOC';
        $filePath = '/some/file/path/sample.pdf';

        // Act
        $result = $this->resourceFileMock->getFingerPrint($docserverTypeId, $filePath);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, $this->resourceFileMock->documentFingerprint);
    }

    /**
     * @return void
     */
    public function testGetFileContentWithFilePathEmptyExpectReturnValueStringAsFalse(): void
    {
        // Arrange

        // Act
        $result = $this->resourceFileMock->getFileContent('');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, 'false');
    }

    /**
     * @return void
     */
    public function testGetFileContentWithFilePathNotEmptyExpectContent(): void
    {
        // Arrange
        $filePath = 'install/samples/resources/2021/03/0001/0001_960655724.pdf';

        // Act
        $result = $this->resourceFileMock->getFileContent($filePath);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, $this->resourceFileMock->mainResourceOriginalFileContent);
    }

    /**
     * @return void
     */
    public function testGetWatermarkWithResIdIsNotValidExpectReturnValueStringAsNull(): void
    {
        // Arrange

        // Act
        $result = $this->resourceFileMock->getWatermark(0, 'smt');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, 'null');
    }

    /**
     * @return void
     */
    public function testGetWatermarkWithFilePathEmptyExpectReturnValueStringAsNull(): void
    {
        // Arrange
        $this->resourceFileMock->doesWatermarkInResourceFileContentFail = true;

        // Act
        $result = $this->resourceFileMock->getWatermark(1, '');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, 'null');
    }
}