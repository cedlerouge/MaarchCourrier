<?php

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\ProcessVisaWorkflowInterface;

class ProcessVisaWorkflowSpy implements ProcessVisaWorkflowInterface
{

    public bool $processVisaWorkflowCalled = false;

    public function processVisaWorkflow(int $resIdMaster, bool $processSignatory): void
    {
        $this->processVisaWorkflowCalled = true;
    }
}
