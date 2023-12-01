<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource Data Interface
 * @author dev@maarch.org
 */

namespace Resource\Domain\Interfaces;

interface ResourceLogInterface
{
    /**
     * @param   string  $logLevel
     * @param   int     $recordId
     * @param   string  $message
     * 
     * @return  void
     * 
     * @throws  ExceptionParameterCanNotBeEmpty
     */
    public function logThumbnailEvent(string $logLevel, int $recordId, string $message): void;
}
