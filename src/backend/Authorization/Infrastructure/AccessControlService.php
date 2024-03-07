<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Access Control Service
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Authorization\Infrastructure;

use Group\controllers\PrivilegeController;
use MaarchCourrier\Authorization\Domain\Port\AccessControlServiceInterface;
use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;

class AccessControlService implements AccessControlServiceInterface
{
    /**
     * @param string $privilegeId
     * @param CurrentUserInterface|UserInterface $user
     *
     * @return bool
     */
    public function hasPrivilege(string $privilegeId, CurrentUserInterface|UserInterface $user): bool
    {
        $userId = null;
        if ($user instanceof UserInterface) {
            $userId = $user->getId();
        } else {
            $userId = $user->getCurrentUserId();
        }

        return PrivilegeController::hasPrivilege(['privilegeId' => $privilegeId, 'userId' => $userId]);
    }
}
