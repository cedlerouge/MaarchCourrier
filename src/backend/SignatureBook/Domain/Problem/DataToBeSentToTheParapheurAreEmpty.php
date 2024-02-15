<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class DataToBeSentToTheParapheurAreEmpty extends Problem
{
    public function __construct(array $message)
    {
        parent::__construct(
            'Some data for sending to parapheur are missing : ' . implode(', ', $message),
            400,
            [
                "value" => $message
            ]
        );
    }
}
