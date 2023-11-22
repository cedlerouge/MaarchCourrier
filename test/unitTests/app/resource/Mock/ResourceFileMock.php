<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Resource\Domain\ResourceFileInterface;

class ResourceFileMock implements ResourceFileInterface
{
    public bool $doesRessourceExist = true;
    public bool $doesRessourceFileExistInDatabase = true;
    public bool $doesFolderExist = true;
    public bool $doesFileExist = true;
    public bool $doesRessourceFileFingerprintMatch = true;
    public bool $doesRessourceFileGetContentFail = false;
    public bool $doesWatermarkInResourceFileContentFail = false;

    public string $mainResourceOriginalFileContent = 'original file content';
    public string $mainWatermarkInResourceFileContent = 'watermark in file content';
    public string $docserverPath = 'install/samples/resources/';
    public string $documentFilePath  = '2021/03/0001/';
    public string $documentFilename  = '0001_960655724.pdf';
    public string $documentFingerprint  = 'file fingerprint';
    public ?string $mainFilePath  = null;

    /**
     * Build file path from document and docserver
     * 
     * @param   string  $docserverId
     * @param   string  $documentPath
     * @param   string  $documentFilename
     * 
     * @return  string  Return the build file path
     */
    public function buildFilePath(string $docserverId, string $documentPath, string $documentFilename): string
    {
        if (empty($docserverId)) {
            return 'Error: Parameter docserverId can not be empty';
        }
        if (empty($documentPath)) {
            return 'Error: Parameter documentPath can not be empty';
        }
        if (empty($documentFilename)) {
            return 'Error: Parameter documentFilename can not be empty';
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
     */
    public function getFingerPrint(string $docserverTypeId, string $filePath): string
    {
        if (empty($docserverTypeId)) {
            return 'Error: Parameter docserverTypeId can not be empty';
        }
        if (empty($filePath)) {
            return 'Error: Parameter filePath can not be empty';
        }

        return $this->documentFingerprint;
    }

    /**
     * Retrieves file content.
     *
     * @param string $filePath The path to the file.
     *
     * @return string|'false' Returns the content of the file as a string if successful, or a string with value 'false' on failure.
     */
    public function getFileContent(string $filePath): string
    {
        if (empty($filePath)) {
            return 'false';
        }

        if (!$this->doesRessourceFileGetContentFail) {
            return $this->mainResourceOriginalFileContent;
        } else {
            return 'false';
        }
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
}
