<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Application;

class SendToPastell
{
    private PastellConfigurationCheck $checkConfigPastell;

    /**
     * @param PastellConfigurationCheck $checkConfigPastell
     */
    public function __construct(PastellConfigurationCheck $checkConfigPastell)
    {
        $this->checkConfigPastell = $checkConfigPastell;
    }

    /**
     * @return void
     */
    public function sendDatas()
    {
        //Pas de curl
        $check = $this->checkConfigPastell->checkPastellConfig();

    }
}
