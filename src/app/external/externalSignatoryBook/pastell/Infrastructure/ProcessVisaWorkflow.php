<?php

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\controllers\IParapheurController;
use ExternalSignatoryBook\pastell\Domain\ProcessVisaWorkflowInterface;

class ProcessVisaWorkflow implements ProcessVisaWorkflowInterface
{

    public function processVisaWorkflow(int $resIdMaster, bool $processSignatory): void
    {
        IParapheurController::processVisaWorkflow(
            ['res_id_master' => $resIdMaster,  'processSignatory' => $processSignatory]
        );
    }
}
