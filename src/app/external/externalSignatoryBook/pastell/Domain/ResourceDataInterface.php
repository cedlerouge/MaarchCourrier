<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

declare(strict_types=1);

namespace ExternalSignatoryBook\pastell\Domain;

interface ResourceDataInterface
{
    public function getMainResourceData(int $resId): array;
}
