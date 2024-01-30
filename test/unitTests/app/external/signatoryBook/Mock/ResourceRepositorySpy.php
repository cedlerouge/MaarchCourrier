<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\signatoryBook\Mock;

use ExternalSignatoryBook\Domain\Ports\ResourceRepositoryInterface;

class ResourceRepositorySpy implements ResourceRepositoryInterface
{
    public bool $externalIdRemoved = false;

    public function removeExternalLink(int $id, string $externalId): void
    {
        // Remove external_id from res_letterbox.
        $this->externalIdRemoved = true;
    }
}
