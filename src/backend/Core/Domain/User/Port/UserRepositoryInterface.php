<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User Repository Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Core\Domain\User\Port;

use MaarchCourrier\User\Domain\User;

interface UserRepositoryInterface
{
    /**
     * @param int $userId
     * @return ?User
     */
    public function getUserById(int $userId): ?User;
}
