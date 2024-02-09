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

namespace MaarchCourrier\SignatureBook\Application\Stamp;

use MaarchCourrier\SignatureBook\Domain\Problems\AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem;
use MaarchCourrier\SignatureBook\Domain\Ports\SignatureRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\UserSignature;
use MaarchCourrier\User\Domain\Problems\UserDoesNotExistProblem;
use MaarchCourrier\User\Domain\Ports\UserRepositoryInterface;

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
     * @throws UserDoesNotExistProblem|AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem
     */
    public function getUserSignatures(int $userId): array
    {
        $user = $this->user->getUserById($userId);
        if ($user === null) {
            throw new UserDoesNotExistProblem();
        }

        // TODO see with Nicolas later
        if ($GLOBALS['id'] !== $user->getId()) {
            throw new AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem();
        }

        return $this->signatureService->getSignaturesByUserId($user->getId());
    }
}
