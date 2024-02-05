<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Custom Automatic Update Interface
 * @author dev@maarch.org
 * @ingroup core
 */

namespace SignatureBook\Domain;

class User
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
