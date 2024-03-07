<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief AccessControlServiceMock class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Tests\Unit\Authorization\Mock;

use MaarchCourrier\Authorization\Domain\Port\AccessControlServiceInterface;
use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;

class AccessControlServiceMock implements AccessControlServiceInterface
{
    public bool $doesUserHasRight = true;

    public function hasPrivilege(string $privilegeId, CurrentUserInterface|UserInterface $user): bool
    {
        return $this->doesUserHasRight;
    }
}
