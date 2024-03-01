<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief MaarchParapheurSignatureService class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Authorization\Infrastructure;

use MaarchCourrier\Core\Domain\MainResource\MainResourceAccessControlInterface;
use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use Resource\controllers\ResController;

class MainResourceAccessControlService implements MainResourceAccessControlInterface
{
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
