<?php

namespace MaarchCourrier\Tests\Functional\Core\Error\Mock;

use MaarchCourrier\Core\Domain\Problem\Problem;

class StubProblem extends Problem
{
    public function __construct(string $value)
    {
        parent::__construct(
            'My custom problem : ' . $value,
            418,
            [
                'value' => $value
            ]
        );
    }
}
