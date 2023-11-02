<?php

declare(strict_types=1);

namespace ExternalSignatoryBook\pastell\Domain;

interface VisaCircuitDataInterface
{
    public function getNextSignatory(int $resId): array;
}
