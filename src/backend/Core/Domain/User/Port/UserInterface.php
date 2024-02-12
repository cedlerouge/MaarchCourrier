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

interface UserInterface
{
    /**
     * Create a user object of an array (keys/values) from the database
     * ```
     * User $user = User::createFromArray(['id' => 1, 'firstname' => 'Robert', 'lastname' => 'RENAUD',...]);
     * ```
     *
     * @param array $array
     * @return User
     */
    public static function createFromArray(array $array = []): User;

    public function getId(): int;

    public function setId(int $id): void;
}
