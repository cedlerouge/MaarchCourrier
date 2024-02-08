<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   Retrieve User Signature
 * @author  dev@maarch.org
 */

namespace SignatureBook\Application\Stamp;

use SignatureBook\Domain\Exceptions\UserDoesNotExistException;
use SignatureBook\Domain\Ports\SignatureServiceInterface;
use SignatureBook\Domain\Ports\UserInterface;
use SignatureBook\Domain\UserSignature;

class RetrieveUserStamps
{
    /**
     * @param UserInterface $user
     * @param SignatureServiceInterface $signatureService
     */
    public function __construct(
        private readonly UserInterface $user,
        private readonly SignatureServiceInterface $signatureService
    ) {
    }

    /**
     * @param int $userId
     * @return UserSignature[]
     * @throws UserDoesNotExistException
     */
    public function getUserSignatures(int $userId): array
    {
        $user = $this->user->getUserById($userId);

        if ($user === null) {
            throw new UserDoesNotExistException();
        }
        return $this->signatureService->getSignaturesByUserId($user->getId());
    }
}
