<?php

namespace SignatureBook\Infrastructure\Repository;

use SignatureBook\Domain\Ports\SignatureServiceInterface;
use SignatureBook\Domain\UserSignature;
use User\models\UserSignatureModel;

class SignatureServiceRepository implements SignatureServiceInterface
{
    /**
     * @param int $userId
     * @return UserSignature[]
     */
    public function getSignaturesByUserId(int $userId): array
    {
        $signatures = UserSignatureModel::getByUserSerialId(['userSerialid' => $userId]);

        if (empty($signatures)) {
            return [];
        }

        $userSignatures = [];
        foreach ($signatures as $s) {
            $userSignatures[] = UserSignature::createFromArray($s);
        }

        return $userSignatures;
    }
}
