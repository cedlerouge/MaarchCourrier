<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

declare(strict_types=1);

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\ResourceFileInterface;

class ResourceFileMock implements ResourceFileInterface
{
    public function getMainResourceFilePath(int $resId): string
    {
        return 'toto.pdf';
    }
}
