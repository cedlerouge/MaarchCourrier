<?php

declare(strict_types=1);

namespace MaarchCourrier\Authorization\Infrastructure;

use Group\controllers\PrivilegeController;
use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeCheckerInterface;
use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;

class PrivilegeChecker implements PrivilegeCheckerInterface
{
    public function hasPrivilege(UserInterface $user, PrivilegeInterface $privilege): bool
    {
        return PrivilegeController::hasPrivilege([
            'privilegeId' => $privilege->getName(),
            'userId'      => $user->getId()
        ]);
    }
}
