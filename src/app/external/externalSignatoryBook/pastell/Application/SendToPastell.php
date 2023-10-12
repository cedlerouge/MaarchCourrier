<?php

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
