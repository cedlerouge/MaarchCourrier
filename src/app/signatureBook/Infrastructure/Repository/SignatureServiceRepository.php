<?php

namespace SignatureBook\Infrastructure\Repository;

use SignatureBook\Domain\Port\SignatureServiceInterface;
use SignatureBook\Domain\UserSignature;
use User\models\UserSignatureModel;

class SignatureServiceRepository implements SignatureServiceInterface
{
    /**
     * @param int $userId
     * @return UserSignature[]|null
     */
    public function getSignaturesByUserId(int $userId): ?array
    {
        $signatures = UserSignatureModel::getByUserSerialId(['userSerialid' => $userId]);

        if (empty($signatures)) {
            return null;
        }

        return UserSignature::createUserSignatureArrayFromArray($signatures);
    }
}
