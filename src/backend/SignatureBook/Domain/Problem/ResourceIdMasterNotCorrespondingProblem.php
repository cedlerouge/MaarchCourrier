<?php

namespace MaarchCourrier\SignatureBook\Domain\Problem;

use MaarchCourrier\Core\Domain\Problem\Problem;

class ResourceIdMasterNotCorrespondingProblem extends Problem
{
    public function __construct(int $resId, int $resIdMaster)
    {
        parent::__construct(
            "res_id " . $resId . " is not an attachment of res_id_master " . $resIdMaster,
            400,
            [
                'resId'       => $resId,
                'resIdMaster' => $resIdMaster
            ]
        );
    }
}
