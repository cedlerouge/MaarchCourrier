<?php

declare(strict_types=1);

namespace ExternalSignatoryBook\pastell\Domain;

interface VisaCircuitDataInterface
{
    /**
     * @param int $resId
     * @return array
     */
    public function getNextSignatory(int $resId): array;
}
