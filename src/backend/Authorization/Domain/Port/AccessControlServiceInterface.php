<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Access Control Service Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Authorization\Domain\Port;

use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;

interface AccessControlServiceInterface
{
    /**
     * Check if user has specific right
     *
     * @param   string $privilegeId Resource id
     * @param   UserInterface|CurrentUserInterface $user User
     *
     * @return  bool
     */
    public function hasPrivilege(string $privilegeId, UserInterface|CurrentUserInterface $user): bool;
}
