<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ExceptionParameterCanNotBeEmpty class
 * @author dev@maarch.org
 */

namespace Resource\Domain\Exceptions;

class ExceptionParameterCanNotBeEmpty extends \Exception
{
    public function __construct(string $parameterName)
    {
        parent::__construct("Parameter $parameterName can not be empty", 404);
    }
}