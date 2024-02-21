<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class StoreResourceProblem extends Problem
{
    public function __construct(array $errors)
    {
        parent::__construct(
            "Attachment out of perimeter",
            403,
            $errors
        );
    }
}
