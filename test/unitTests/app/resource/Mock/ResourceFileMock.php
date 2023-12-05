<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Exception;
use Resource\Domain\Exceptions\ExceptionConvertThumbnail;
use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmpty;
use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmptyAndShould;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use Resource\Domain\Ports\ResourceFileInterface;

class ResourceFileMock implements ResourceFileInterface
{
    public bool $doesResourceExist = true;
    public bool $doesResourceFileExistInDatabase = true;
    public bool $doesFolderExist = true;
    public bool $doesFileExist = true;
    public bool $doesDocserverPathExist = true;
    public bool $doesResourceFileFingerprintMatch = true;
    public bool $doesResourceFileGetContentFail = false;
    public bool $doesWatermarkInResourceFileContentFail = false;
    public bool $doesResourceConvertToThumbnailFailed = false;
    public bool $doesResourceConvertOnePageToThumbnailFailed = false;
    public bool $returnResourceThumbnailFileContent = false;
    public bool $triggerAnExceptionWhenGetTheNumberOfPagesInThePdfFile = false;

    public string $mainResourceFileContent = 'original file content';
    public string $mainWatermarkInResourceFileContent = 'watermark in file content';
    public string $docserverPath = 'install/samples/resources/';
    public string $documentFilePath  = '2021/03/0001/';
    public string $documentFilename  = '0001_960655724.pdf';
    public string $documentFingerprint  = 'file fingerprint';
    public string $resourceThumbnailFileContent  = 'resource thumbnail of an img';
    public string $noThumbnailFileContent  = 'thumbnail of no img';
    public ?string $mainFilePath  = null;

    /**
     * Build file path from docserver and document paths
     *
     * @param   string  $docserverPath
     * @param   string  $documentPath
     * @param   string  $documentFilename
     *
     * @return  string  Return the build file path or empty if docserverPath does not exist or empty
     */
    public function buildFilePath(string $docserverPath, string $documentPath, string $documentFilename): string
    {
        if (empty($this->docserverPath) || !$this->doesDocserverPathExist) {
            return null;
        }

        return $this->docserverPath . str_replace('#', DIRECTORY_SEPARATOR, $documentPath) . $documentFilename;
    }

    public function folderExists(string $folderPath): bool
    {
        if (empty($folderPath)) {
            return false;
        }
        return $this->doesFolderExist;
    }

    public function fileExists(string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }
        return $this->doesFileExist;
    }

    public function getFingerPrint(string $docserverTypeId, string $filePath): string
    {
        if (empty($docserverTypeId)) {
            return '';
        }
        if (empty($filePath)) {
            return '';
        }

        return $this->documentFingerprint;
    }

    /**
     * Retrieves file content.
     *
     * @param   string  $filePath       The path to the file.
     * @param   bool    $isEncrypted    Flag if the file is encrypted.
     *
     * @return string|'false' Returns the content of the file as a string if successful, or a string with value 'false' on failure.
     */
    public function getFileContent(string $filePath, bool $isEncrypted = false): string
    {
        if (empty($filePath)) {
            return 'false';
        }

        if ($this->doesResourceFileGetContentFail) {
            if ($this->returnResourceThumbnailFileContent && strpos($filePath, 'noThumbnail.png') !== false) {
                return $this->noThumbnailFileContent;
            }
            return 'false';
        }

        if ($this->returnResourceThumbnailFileContent && strpos($filePath, 'noThumbnail.png') !== false) {
            return $this->noThumbnailFileContent;
        }

        return $this->returnResourceThumbnailFileContent ? $this->resourceThumbnailFileContent : $this->mainResourceFileContent;
    }

    /**
     * Retrieves file content with watermark.
     *
     * @param   int     $resId          Resource id.
     * @param   string  $ffileContent   The path to the file.
     *
     * @return  string|'null'   Returns the content of the file as a string if successful, or a string with value 'null' on failure.
     */
    public function getWatermark(int $resId, string $fileContent): string
    {
        if ($resId <= 0) {
            return 'null';
        }

        if (!$this->doesWatermarkInResourceFileContentFail) {
            return $this->mainWatermarkInResourceFileContent;
        } else {
            return 'null';
        }
    }

    public function convertToThumbnail(int $resId): array
    {
        if ($this->doesResourceConvertToThumbnailFailed) {
            return ['errors' => 'Convertion to thumbnail failed'];
        }
        return [];
    }

    /**
     * Convert resource page to thumbnail.
     *
     * @param   int     $resId  Resource id.
     * @param   string  $type   Resource type, 'resource' or 'attachment'.
     * @param   int     $page   Resource page number.
     *
     * @return  string   If returned contains 'errors:' then the convertion failed
     */
    public function convertOnePageToThumbnail(int $resId, string $type, int $page): string
    {
        return 'true';
    }

    /**
     * Retrieves the number of pages in a pdf file
     *
     * @param   string  $filePath   Resource path.
     *
     * @return  int     Number of pages.
     *
     * @throws  Exception|PdfParserException
     */
    public function getTheNumberOfPagesInThePdfFile(string $filePath): int
    {
        if (empty($filePath)) {
            throw new Exception("Throw an exception when get pdf file");
        }

        if ($this->triggerAnExceptionWhenGetTheNumberOfPagesInThePdfFile) {
            throw new Exception("Throw an exception when parsing pdf file");
        }

        return 1;
    }
}
