<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Application;

use MaarchCourrier\Tests\app\resource\Mock\ResourceDataMock;
use PHPUnit\Framework\TestCase;
use Resource\Domain\ResourceDataInterface;

class RetrieveDataTest extends TestCase
{
    private ResourceDataMock $resourceDataMock;

    protected function setUp(): void
    {
        $this->resourceDataMock = new ResourceDataMock();
    }

    /**
     * @return void
     */
    public function testGetMainResourceDataWithResId0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getMainResourceData(0);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'resId' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetSignResourceDataWithResId0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getSignResourceData(0, 1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'resId' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetSignResourceDataWithVersion0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getSignResourceData(1, 0);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'version' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetDocserverDataByDocserverIdWithDocserverIdIsEmptyExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getDocserverDataByDocserverId('');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'docserverId' parameter can not be empty");
    }

    /**
     * Provides data for testing the `formatFileName` function with no maximum length.
     *
     * @return array[] An array of test cases, each containing 'toFormat' (input string),
     *                 'maxLength' (maximum length allowed), and 'ExpectedFormat' (expected formatted string).
     */
    public function provideDataToFormatFileNameWithNoMaxLength()
    {
        // 260-character string
        $longString = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' .
        'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor ' .
        'in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, ' .
        'sunt in culpa qui officia deserunt mollit anim id est laborum.';

        $expectedString = substr($longString, 0, 250);

        return [
            'no special characters and max length is null' => [
                "toFormat"       => "Maarch Courrier.txt",
                "maxLength"      => null,
                "ExpectedFormat" => "Maarch Courrier.txt"
            ],
            "character '-' and max length is null" => [
                "toFormat"       => "Maarch-Courrier.txt",
                "maxLength"      => null,
                "ExpectedFormat" => "Maarch-Courrier.txt"
            ],
            "character '[]' and max length is null" => [
                "toFormat"       => "[Maarch-Courrier].txt",
                "maxLength"      => null,
                "ExpectedFormat" => "[Maarch-Courrier].txt"
            ],
            "mixed characters (:/*?) and max length is null" => [
                "toFormat"       => "file:with/special*characters?.txt",
                "maxLength"      => null,
                "ExpectedFormat" => "file_with_special_characters_.txt"
            ]
        ];
    }

