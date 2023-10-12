<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Application;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
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

        if (empty($config) || empty($config->getUrl()) || empty($config->getLogin()) || empty($config->getPassword())) {
            return false;
        }
        $version = $this->pastellApi->getVersion($config->getUrl(), $config->getLogin(), $config->getPassword());
        if (!empty($version['errors'])) {
            return false;
        }

        if (empty($config->getEntity())) {
            return false;
        }

        $entities = $this->pastellApi->getEntity($config->getUrl(), $config->getLogin(),$config->getPassword());

        if (!in_array($config->getEntity(), $entities)) {
            return false;
        }

        $connectors = $this->pastellApi->getConnector($config->getUrl(), $config->getLogin(), $config->getPassword(), $config->getEntity());

        if (!in_array($config->getConnector(), $connectors)) {
            return false;
        }

        return true;
    }

    public function checkType()
    {

    }
}
