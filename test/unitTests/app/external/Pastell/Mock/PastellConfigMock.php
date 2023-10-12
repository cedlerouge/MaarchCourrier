<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;

class PastellConfigMock implements PastellConfigInterface
{

    public array $pastellConfig = [
        'url' => 'testUrl',
        'login' => 'toto',
        'password' => 'toto123'
    ];

    /**
     * @return array|string[]
     */
    public function getPastellConfig(): array
    {
        return $this->pastellConfig;
    }
}
