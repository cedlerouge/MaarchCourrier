<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ExceptionParameterMustBeGreaterThanFor class
 * @author dev@maarch.org
 */

namespace Resource\Domain\Exceptions;

class ExceptionParameterMustBeGreaterThanFor extends \Exception
{
    public function __construct(string $parameterName, int $number, string $for)
    {
        parent::__construct("Parameter '$parameterName' must be greater than $number for $for", 404);
    }
}