<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Resource\Domain\Ports\ResourceLogInterface;

class ResourceLogMock implements ResourceLogInterface
{
    /**
     * @inheritDoc
     */
    public function logThumbnailEvent(string $logLevel, int $recordId, string $message): void
    {
    }
}
