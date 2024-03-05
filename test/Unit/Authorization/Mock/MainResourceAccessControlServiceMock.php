<?php

namespace MaarchCourrier\Tests\Unit\Authorization\Mock;

use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceAccessControlInterface;
use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;

class MainResourceAccessControlServiceMock implements MainResourceAccessControlInterface
{
    public bool $doesUserHasRight = true;

    public function hasRightByResId(int $resId, CurrentUserInterface|UserInterface $user): bool
    {
        return $this->doesUserHasRight;
    }
}
