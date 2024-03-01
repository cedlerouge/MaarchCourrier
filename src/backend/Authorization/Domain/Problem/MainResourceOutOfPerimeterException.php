<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ResourceOutOfPerimeterException class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Authorization\Domain\Problem;

use Exception;

class MainResourceOutOfPerimeterException extends Exception
{
    public function __construct()
    {
        parent::__construct("Document out of perimeter", 403);
    }
}
