<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class StoreResourceProblem extends Problem
{
    public function __construct(string $errors)
    {
        parent::__construct(
            "Error during signed file storage : " . $errors,
            400,
            [
                'errors' => $errors
            ]
        );
    }
}
