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
        HistoryController::add([
            'tableName' => (is_null($resIdMaster)) ? 'res_letterbox' : 'res_attachments',
            'recordId'  => $resId,
            'eventType' => 'SIGN',
            'eventId'   => (is_null($resIdMaster)) ? 'resourceSign' : 'attachmentSign',
            'info'      => _DOCUMENT_SIGNED
        ]);
    }

    public function historySignatureRefus(int $resId, ?int $resIdMaster = null): void
    {
        HistoryController::add([
            'tableName' => (is_null($resIdMaster)) ? 'res_letterbox' : 'res_attachments',
            'recordId'  => $resId,
            'eventType' => 'SIGN',
            'eventId'   => (is_null($resIdMaster)) ? 'resourceSign' : 'attachmentSign',
            'info'      => 'Signature refused'
        ]);
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
