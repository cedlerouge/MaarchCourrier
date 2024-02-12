<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief MaarchParapheurSignatureNotAppliedException class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class MaarchParapheurSignatureNotAppliedException extends Problem
{
    public function __construct(string $message)
    {
        parent::__construct(
            "Exception during the application of the signature : " . $message,
            400,
            [
                'value' => $message
            ]
        );
    }
}
