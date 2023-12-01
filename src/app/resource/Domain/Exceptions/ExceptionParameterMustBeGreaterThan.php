<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ExceptionParameterMustBeGreaterThan class
 * @author dev@maarch.org
 */

namespace Resource\Domain\Exceptions;

class ExceptionParameterMustBeGreaterThan extends \Exception
{
    public function __construct(string $parameterName, int $number)
    {
        parent::__construct("Parameter '$parameterName' must be greater than $number", 404);
    }
}