<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class ResourceIdEmptyProblem extends Problem
{
    public function __construct()
    {
        parent::__construct(
            "res_id parameter is missing in payload",
            400
        );
    }
}
