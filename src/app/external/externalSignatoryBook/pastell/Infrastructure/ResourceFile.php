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

use Attachment\models\AttachmentTypeModel;
use Convert\controllers\ConvertPdfController;
use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use ExternalSignatoryBook\pastell\Domain\ResourceFileInterface;
use Resource\controllers\StoreController;

class ResourceFile implements ResourceFileInterface
{
    /**
     * Getting the file path of main file
     * @param int $resId
     * @return string
     */
    public function getMainResourceFilePath(int $resId): string
    {
        $adrMainInfo = ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => 'letterbox_coll']);
        // Checking extension of file
        if (empty($adrMainInfo['docserver_id']) || strtolower(pathinfo($adrMainInfo['filename'], PATHINFO_EXTENSION)) != 'pdf') {
            return 'Error: Document ' . $resId . ' is not converted in pdf';
        } else {
            $letterboxPath = DocserverModel::getByDocserverId(['docserverId' => $adrMainInfo['docserver_id'], 'select' => ['path_template']]);
            return $letterboxPath['path_template'] . str_replace('#', '/', $adrMainInfo['path']) . $adrMainInfo['filename'];
        }
    }

    public function getAttachmentsFilePath(array $attachmentsResource): array
    {
        // TODO compteur pour le nombre de PJ, à intégrer dans le curl ???
        $attachmentTypes = AttachmentTypeModel::get(['select' => ['type_id', 'signable']]);
        $attachmentTypes = array_column($attachmentTypes, 'signable', 'type_id');
        foreach ($attachmentsResource as $key => $value) {
            if (!$attachmentTypes[$value['attachment_type']]) {
                $adrInfo = AdrModel::getConvertedDocumentById(['resId' => $value['res_id'], 'collId' => 'attachments_coll', 'type' => 'PDF']);
                $annexeAttachmentPath = DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id'], 'select' => ['path_template']]);
                $value['filePath'] = $annexeAttachmentPath['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $adrInfo['path']) . $adrInfo['filename'];

                // Checking fingerprint
                $docserverType = DocserverTypeModel::getById(['id' => $annexeAttachmentPath['docserver_type_id'], 'select' => ['fingerprint_mode']]);
                $fingerprint = StoreController::getFingerPrint(['filePath' => $value['filePath'], 'mode' => $docserverType['fingerprint_mode']]);
                if ($value['fingerprint'] != $fingerprint) {
                    return ['error' => 'Fingerprints do not match'];
                }
            }
        }

        // TODO attachment to freeze??
    }
}
