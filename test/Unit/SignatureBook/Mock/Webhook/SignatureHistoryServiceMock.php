<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureHistoryServiceInterface;

class SignatureHistoryServiceMock implements SignatureHistoryServiceInterface
{
    public bool $addedInHistoryValidation = false;
    public bool $addedInHistoryRefus = false;
    public bool $addedInHistoryError = false;
    public function historySignatureValidation(int $resId, ?int $resIdMaster = null): void
    {
        $this->addedInHistoryValidation = true;
    }

    public function historySignatureRefus(int $resId, ?int $resIdMaster = null): void
    {
        $this->addedInHistoryRefus = true;
    }

    public function historySignatureError(int $resId, ?int $resIdMaster = null): void
    {
        $this->addedInHistoryError = true;
    }
}
