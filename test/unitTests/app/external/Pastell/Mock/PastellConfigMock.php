<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;

class PastellConfigMock implements PastellConfigInterface
{
    public ?PastellConfig $pastellConfig = null;

    public function __construct()
    {
        $this->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            '',
            ''
        );
    }

    /**
     * @return PastellConfig|null
     */
    public function getPastellConfig(): ?PastellConfig
    {
        return $this->pastellConfig;
    }
}
