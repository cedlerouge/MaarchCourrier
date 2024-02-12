<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User Class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\User\Domain;

use MaarchCourrier\User\Domain\Ports\UserInterface;

class User implements UserInterface
{
    private int $id;

    /**
     * Create User from an array
     *
     * @param array $array
     * @return User
     */
    public static function createFromArray(array $array = []): User
    {
        $user = new User();

        $user->setId($array['id'] ?? 0);

        return $user;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
