<?php

declare(strict_types=1);

namespace MaarchCourrier\Tests\Unit\Authorization\Mock;

use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeCheckerInterface;
use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeInterface;
use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;

class PrivilegeCheckerMock implements PrivilegeCheckerInterface
{
    public bool $hasPrivilege = false;
    public bool $hasGroupPrivilege = false;
    public bool $hasGroupPrivilegeCalled = false;

    public function hasPrivilege(UserInterface $user, PrivilegeInterface $privilege): bool
    {
        return $this->hasPrivilege;
    }

    public function hasGroupPrivilege(GroupInterface $group, PrivilegeInterface $privilege): bool
    {
        $this->hasGroupPrivilegeCalled = true;
        return $this->hasGroupPrivilege;
    }
}
