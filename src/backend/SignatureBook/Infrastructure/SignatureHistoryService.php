<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief SignatureHistoryService class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure;

use History\controllers\HistoryController;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureHistoryServiceInterface;

class SignatureHistoryService implements SignatureHistoryServiceInterface
{
    public function historySignatureValidation(int $resId, ?int $resIdMaster = null): void
    {
        if (!is_null($resIdMaster)) {
            HistoryController::add([
                'tableName' => 'res_letterbox',
                'recordId'  => $resIdMaster,
                'eventType' => 'SIGN',
                'eventId'   => 'resourceSign',
                'info'      => _ATTACHMENT_SIGNED
            ]);

            HistoryController::add([
                'tableName' => 'res_attachments',
                'recordId'  => $resId,
                'eventType' => 'SIGN',
                'eventId'   => 'attachmentSign',
                'info'      => _ATTACHMENT_SIGNED
            ]);

        } else {
            HistoryController::add([
                'tableName' => 'res_letterbox',
                'recordId'  => $resId,
                'eventType' => 'SIGN',
                'eventId'   => 'resourceSign',
                'info'      => _MAIN_RESOURCE_SIGNED
            ]);
        }
    }

    public function historySignatureRefus(int $resId, ?int $resIdMaster = null): void
    {
        if (!is_null($resIdMaster)) {
            HistoryController::add([
                'tableName' => 'res_letterbox',
                'recordId'  => $resIdMaster,
                'eventType' => 'SIGN',
                'eventId'   => 'resourceSign',
                'info'      => _ATTACHMENT_SIGN_REFUSED
            ]);

            HistoryController::add([
                'tableName' => 'res_attachments',
                'recordId'  => $resId,
                'eventType' => 'SIGN',
                'eventId'   => 'attachmentSign',
                'info'      => _ATTACHMENT_SIGN_REFUSED
            ]);

        } else {
            HistoryController::add([
                'tableName' => 'res_letterbox',
                'recordId'  => $resId,
                'eventType' => 'SIGN',
                'eventId'   => 'resourceSign',
                'info'      => _MAIN_RESOURCE_SIGN_REFUSED
            ]);
        }
    }

    public function historySignatureError(int $resId, ?int $resIdMaster = null): void
    {
        HistoryController::add([
            'tableName' => (is_null($resIdMaster)) ? 'res_letterbox' : 'res_attachments',
            'recordId'  => $resId,
            'eventType' => 'SIGN',
            'eventId'   => (is_null($resIdMaster)) ? 'resourceSign' : 'attachmentSign',
            'info'      => 'Error during signature process'
        ]);
    }
}
