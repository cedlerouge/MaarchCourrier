<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve User Signature
 * @author dev@maarch.org
 */

namespace SignatureBook\Application\Stamp;

use SignatureBook\Domain\Exceptions\AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesException;
use SignatureBook\Domain\Exceptions\UserDoesNotExistException;
use SignatureBook\Domain\Ports\SignatureRepositoryInterface;
use SignatureBook\Domain\Ports\UserRepositoryInterface;
use SignatureBook\Domain\UserSignature;

class RetrieveUserStamps
{
    /**
     * @param UserRepositoryInterface $user
     * @param SignatureRepositoryInterface $signatureService
     */
    public function __construct(
        private readonly UserRepositoryInterface $user,
        private readonly SignatureRepositoryInterface $signatureService
    ) {
    }

    /**
     * @param int $userId
     * @return UserSignature[]
     * @throws UserDoesNotExistException|AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesException
     */
    public function getUserSignatures(int $userId): array
    {
        $user = $this->user->getUserById($userId);
        if ($user === null) {
            throw new UserDoesNotExistException();
        }

        // TODO see with Nicolas later
        if ($GLOBALS['id'] !== $user->getId()) {
            throw new AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesException();
        }

        return $this->signatureService->getSignaturesByUserId($user->getId());
    }
}
