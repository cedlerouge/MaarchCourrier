<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class NoDocumentsInSignatureBookForThisId extends Problem
{
    public function __construct()
    {
        parent::__construct(
            "No document are found for this resId in signature book",
            400
        );
    }
}
