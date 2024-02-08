<?php

namespace MaarchCourrier\Tests\app\signatureBook\Mock\Stamp;

use SignatureBook\Domain\Ports\SignatureRepositoryInterface;
use SignatureBook\Domain\UserSignature;

class SignatureRepositoryMock implements SignatureRepositoryInterface
{
    public bool $doesSignatureStampsExist = true;

    /**
     * @param int $userId
     * @return UserSignature[]
     */
    public function getSignaturesByUserId(int $userId): array
    {
        if (!$this->doesSignatureStampsExist) {
            return [];
        }

        $userSignatures = [];
        $userSignatures[] = UserSignature::createFromArray(['id' => 1, 'user_serial_id' => 1, 'signature_label' => 'Marvel Signature Stamp']);
        $userSignatures[] = UserSignature::createFromArray(['id' => 2, 'user_serial_id' => 1, 'signature_label' => 'Stan Lee Approve Stamp']);

        return $userSignatures;
    }
}
