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

    /**
     * @return bool
     */
    public function checkPastellConfig(): bool
    {
        return false;
    }

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return string[]
     */
    public function getVersion(string $url, string $login, string $password): array
    {
        return ['errors' => 'Erreur lors de la récupération de la version'];
    }

    public function getEntity($entity): array
    {
        return ['errors' => 'Erreur lors de la récupération'];
    }
}
