<?php

declare(strict_types=1);

namespace MaarchCourrier\Core\Domain\Problem;

class InternalServerProblem extends Problem
{
    public function __construct(?\Throwable $throwable = null, bool $debug = false)
    {
        $context = [
            'message' => $throwable->getMessage()
        ];

        if ($debug) {
            $context += [
                'file'    => $throwable->getFile(),
                'line'    => $throwable->getLine(),
                'trace'   => $throwable->getTrace()
            ];
        }

        parent::__construct('Internal server error', 500, $context);
    }
}
