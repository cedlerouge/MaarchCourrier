<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User factory
 * @author dev@maarch.org
 */

declare(strict_types=1);

namespace MaarchCourrier\User\Infrastructure;

use MaarchCourrier\Core\Domain\User\Port\UserFactoryInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\User\Application\RetrieveUser;
use MaarchCourrier\User\Domain\User;
use MaarchCourrier\User\Infrastructure\Repository\UserRepository;

class UserFactory implements UserFactoryInterface
{
    public function createUserFromArray(array $values): UserInterface
    {
        return User::createFromArray($values);
    }

    public function createRetrieveUser(): RetrieveUser
    {
        return new RetrieveUser(new UserRepository());
    }
}
