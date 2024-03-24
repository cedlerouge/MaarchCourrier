<?php

declare(strict_types=1);

namespace MaarchCourrier\Tests\Unit\Authorization\Mock;

use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeCheckerInterface;
use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;

class PrivilegeCheckerMock implements PrivilegeCheckerInterface
{
    public bool $hasPrivilege = false;

    public function hasPrivilege(UserInterface $user, PrivilegeInterface $privilege): bool
    {
        return $this->hasPrivilege;
    }
}
