<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

declare(strict_types=1);

namespace ExternalSignatoryBook\pastell\Domain;

interface PastellApiInterface
{
    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return array
     */
    public function getVersion(string $url, string $login, string $password): array;

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return array
     */
    public function getEntity(string $url, string $login, string $password): array;

    public function getConnector(string $url, string $login, string $password, int $entite);
}
