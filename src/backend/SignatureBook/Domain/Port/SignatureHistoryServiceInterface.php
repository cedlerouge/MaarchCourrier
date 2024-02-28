<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

interface SignatureHistoryServiceInterface
{
    public function historySignatureValidation(int $resId, ?int $resIdMaster = null): void;
    public function historySignatureRefus(int $resId, ?int $resIdMaster = null): void;
    public function historySignatureError(int $resId, ?int $resIdMaster = null): void;
}
