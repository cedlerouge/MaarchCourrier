<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Resource\Domain\Ports\ResourceFileInterface;

class ResourceFileMock implements ResourceFileInterface
{
    public bool $doesFolderExist = true;
    public bool $doesFileExist = true;
    public bool $doesDocserverPathExist = true;
    public bool $doesResourceFileGetContentFail = false;
    public bool $doesWatermarkInResourceFileContentFail = false;
    public bool $doesResourceConvertToThumbnailFailed = false;
    public bool $returnResourceThumbnailFileContent = false;
    public bool $triggerAnExceptionWhenGetTheNumberOfPagesInThePdfFile = false;

    public string $mainResourceFileContent = 'original file content';
    public string $mainWatermarkInResourceFileContent = 'watermark in file content';
    public string $docserverPath = 'install/samples/resources/';
    public string $documentFingerprint  = 'file fingerprint';
    public string $resourceThumbnailFileContent  = 'resource thumbnail of an img';
    public string $noThumbnailFileContent  = 'thumbnail of no img';

    /**
     * @inheritDoc
     */
    public function buildFilePath(string $docserverPath, string $documentPath, string $documentFilename): string
    {
        if (empty($this->docserverPath) || !$this->doesDocserverPathExist) {
            return '';
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
     * @inheritDoc
     */
    public function getFileContent(string $filePath, bool $isEncrypted = false): ?string
    {
        if (empty($filePath)) {
            return null;
        }

        if ($this->doesResourceFileGetContentFail) {
            if ($this->returnResourceThumbnailFileContent && strpos($filePath, 'noThumbnail.png') !== false) {
                return $this->noThumbnailFileContent;
            }
            return null;
        }

        if ($this->returnResourceThumbnailFileContent && strpos($filePath, 'noThumbnail.png') !== false) {
            return $this->noThumbnailFileContent;
        }

        return $this->returnResourceThumbnailFileContent ? $this->resourceThumbnailFileContent : $this->mainResourceFileContent;
    }

    /**
     * @inheritDoc
     */
    public function getWatermark(int $resId, ?string $fileContent): ?string
    {
        if ($resId <= 0) {
            return null;
        }

        if (!$this->doesWatermarkInResourceFileContentFail) {
            return $this->mainWatermarkInResourceFileContent;
        } else {
            return null;
        }
    }

    public function convertToThumbnail(int $resId, int $version, string $fileContent, string $extension): array
    {
        if ($this->doesResourceConvertToThumbnailFailed) {
            return ['errors' => 'Conversion to thumbnail failed'];
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function convertOnePageToThumbnail(int $resId, string $type, int $page): string
    {
        return 'true';
    }

    /**
     * @inheritDoc
     */
    public function getTheNumberOfPagesInThePdfFile(string $filePath): int
    {
        /*
        if (empty($filePath)) {
            throw new Exception("Throw an exception when get pdf file");
        }

        if ($this->triggerAnExceptionWhenGetTheNumberOfPagesInThePdfFile) {
            throw new Exception("Throw an exception when parsing pdf file");
        }
        */

        return 1;
    }
}
