<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve user class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\User\Application;

use MaarchCourrier\Core\Domain\Problem\ParameterMustBeGreaterThanZeroException;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserRepositoryInterface;
use MaarchCourrier\Core\Domain\User\Problem\UserDoesNotExistProblem;

class RetrieveUser
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @param int $id
     *
     * @return ?UserInterface
     * @throws ParameterMustBeGreaterThanZeroException
     * @throws UserDoesNotExistProblem
     */
    public function getUserById(int $id): ?UserInterface
    {
        if ($id <= 0) {
            throw new ParameterMustBeGreaterThanZeroException('id');
        }

        $user = $this->userRepository->getUserById($id);

        if (empty($user)) {
            throw new UserDoesNotExistProblem();
        }

        return $user;
    }
}
