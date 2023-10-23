<?php

namespace ExternalSignatoryBook\pastell\Domain;

interface ProcessVisaWorkflowInterface
{
    public function processVisaWorkflow(int $resIdMaster, bool $processSignatory): void;
}
