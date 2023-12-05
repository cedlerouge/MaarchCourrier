<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource Data Interface
 * @author dev@maarch.org
 */

namespace Resource\Domain\Interfaces;

interface ResourceFileInterface
{
    // public const ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST = 'Docserver does not exist';
    // public const ERROR_RESOURCE_NOT_FOUND_IN_DOCSERVER = 'Document not found in docserver';
    // public const ERROR_RESOURCE_FINGERPRINT_DOES_NOT_MATCH = 'Fingerprints do not match';
    // public const ERROR_RESOURCE_FAILED_TO_GET_DOC_FROM_DOCSERVER = 'Failed to get document on docserver';
    // public const ERROR_THUMBNAIL_NOT_FOUND_IN_DOCSERVER_OR_NOT_READABLE = 'Thumbnail not found in docserver or not readable';
    // public const ERROR_RESOURCE_PAGE_NOT_FOUND = "Page not found in docserver";

    /**
     * Build file path from docserver and document paths
     * 
     * @param   string  $docserverPath
     * @param   string  $documentPath
     * @param   string  $documentFilename
     * 
     * @return  string  Return the build file path or empty if docserverPath does not exist or empty
     */
    public function buildFilePath(string $docserverPath, string $documentPath, string $documentFilename): string;

    public function folderExists(string $folderPath): bool;

    public function fileExists(string $filePath): bool;

    public function getFingerPrint(string $docserverTypeId, string $filePath): string;

    /**
     * Retrieves file content.
     *
     * @param   string  $filePath       The path to the file.
     * @param   bool    $isEncrypted    Flag if the file is encrypted. The default value is false
     *
     * @return string|'false' Returns the content of the file as a string if successful, or a string with value 'false' on failure.
     */
    public function getFileContent(string $filePath, bool $isEncrypted = false): string;

    /**
     * Retrieves file content with watermark.
     *
     * @param   int     $resId          Resource id.
     * @param   string  $fileContent    Resource file content.
     *
     * @return  string|'null'   Returns the content of the file as a string if successful, or a string with value 'null' on failure.
     */
    public function getWatermark(int $resId, string $fileContent): string;

    public function convertToThumbnail(int $resId): array;

    /**
     * Convert resource page to thumbnail.
     * 
     * @param   int     $resId  Resource id.
     * @param   string  $type   Resource type, 'resource' or 'attachment'.
     * @param   int     $page   Resource page number.
     * 
     * @return  string  If returned contains 'errors:' then the convertion failed
     */
    public function convertOnePageToThumbnail(int $resId, string $type, int $page): string;

    /**
     * Retrieves the number of pages in a pdf file
     * 
     * @param   string  $filePath   Resource path.
     * 
     * @return  int     Number of pages.
     * 
     * @throws  Exception|PdfParserException
     */
    public function getTheNumberOfPagesInThePdfFile(string $filePath): int;
}
