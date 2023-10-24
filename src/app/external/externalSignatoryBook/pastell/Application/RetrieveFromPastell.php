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
use ExternalSignatoryBook\pastell\Domain\PastellStates;
use ExternalSignatoryBook\pastell\Domain\ProcessVisaWorkflowInterface;

class RetrieveFromPastell
{
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;
    private PastellConfigurationCheck $pastellConfigCheck;
    private ProcessVisaWorkflowInterface $processVisaWorkflow;
    private ParseIParapheurLog $parseIParapheurLog;
    private PastellConfig $config;
    private PastellStates $pastellStates;

    public function __construct(
        PastellApiInterface          $pastellApi,
        PastellConfigInterface       $pastellConfig,
        PastellConfigurationCheck    $pastellConfigCheck,
        ProcessVisaWorkflowInterface $processVisaWorkflow,
        ParseIParapheurLog           $parseIParapheurLog
    )
    {
        $this->pastellApi = $pastellApi;
        $this->pastellConfig = $pastellConfig;
        $this->pastellConfigCheck = $pastellConfigCheck;
        $this->processVisaWorkflow = $processVisaWorkflow;
        $this->parseIParapheurLog = $parseIParapheurLog;

        $this->config = $this->pastellConfig->getPastellConfig();
        $this->pastellStates = $this->pastellConfig->getPastellStates();
    }

    /**
     * @param array $idsToRetrieve
     * @return array
     */
    public function retrieve(array $idsToRetrieve): array
    {
        foreach ($idsToRetrieve as $key => $value) {
            $verif = $this->pastellApi->verificationIParapheur($this->config, $value['external_id']);
            if ($verif === true){
                $result = $this->parseIParapheurLog->parseLogIparapheur($value['res_id'], $value['external_id']);
                $idsToRetrieve[$key] = array_merge($value, $result);
            }
        }

        return $idsToRetrieve;
    }
}
