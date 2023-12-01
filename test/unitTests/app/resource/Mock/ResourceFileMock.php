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
use Resource\Domain\Interfaces\ResourceFileInterface;

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
     * Build file path from document and docserver
     * 
     * @param   string  $docserverId
     * @param   string  $documentPath
     * @param   string  $documentFilename
     * 
     * @return  string  Return the build file path
     * 
     * @throws  ExceptionParameterCanNotBeEmpty|ExceptionResourceDocserverDoesNotExist
     */
    public function buildFilePath(string $docserverId, string $documentPath, string $documentFilename): string
    {
        if (empty($docserverId)) {
            throw new ExceptionParameterCanNotBeEmpty('docserverId');
        }
        if (empty($documentPath)) {
            throw new ExceptionParameterCanNotBeEmpty('documentPath');
        }
        if (empty($documentFilename)) {
            throw new ExceptionParameterCanNotBeEmpty('documentFilename');
        }
        if (empty($this->docserverPath) || !$this->doesDocserverPathExist) {
            throw new ExceptionResourceDocserverDoesNotExist();
        }

        return $this->docserverPath . str_replace('#', DIRECTORY_SEPARATOR, $documentPath) . $documentFilename;
    }

    /**
     * Check if folder exists 
     * 
     * @param   string  $folderPath
     * 
     * @return  bool
     */
    public function folderExists(string $folderPath): bool
    {
        if (empty($folderPath)) {
            return false;
        }
        return $this->doesFolderExist;
    }

    /**
     * Check if file exists 
     * 
     * @param   string  $filePath
     * 
     * @return  bool
     */
    public function fileExists(string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }
        return $this->doesFileExist;
    }

    /**
     * Get file fingerprint
     * 
     * @param   string  $docserverTypeId
     * @param   string  $filePath
     * 
     * @return  string
     * 
     * @throws  ExceptionParameterCanNotBeEmpty
     */
    public function getFingerPrint(string $docserverTypeId, string $filePath): string
    {
        if (empty($docserverTypeId)) {
            throw new ExceptionParameterCanNotBeEmpty('docserverTypeId');
        }
        if (empty($filePath)) {
            throw new ExceptionParameterCanNotBeEmpty('filePath');
        }

        return $this->documentFingerprint;
    }

    /**
     * Retrieves file content.
     *
     * @param   string  $filePath       The path to the file.
     * @param   bool    $isEncrypted    Flag if the file is encrypted. The default value is false
     *
     * @return  string|'false'  Returns the content of the file as a string if successful, or a string with value 'false' on failure.
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

    /**
     * Convert resource to thumbnail.
     * 
     * @param   int     $resId  Resource id.
     * 
     * @return  void
     * 
     * @throws  ExceptionParameterMustBeGreaterThan|ExceptionConvertThumbnail
     */
    public function convertToThumbnail(int $resId): void
    {
        if ($resId <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('resId', 0);
        }

        if ($this->doesResourceConvertToThumbnailFailed) {
            throw new ExceptionConvertThumbnail('Convertion to thumbnail failed');
        }
    }

    /**
     * Convert resource page to thumbnail.
     * 
     * @param   int     $resId  Resource id.
     * @param   string  $type   Resource type, 'resource' or 'attachment'.
     * @param   int     $page   Resource page number.
     * 
     * @return  void
     * 
     * @throws  ExceptionParameterCanNotBeEmptyAndShould|ExceptionConvertThumbnail
     */
    public function convertOnePageToThumbnail(int $resId, string $type, int $page): void
    {
        if ($resId <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('resId', 0);
        }
        if (empty($type) || !in_array($type, ['resource', 'attachment'])) {
            throw new ExceptionParameterCanNotBeEmptyAndShould('type', "'resource', 'attachment'");
        }
        if ($page <= 0) {
            throw new ExceptionParameterMustBeGreaterThan('page', 0);
        }

        if ($this->doesResourceConvertOnePageToThumbnailFailed) {
            throw new ExceptionConvertThumbnail('Convertion one page to thumbnail failed');
        }
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
