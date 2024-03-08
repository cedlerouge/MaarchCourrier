<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class ResourceAlreadySignProblem extends Problem
{
    public function __construct()
    {
        parent::__construct(
            "Resource already signed",
            400
        );
    }
}
