<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace ExternalSignatoryBook\pastell\Domain;

interface ProcessVisaWorkflowInterface
{
    public function processVisaWorkflow(int $resIdMaster, bool $processSignatory): void;
}
