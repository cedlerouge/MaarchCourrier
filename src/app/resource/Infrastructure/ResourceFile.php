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
use Resource\Domain\Interfaces\ResourceFileInterface;
use Resource\controllers\StoreController;
use Resource\controllers\WatermarkController;
use Resource\Domain\Exceptions\ExceptionConvertThumbnail;
use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmpty;
use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmptyAndShould;
use Resource\Domain\Exceptions\ExceptionParameterMustBeGreaterThan;
use Resource\Domain\Exceptions\ExceptionResourceDocserverDoesNotExist;
use setasign\Fpdi\Fpdi;
use SrcCore\controllers\PasswordController;
use SrcCore\models\CoreConfigModel;

class ResourceFile implements ResourceFileInterface
{
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

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $docserverId, 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            throw new ExceptionResourceDocserverDoesNotExist();
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
     * 
     * @throws  ExceptionParameterCanNotBeEmpty
     */
    public function getFingerPrint(string $docserverTypeId, string $filePath): string
    {
        if (empty($docserverTypeId)) {
            throw new ExceptionParameterCanNotBeEmpty('docserverId');
        }
        if (empty($filePath)) {
            throw new ExceptionParameterCanNotBeEmpty('documentPath');
        }

        $docserverType  = DocserverTypeModel::getById(['id' => $docserverTypeId, 'select' => ['fingerprint_mode']]);
        $fingerprint    = StoreController::getFingerPrint(['filePath' => $filePath, 'mode' => $docserverType['fingerprint_mode']]);
        return $fingerprint;
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

        $check = ConvertThumbnailController::convert(['type' => 'resource', 'resId' => $resId]);

        if (isset($check['errors'])) {
            throw new ExceptionConvertThumbnail($check['errors']);
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

        $check = null;

        try {
            $check = ConvertThumbnailController::convertOnePage(['type' => $type, 'resId' => $resId, 'page' => $page]);
        } catch (\Throwable $th) {
            throw new ExceptionConvertThumbnail($th->getMessage());
        }

        if (isset($check['errors'])) {
            throw new ExceptionConvertThumbnail($check['errors']);
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
