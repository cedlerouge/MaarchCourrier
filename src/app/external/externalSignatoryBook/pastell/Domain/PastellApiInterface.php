<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

declare(strict_types=1);

namespace ExternalSignatoryBook\pastell\Domain;

use ExternalSignatoryBook\pastell\Infrastructure\PastellApi;

interface PastellApiInterface
{
    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getVersion(PastellConfig $config): array;

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getEntity(PastellConfig $config): array;

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getConnector(PastellConfig $config): array;

    public function getDocumentType(PastellConfig $config): array;

    public function getIparapheurType(PastellConfig $config): array;

    public function getIparapheurSousType(PastellConfig $config): array;

}
