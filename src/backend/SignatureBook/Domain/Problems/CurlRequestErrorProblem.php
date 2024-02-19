<?php

namespace MaarchCourrier\SignatureBook\Domain\Problems;

use MaarchCourrier\Core\Domain\Problem\Problem;

class CurlRequestErrorProblem extends Problem
{
    public function __construct(int $httpCode, array $content)
    {
        parent::__construct(
            "Error during external parapheur request : " . $content['errors'],
            $httpCode,
            [
                'errors' => $content['errors']
            ]
        );
    }
}
