<?php

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

    private PastellConfig $config;
    private PastellStates $pastellStates;

    public function __construct(
        PastellApiInterface $pastellApi,
        PastellConfigInterface $pastellConfig,
        PastellConfigurationCheck $pastellConfigCheck,
        ProcessVisaWorkflowInterface $processVisaWorkflow
    )
    {
        $this->pastellApi = $pastellApi;
        $this->pastellConfig = $pastellConfig;
        $this->pastellConfigCheck = $pastellConfigCheck;
        $this->processVisaWorkflow = $processVisaWorkflow;

        $this->config = $this->pastellConfig->getPastellConfig();
        $this->pastellStates = $this->pastellConfig->getPastellStates();
    }

    public function retrieve(array $resIds, $idFolder): array
    {
        foreach ($resIds as $resId) {
            $documentDetail = $this->pastellApi->getDocumentDetail($this->config,$idFolder);
            if ($documentDetail['data']['has_signature'] == "1") {
                return $this->parseLogIparapheur($resId, $idFolder);
            }
        }
        return [];
    }


    public function parseLogIparapheur(int $res, string $idFolder): array
    {
        $return = [];
        $iParapheurHistory = $this->pastellApi->getXmlDetail($this->config, $idFolder);

        if ($iParapheurHistory->MessageRetour->codeRetour == $this->pastellStates->getErrorCode()) {
            return ['error' => '[ Log KO in iParapheur : ' . $iParapheurHistory->MessageRetour->severite . ']' . $iParapheurHistory->MessageRetour->message];
        }

        foreach ($iParapheurHistory->LogDossier as $historyLog) {
            $status = $historyLog->status;
            if ($status == $this->pastellStates->getSignState()) {
                $return =  $this->handleValidate($res, $idFolder, true);
            } elseif ($status == $this->pastellStates->getVisaState())  {
                $return =  $this->handleValidate($res, $idFolder, false);
            } elseif ($status == $this->pastellStates->getRefusedSign() || $status == $this->pastellStates->getRefusedVisa()) {
                $return = $this->handleRefused($res, $historyLog);
            }
        }
        return $return;
    }


    public function handleValidate(int $res, string $idFolder, bool $signed): array
    {

        $file = $this->pastellApi->downloadFile($this->config, $idFolder);
        if (!empty($file['error'])) {
            return ['error' => $file['error']];
        }

        if ($signed) {
            $this->processVisaWorkflow->processVisaWorkflow($res, true);
        }

        return [
            'status'      => 'validated',
            'format'      => 'pdf',
            'encodedFile' => $file['encodedFile']
        ];
    }

    public function handleRefused(int $res, object $info): array
    {
        $noteContent = $info->nom .': ' . $info->annotation;

        return [
            'status' => 'refused',
            'content' => $noteContent
        ];
    }

}
