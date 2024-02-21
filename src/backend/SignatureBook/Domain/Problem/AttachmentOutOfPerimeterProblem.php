<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class AttachmentOutOfPerimeterProblem extends Problem
{
    public function __construct()
    {
        parent::__construct(
            "Attachment out of perimeter",
            403
        );
    }
}
