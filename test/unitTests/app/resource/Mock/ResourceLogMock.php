<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Resource\Domain\Exceptions\ExceptionParameterCanNotBeEmpty;
use Resource\Domain\Ports\ResourceLogInterface;

class ResourceLogMock implements ResourceLogInterface
{
    /**
     * @param   string  $logLevel
     * @param   int     $recordId
     * @param   string  $message
     *
     * @return  void
     *
     * @throws  ExceptionParameterCanNotBeEmpty
     */
    public function logThumbnailEvent(string $logLevel, int $recordId, string $message): void
    {
        if (empty($logLevel)) {
            throw new ExceptionParameterCanNotBeEmpty('logLevel');
        }
    }

}
