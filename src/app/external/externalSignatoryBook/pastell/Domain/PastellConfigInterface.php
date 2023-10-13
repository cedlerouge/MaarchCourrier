<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Domain;

interface PastellConfigInterface
{
    /**
     * @return PastellConfig|null
     */
    public function getPastellConfig(): ?PastellConfig;
}
 
