<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Application;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;
use ExternalSignatoryBook\pastell\Infrastructure\PastellApi;

/**
 *
 */
class SendToPastell
{

    private PastellConfigurationCheck $checkConfigPastell;
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;

    /**
     * @param PastellConfigurationCheck $checkConfigPastell
     * @param PastellApiInterface $pastellApi
     * @param PastellConfigInterface $pastellConfig
     */
    public function __construct(
        PastellConfigurationCheck $checkConfigPastell,
        PastellApiInterface       $pastellApi,
        PastellConfigInterface    $pastellConfig
    )
    {
        $this->checkConfigPastell = $checkConfigPastell;
        $this->pastellConfig = $pastellConfig;
        $this->pastellApi = $pastellApi;
    }

    /**
     * @return bool
     */
    public function sendDocumentToPastell(): bool
    {
        $config = $this->pastellConfig->getPastellConfig();

        // Check folder creation
        $idFolder = $this->pastellApi->createFolder($config);
        if (empty($idFolder)) {
            return false;
        } elseif (!empty($idFolder['errors'])) {
            return false;
        }
        $idFolder = $idFolder['idFolder'];

        // Check iParapheur sous type
        $IparapheurSousType = $this->pastellApi->getIparapheurSousType($config, $idFolder);
        if (!empty($IparapheurSousType['errors'])) {
            return false;
        } elseif (!in_array($config->getIparapheurSousType(), $IparapheurSousType)) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    public function sendDatas()
    {
        $check = $this->checkConfigPastell->checkPastellConfig();
    }
}
