<?php

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
