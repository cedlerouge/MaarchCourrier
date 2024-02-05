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
     * @throws UserDoesNotExistException
     */
    public function getUserSignatures(int $userId): ?array
    {
        if (empty($this->user->getUserById($userId))) {
            throw new UserDoesNotExistException();
        }
        return $this->signatureService->getSignaturesByUserId($userId);
    }
}
