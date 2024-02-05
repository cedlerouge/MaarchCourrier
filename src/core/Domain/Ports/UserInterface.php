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

namespace SrcCore\Domain\Ports;

use SrcCore\Domain\User;

interface UserInterface
{
    /**
     * @param int $userId
     * @return ?User
     */
    public function getUserById(int $userId): ?User;
}