    /**
     * @dataProvider provideDataToFormatFileNameWithNoMaxLength
     * @return void
     */
    public function testFormatFilenameWithDefaultMaxLengthExpectGoodFormat(string $toFormat, $maxLength, string $ExpectedFormat): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->formatFilename($toFormat);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, $ExpectedFormat);
    }

    /**
     * Provides data for testing the `formatFileName` function with maximum length.
     *
     * @return array[] An array of test cases, each containing 'toFormat' (input string),
     *                 'maxLength' (maximum length allowed), and 'ExpectedFormat' (expected formatted string).
     */
    public function provideDataToFormatFileNameWithMaxLength()
    {
        // 260-character string
        $longString = 'Lorem ipsum dolor sit amet- consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' .
        'Ut enim ad minim veniam- quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor ' .
        'in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident- ' .
        'sunt in culpa qui officia deserunt mollit anim id est laborum.';

        $expectedString = substr($longString, 0, 250);

        return [
            "mixed characters (\/\"\">>) and max length is 15" => [
                "toFormat"       => "file\with/\"special\">characters>.txt",
                "maxLength"      => 15,
                "ExpectedFormat" => "file\with__spec"
            ],
            "260 characters string expect 250 character" => [
                "toFormat"       => $longString,
                "maxLength"      => 250,
                "ExpectedFormat" => $expectedString
            ],
        ];
    }

    /**
     * @dataProvider provideDataToFormatFileNameWithMaxLength
     * @return void
     */
    public function testFormatFilenameExpectGoodFormat(string $toFormat, $maxLength, string $ExpectedFormat): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->formatFilename($toFormat, $maxLength);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertSame($result, $ExpectedFormat);
    }

    /**
     * @return void
     */
    public function testGetConvertedPdfByIdWithResIdIs0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getConvertedPdfById(0, 'something');

        // Assert
        $this->assertNotEmpty($result['errors']);
        $this->assertSame($result['errors'], "The 'resId' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetConvertedPdfByIdWithResIdIs1AndCollIdIsSomethingExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getConvertedPdfById(1, 'something');

        // Assert
        $this->assertNotEmpty($result['errors']);
        $this->assertSame($result['errors'], "The 'collId' parameter can not be empty and should be 'letterbox_coll' or 'attachments_coll'");
    }

    /**
     * @return void
     */
    public function testGetConvertedPdfByIdWithResIdIs1AndCollIdIsLetterboxCollExpectEmptyResult(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getConvertedPdfById(1, 'letterbox_coll');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertArrayNotHasKey('error', $result);
        $this->assertArrayHasKey('docserver_id', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('fingerprint', $result);
    }

    /**
     * @return void
     */
    public function testGetConvertedPdfByIdWithResIdIs1AndCollIdIsAttachmentsCollExpectEmptyResult(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getConvertedPdfById(1, 'attachments_coll');

        // Assert
        $this->assertNotEmpty($result);
        $this->assertArrayNotHasKey('error', $result);
        $this->assertArrayHasKey('docserver_id', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('fingerprint', $result);
    }

    /**
     * @return void
     */
    public function testGetResourceVersionWithResIdIs0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getResourceVersion(0, 'something', 1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'resId' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetResourceVersionWithTypeParamEmptyExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getResourceVersion(1, '', 1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'type' parameter should be : " . implode(', ', ResourceDataInterface::ADR_RESOURCE_TYPES));
    }

    /**
     * @return void
     */
    public function testGetResourceVersionWithTypeParamHasWrongTypeExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getResourceVersion(1, 'ME', 1);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'type' parameter should be : " . implode(', ', ResourceDataInterface::ADR_RESOURCE_TYPES));
    }

    /**
     * @return void
     */
    public function testGetResourceVersionWithVersionParamIsNotValidExpectError(): void
    {
        // Arrange
        $type = ResourceDataInterface::ADR_RESOURCE_TYPES[0];

        // Act
        $result = $this->resourceDataMock->getResourceVersion(1, $type, 0);

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'version' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetResourceVersionWithAllParamsAreValidExpectReturnResource(): void
    {
        // Arrange
        $type = ResourceDataInterface::ADR_RESOURCE_TYPES[0];

        // Act
        $result = $this->resourceDataMock->getResourceVersion(1, $type, 1);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertArrayNotHasKey('error', $result);
        $this->assertArrayHasKey('docserver_id', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('fingerprint', $result);
    }

    /**
     * @return void
     */
    public function testGetLatestResourceVersionWithResIdIs0ExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getLatestResourceVersion(0, 'something');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'resId' parameter must be greater than 0");
    }

    /**
     * @return void
     */
    public function testGetLatestResourceVersionWithTypeParamEmptyExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getLatestResourceVersion(1, '');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'type' parameter should be : " . implode(', ', ResourceDataInterface::ADR_RESOURCE_TYPES));
    }

    /**
     * @return void
     */
    public function testGetLatestResourceVersionWithTypeParamHasWrongTypeExpectError(): void
    {
        // Arrange

        // Act
        $result = $this->resourceDataMock->getLatestResourceVersion(1, 'ME');

        // Assert
        $this->assertNotEmpty($result['error']);
        $this->assertSame($result['error'], "The 'type' parameter should be : " . implode(', ', ResourceDataInterface::ADR_RESOURCE_TYPES));
    }

    /**
     * @return void
     */
    public function testGetLatestResourceVersionWithAllParamsAreValidExpectReturnResource(): void
    {
        // Arrange
        $type = ResourceDataInterface::ADR_RESOURCE_TYPES[0];

        // Act
        $result = $this->resourceDataMock->getLatestResourceVersion(1, $type);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertArrayNotHasKey('error', $result);
        $this->assertArrayHasKey('docserver_id', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('fingerprint', $result);
    }
}