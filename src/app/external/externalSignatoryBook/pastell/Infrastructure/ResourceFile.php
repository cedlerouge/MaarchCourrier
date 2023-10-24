<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace ExternalSignatoryBook\pastell\Infrastructure;

use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use ExternalSignatoryBook\pastell\Domain\ResourceFileInterface;

class ResourceFile implements ResourceFileInterface
{
    /**
     * @param int $resId
     * @return string
     */
    public function getMainResourceFilePath(int $resId): string
    {
        // TODO check fingerprint ?

        $adrMainInfo = ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => 'letterbox_coll']);
        $letterboxPath = DocserverModel::getByDocserverId(['docserverId' => $adrMainInfo['docserver_id'], 'select' => ['path_template']]);
        return $letterboxPath['path_template'] . str_replace('#', '/', $adrMainInfo['path']) . $adrMainInfo['filename'];
    }
}
