<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   User Signature
 * @author  dev@maarch.org
 */

namespace SignatureBook\Application\Stamp;

use SignatureBook\Domain\Exceptions\UserDoesNotExistException;
use SignatureBook\Domain\Ports\SignatureServiceInterface;
use SignatureBook\Domain\Ports\UserInterface;
use SignatureBook\Domain\UserSignature;

class RetrieveUserStamps
{
    private UserInterface $user;
    private SignatureServiceInterface $signatureService;

    /**
     * @param UserInterface $user
     * @param SignatureServiceInterface $signatureService
     */
    public function __construct(
        UserInterface $user,
        SignatureServiceInterface $signatureService
    ) {
        $this->user = $user;
        $this->signatureService = $signatureService;
    }

    /**
     * @param int $userId
     * @return UserSignature[]
     * @throws UserDoesNotExistException
     */
    public function getUserSignatures(int $userId): array
    {
        $user = $this->user->getUserById($userId);

        if (empty($user)) {
            throw new UserDoesNotExistException();
        }
        return $this->signatureService->getSignaturesByUserId($user->getId());
    }
}
