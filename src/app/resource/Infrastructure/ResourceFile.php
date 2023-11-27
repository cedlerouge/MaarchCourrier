<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource file
 * @author dev@maarch.org
 */

namespace Resource\Infrastructure;

use Convert\controllers\ConvertThumbnailController;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use Resource\Domain\ResourceFileInterface;
use Resource\controllers\StoreController;
use Resource\controllers\WatermarkController;
use Resource\models\ResModel;
use setasign\Fpdi\Fpdi;
use SrcCore\controllers\PasswordController;
use SrcCore\models\CoreConfigModel;

class ResourceFile implements ResourceFileInterface
{
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

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $docserverId, 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return 'Error: ' . $this::ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST;
        }

        return $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $documentPath) . $documentFilename;
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
        return is_dir($folderPath);
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
        return file_exists($filePath);
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
            return 'Error: Parameter docserverId can not be empty';
        }
        if (empty($filePath)) {
            return 'Error: Parameter documentPath can not be empty';
        }

        $docserverType  = DocserverTypeModel::getById(['id' => $docserverTypeId, 'select' => ['fingerprint_mode']]);
        $fingerprint    = StoreController::getFingerPrint(['filePath' => $filePath, 'mode' => $docserverType['fingerprint_mode']]);
        return $fingerprint;
    }

    /**
     * Update resource fingerprint
     * 
     * @param   int     $resId
     * @param   string  $fingerprint
     * 
     * @return  void
     */
    public function updateFingerprint(int $resId, string $fingerprint): void
    {
        ResModel::update(['set' => ['fingerprint' => $fingerprint], 'where' => ['res_id = ?'], 'data' => [$resId]]);
    }

    /**
     * Retrieves file content.
     *
     * @param   string  $filePath       The path to the file.
     * @param   bool    $isEncrypted    Flag if the file is encrypted. The default value is false
     *
     * @return string|'false' Returns the content of the file as a string if successful, or a string with value 'false' on failure.
     */
    public function getFileContent(string $filePath, bool $isEncrypted = false): string
    {
        if (empty($filePath)) {
            return 'false';
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            return 'false';
        }

        if ($isEncrypted) {
            $fileContent = PasswordController::decrypt(['encryptedData' => base64_encode($fileContent)]);
        }

        return $fileContent;
    }

    /**
     * Retrieves file content with watermark.
     *
     * @param   int     $resId          Resource id.
     * @param   string  $fileContent    Resource file content.
     *
     * @return  string|'null'   Returns the content of the file as a string if successful, or a string with value 'null' on failure.
     */
    public function getWatermark(int $resId, string $fileContent): string
    {
        if ($resId <= 0) {
            return 'null';
        }
        if ($fileContent === 'false') {
            return 'null';
        }
        return WatermarkController::watermarkResource(['resId' => $resId, 'fileContent' => $fileContent]);
    }

    /**
     * Convert resource to thumbnail.
     * 
     * @param   int     $resId  Resource id.
     * @return  array{
     *      error?:     string, If an error occurs.
     *      success?:   true    If successful.
     * }
     */
    public function convertToThumbnail(int $resId): array
    {
        $check = ConvertThumbnailController::convert(['type' => 'resource', 'resId' => $resId]);

        if (isset($check['errors'])) {
            return ['error' => $check['errors']];
        } else {
            return ['success' => $check];
        }
    }

    /**
     * Convert resource page to thumbnail.
     * 
     * @param   int     $resId  Resource id.
     * @param   string  $type   Resource type, 'resource' or 'attachment'.
     * @param   int     $page   Resource page number.
     * @return  array{
     *      error?:     string, If an error occurs.
     *      success?:   true    If successful.
     * }
     */
    public function convertOnePageToThumbnail(int $resId, string $type, int $page): array
    {
        if (empty($type) || !in_array($type, ['resource', 'attachment'])) {
            return ['error' => "The 'type' is empty or not 'resource', 'attachment'"];
        }

        $check = ConvertThumbnailController::convertOnePage(['type' => $type, 'resId' => $resId, 'page' => $page]);
        if (isset($check['errors'])) {
            return ['error' => $check['errors']];
        } else {
            return ['success' => $check];
        }
    }

    /**
     * Retrieves the number of pages in a pdf file
     * 
     * @param   string  $filePath   Resource path.
     * 
     * @return  int     Number of pages.
     * @throws  Exception|PdfParserException
     */
    public function getTheNumberOfPagesInThePdfFile(string $filePath): int
    {
        $pageCount = 0;
        $libPath = CoreConfigModel::getSetaSignFormFillerLibrary();

        if (!empty($libPath)) {
            require_once($libPath);

            $document = \SetaPDF_Core_Document::loadByFilename($filePath);
            $pages = $document->getCatalog()->getPages();
            $pageCount = count($pages);
        } else {
            $libPath = CoreConfigModel::getFpdiPdfParserLibrary();
            if (file_exists($libPath)) {
                require_once($libPath);
            }
            $pdf = new Fpdi('P', 'pt');
            $pageCount = $pdf->setSourceFile($filePath);
        }

        return $pageCount;
    }
}
