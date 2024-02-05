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

namespace SignatureBook\Infrastructure\Repository;

use SignatureBook\Domain\Ports\UserInterface;
use SignatureBook\Domain\User;
use User\models\UserModel;

class UserRepository implements UserInterface
{
    public function getUserById(int $userId): ?User
    {
        $user = UserModel::getById(['id' => $userId, 'select' => ['id']]);

        if (empty($user)) {
            return null;
        }

        return User::createFromArray($user);
    }
}
