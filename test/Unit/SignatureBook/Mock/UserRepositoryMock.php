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

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock;

use MaarchCourrier\User\Domain\User;
use MaarchCourrier\User\Domain\Port\UserRepositoryInterface;

class UserRepositoryMock implements UserRepositoryInterface
{
    public bool $doesUserExist = true;

    public function getUserById(int $userId): ?User
    {
        if ($userId <= 0 || !$this->doesUserExist) {
            return null;
        }
        return User::createFromArray(['id' => $userId]);
    }
}
