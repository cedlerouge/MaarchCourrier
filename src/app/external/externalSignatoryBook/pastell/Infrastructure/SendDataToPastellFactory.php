<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

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

        $resourceData = new ResourceDataDb();
        $resourceFile = new ResourceFile();

        $processVisaWorkflow = new ProcessVisaWorkflow();

        return new SendToPastell(
            $pastellConfigCheck,
            $pastellApi,
            $pastellConfig,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );
    }
}
