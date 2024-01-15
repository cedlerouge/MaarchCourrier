<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ResourceFailedToGetDocumentFromDocserverException class
 * @author dev@maarch.org
 */

namespace Resource\Domain\Exceptions;

use Exception;

class ResourceFailedToGetDocumentFromDocserverException extends Exception
{
    public function __construct()
    {
        parent::__construct("Failed to get document on docserver", 500);
    }
}
