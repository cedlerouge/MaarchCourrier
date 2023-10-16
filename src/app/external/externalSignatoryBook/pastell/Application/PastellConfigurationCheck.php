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
        $version = $this->pastellApi->getVersion($config);
        if (!empty($version['errors'])) {
            return false;
        }

        // Check Entity
        if (empty($config->getEntity())) {
            return false;
        }
        $entities = $this->pastellApi->getEntity($config);
        if (!empty($entities['errors'])) {
            return false;
        } elseif (!in_array($config->getEntity(), $entities)) {
            return false;
        }

        // Check connector
        if (empty($config->getConnector())) {
            return false;
        }
        $connectors = $this->pastellApi->getConnector($config);
        if (!empty($connectors['errors'])) {
            return false;
        } else if (!in_array($config->getConnector(), $connectors)) {
            return false;
        }

        // Check document type
        if (empty($config->getDocumentType())) {
            return false;
        }
        $flux = $this->pastellApi->getDocumentType($config);
        if (!empty($flux['errors'])) {
            return false;
        } elseif (!in_array($config->getDocumentType(), $flux)) {
            return false;
        }

        // Check iParapheur type
        if (empty($config->getIparapheurType())) {
            return false;
        }
        $iParapheurType = $this->pastellApi->getIparapheurType($config);
        if (!empty($iParapheurType['errors'])) {
            return false;
        } elseif (!in_array($config->getIparapheurType(), $iParapheurType)) {
            return false;
        }

        return true;
    }
}
