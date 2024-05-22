<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief No Resources Found To Sign Problem
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class NoResourcesFoundToSignProblem extends Problem
{
    public function __construct()
    {
        parent::__construct(
            "No resources found to sign",
            404
        );
    }
}
