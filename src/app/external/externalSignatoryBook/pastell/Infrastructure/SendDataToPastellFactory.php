<?php

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\SendToPastell;

class SendDataToPastellFactory
{
    /**
     * @return SendToPastell
     */
    public static function sendDataToPastell(): SendToPastell
    {
        $pastellApi = new PastellApi();
        $pastellConfig = new PastellXmlConfig();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApi, $pastellConfig);

        return new SendToPastell($pastellConfigCheck, $pastellApi, $pastellConfig);
    }
}
