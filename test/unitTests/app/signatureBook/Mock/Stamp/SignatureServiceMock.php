<?php

namespace unitTests\app\signatureBook\Mock\Stamp;

use SignatureBook\Domain\Port\SignatureServiceInterface;
use SignatureBook\Domain\UserSignature;

class SignatureServiceMock implements SignatureServiceInterface
{
    public bool $doesSignatureStampsExist = true;


    public function getSignaturesByUserId(int $userId): ?array
    {
        if (!$this->doesSignatureStampsExist) {
            return null;
        }

        $userSignatures = [];
        $userSignatures[] = UserSignature::createUserSignatureArrayFromArray([
            ['id' => 1, 'user_serial_id' => 1]
        ]);

        return $userSignatures;
    }
}
