<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   User Signature
 * @author  dev@maarch.org
 */

namespace SrcCore\Domain\Exceptions;

use Exception;

class UserDoesNotExistException extends Exception
{
    public function __construct()
    {
        parent::__construct("User does not exist", 400);
    }
}
