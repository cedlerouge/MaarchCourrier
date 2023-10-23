<?php

namespace ExternalSignatoryBook\pastell\Infrastructure;


use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\RetrieveFromPastell;
use ExternalSignatoryBook\pastell\Domain\ProcessVisaWorkflowInterface;

class RetrieveFromPastellFactory
{
    public static function create(): RetrieveFromPastell
    {
        $pastellApi = new PastellApi();
        $pastellConfig = new PastellXmlConfig();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApi, $pastellConfig);
        $processVisaWorkflow = new ProcessVisaWorkflow();

        // TODO
        return new RetrieveFromPastell($pastellApi, $pastellConfig, $pastellConfigCheck, $processVisaWorkflow);
    }
}
