<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;

class PastellApiMock implements PastellApiInterface
{

    public array $version = [];
    public array $entity = [];
    public array $connector = [];
    public array $type = [];

    /**
     * @param PastellConfig $config
     * @return string[]
     */
    public function getVersion(PastellConfig $config): array
    {
        return $this->version;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getEntity(PastellConfig $config): array
    {
        return $this->entity;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getConnector(PastellConfig $config): array
    {
        return $this->connector;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getType(PastellConfig $config): array
    {
        return $this->type;
    }
}
