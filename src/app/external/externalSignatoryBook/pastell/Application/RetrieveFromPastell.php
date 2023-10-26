<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve from Pastell
 * @author dev@maarch.org
 */

namespace ExternalSignatoryBook\pastell\Application;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;
use ExternalSignatoryBook\pastell\Domain\PastellStates;
use ExternalSignatoryBook\pastell\Domain\ProcessVisaWorkflowInterface;
use ExternalSignatoryBook\pastell\Infrastructure\PastellApi;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;

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

    public function retrieve(array $idsToRetrieve): array
    {
        foreach ($idsToRetrieve as $key => $value) {
            $info = $this->pastellApi->getFolderDetail($this->config, $value['external_id']);
            if (!empty($info['error'])) {
                $idsToRetrieve[$key]['status'] = 'waiting';
            } else {
                if (in_array('verif-iparapheur', $info['actionPossibles'])) {
                    $verif = $this->pastellApi->verificationIParapheur($this->config, $value['external_id']);
                    if ($verif !== true) {
                            return ['error' => 'L\'action « verif-iparapheur »  n\'est pas permise : Le dernier état du document (<termine>) ne permet pas de déclencher cette action'];
                    }
                }
                $result = $this->parseIParapheurLog->parseLogIparapheur($value['res_id'], $value['external_id']);
                $idsToRetrieve[$key] = array_merge($value, $result);
            }
        }
        return $idsToRetrieve;
    }
}
