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
    public function getVersion(PastellConfig $config): array;

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return array
     */
    public function getEntity(PastellConfig $config): array;

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @param int $entite
     * @return array
     */
    public function getConnector(PastellConfig $config): array;

    public function getType(PastellConfig $config): array;

}
