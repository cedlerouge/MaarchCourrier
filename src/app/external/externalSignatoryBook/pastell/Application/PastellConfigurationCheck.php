<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace ExternalSignatoryBook\pastell\Application;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;

class PastellConfigurationCheck
{
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;

    /**
     * @param PastellApiInterface $pastellApi
     * @param PastellConfigInterface $pastellConfig
     */
    public function __construct(PastellApiInterface $pastellApi, PastellConfigInterface $pastellConfig)
    {
        $this->pastellApi = $pastellApi;
        $this->pastellConfig = $pastellConfig;

    }

    /**
     * @return bool
     */
    public function checkPastellConfig(): bool
    {
        $config = $this->pastellConfig->getPastellConfig();

        if (empty($config) || empty($config['url']) || empty($config['login']) || empty($config['password'])) {
            return false;
        }
        $version = $this->pastellApi->getVersion($config['url'], $config['login'], $config['password']);
        if (!empty($version['errors'])) {
            return false;
        }

        if (empty($config['entityId'])) {
            return false;
        }

        $entities = $this->pastellApi->getEntity($config);

        if (!in_array($config['entityId'], $entities)) {
            return false;
        }

        return true;
    }

    public function checkType()
    {

    }
}
