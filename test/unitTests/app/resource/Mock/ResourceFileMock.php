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
    public bool $doesRessourceDocserverExist = true;
    public bool $doesRessourceFileExistInDocserver = true;
    public bool $doesRessourceFileFingerprintMatch = true;
    public bool $doesRessourceFileGetContentFaile = false;

    public string $mainResourceOriginalFileContent = 'original file content';
    public string $docserverPath = 'install/samples/resources/';
    public string $documentFilePath  = '2021/03/0001/';
    public string $documentFilename  = '0001_960655724.pdf';
    public string $documentFingerprint  = 'file fingerprint';
    public ?string $mainFilePath  = null;

    // public function __construct()
    // {
    //     $this->mainFilePath = $this->docserverPath . $this->documentFilePath . $this->documentFilename;
    // }

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
        return $this->doesRessourceDocserverExist;
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
        return $this->doesRessourceFileExistInDocserver;
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
     * @return string|false Returns the content of the file as a string if successful, or false on failure.
     */
    public function getFileContent(string $filePath): string|false
    {
        if (empty($filePath)) {
            return false;
        }

        if (!$this->doesRessourceFileGetContentFaile) {
            return $this->mainResourceOriginalFileContent;
        } else {
            return false;
        }
    }
}
