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
use Docserver\models\DocserverTypeModel;
use Exception;
use Resource\Domain\Ports\ResourceFileInterface;
use Resource\controllers\StoreController;
use Resource\controllers\WatermarkController;
use setasign\Fpdi\Fpdi;
use SrcCore\controllers\PasswordController;
use SrcCore\models\CoreConfigModel;

class ResourceFile implements ResourceFileInterface
{
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
        if (empty($docserverPath) || !file_exists($docserverPath)) {
            return '';
        }

        return $docserverPath . str_replace('#', DIRECTORY_SEPARATOR, $documentPath) . $documentFilename;
    }

    public function folderExists(string $folderPath): bool
    {
        if (empty($folderPath)) {
            return false;
        }
        return is_dir($folderPath);
    }

    public function fileExists(string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }
        return file_exists($filePath);
    }

    public function getFingerPrint(string $docserverTypeId, string $filePath): string
    {
        if (empty($docserverTypeId)) {
            return '';
        }
        if (empty($filePath)) {
            return '';
        }

        $docserverType  = DocserverTypeModel::getById(['id' => $docserverTypeId, 'select' => ['fingerprint_mode']]);
        $fingerprint    = StoreController::getFingerPrint(['filePath' => $filePath, 'mode' => $docserverType['fingerprint_mode']]);
        return $fingerprint;
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

    public function convertToThumbnail(int $resId): array
    {
        return ConvertThumbnailController::convert(['type' => 'resource', 'resId' => $resId]);
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
        $check = 'true';

        try {
            $check = ConvertThumbnailController::convertOnePage(['type' => $type, 'resId' => $resId, 'page' => $page]);
        } catch (\Throwable $th) {
            return "errors: " . $th->getMessage();
        }

        if (isset($check['errors'])) {
            return "errors: " . $check['errors'];
        }

        return $check;
    }

    /**
     * Retrieves the number of pages in a pdf file
     *
     * @param   string  $filePath   Resource path.
     *
     * @return  int     Number of pages.
     */
    public function getTheNumberOfPagesInThePdfFile(string $filePath): int
    {
        $pageCount = 0;

        try {
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
        } catch (Exception $e) {
            $pageCount = 0;
        }
        return $pageCount;
    }
}
