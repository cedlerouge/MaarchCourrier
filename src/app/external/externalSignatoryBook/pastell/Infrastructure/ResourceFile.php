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

namespace ExternalSignatoryBook\pastell\Infrastructure;

use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use ExternalSignatoryBook\pastell\Domain\ResourceFileInterface;

class ResourceFile implements ResourceFileInterface
{
    /**
     * Getting the file path of main file
     * @param int $resId
     * @return string
     */
    public function getMainResourceFilePath(int $resId): string
    {
        // No fingerprint for main file only attachments

        $adrMainInfo = ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => 'letterbox_coll']);
        // Checking extension of file
        if (empty($adrMainInfo['docserver_id']) || strtolower(pathinfo($adrMainInfo['filename'], PATHINFO_EXTENSION)) != 'pdf') {
            return 'Error: Document ' . $resId . ' is not converted in pdf';
        } else {
            $letterboxPath = DocserverModel::getByDocserverId(['docserverId' => $adrMainInfo['docserver_id'], 'select' => ['path_template']]);
            return $letterboxPath['path_template'] . str_replace('#', '/', $adrMainInfo['path']) . $adrMainInfo['filename'];
        }
    }
}
