<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;

class PastellApiMock implements PastellApiInterface
{

    public array $version = [];
    public array $entity = [];

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return string[]
     */
    public function getVersion(string $url, string $login, string $password): array
    {
        return $this->version;
    }

    public function getEntity(string $url, string $login, string $password): array
    {
        return $this->entity;
    }

    public function getConnector(string $url, string $login, string $password, int $entite)
    {
        // TODO: Implement getConnector() method.
    }
}
