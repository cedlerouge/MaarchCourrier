<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\signatoryBook\Mock;

use ExternalSignatoryBook\Domain\Ports\HistoryRepositoryInterface;

class HistoryRepositoryMock implements HistoryRepositoryInterface
{
    public function addHistoryForResource(string $recordId, int $userId, string $info): void
    {
        // Add history for resource.
    }
}
