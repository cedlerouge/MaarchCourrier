<?php

namespace MaarchCourrier\Tests\Functional\Core\Error\Mock;

use MaarchCourrier\Core\Domain\Port\EnvironnementInterface;

class EnvironnementMock implements EnvironnementInterface
{
    public bool $debug = false;

    public function isDebug(): bool
    {
        return $this->debug;
    }
}
