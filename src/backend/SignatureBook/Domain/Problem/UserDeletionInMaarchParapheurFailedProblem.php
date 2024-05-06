<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class UserDeletionInMaarchParapheurFailedProblem extends Problem
{
    /**
     * @param array $content
     */
    public function __construct(array $content)
    {
        parent::__construct(
            "user deletion in maarch parapheur failed : " . $content['errors'],
            403,
            [
                'errors' => $content['errors']
            ]
        );
    }
}
