<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\UpdateSignatoryUserInterface;

class UpdateSignatoryUserMock implements UpdateSignatoryUserInterface
{
    /**
     * @param int $resId
     * @param string $type
     * @param string $signatoryUser
     * @return void
     */
    public function updateDocumentExternalStateSignatoryUser(int $resId, string $type, string $signatoryUser): void
    {
    }
}
