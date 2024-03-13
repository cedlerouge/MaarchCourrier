<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Main Resource Access Control Service class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Authorization\Infrastructure;

use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceAccessControlInterface;
use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use Resource\controllers\ResController;

class MainResourceAccessControlService implements MainResourceAccessControlInterface
{
    /**
     * @param int $resId
     * @param UserInterface|CurrentUserInterface $user
     *
     * @return bool
     */
    public function hasRightByResId(int $resId, UserInterface|CurrentUserInterface $user): bool
    {
        $userId = null;
        if ($user instanceof UserInterface) {
            $userId = $user->getId();
        } else {
            $userId = $user->getCurrentUserId();
        }
        return ResController::hasRightByResId(['resId' => [$resId], 'userId' => $userId]);
    }
}
