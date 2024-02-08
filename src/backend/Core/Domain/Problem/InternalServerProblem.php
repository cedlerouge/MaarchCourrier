<?php

declare(strict_types=1);

namespace MaarchCourrier\Core\Domain\Problem;

class InternalServerProblem extends Problem
{
    public function __construct(?\Throwable $throwable = null)
    {
        parent::__construct(
            'Internal server error',
            500,
            [
                'message' => $throwable->getMessage(),
                'file'    => $throwable->getFile(),
                'line'    => $throwable->getLine(),
                'trace'   => $throwable->getTrace()
            ]
        );
    }
}
