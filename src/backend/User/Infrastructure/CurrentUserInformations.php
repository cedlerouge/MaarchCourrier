<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief CurrentUserRepository class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\User\Infrastructure;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use SrcCore\controllers\AuthenticationController;

class CurrentUserInformations implements CurrentUserInterface
{
    public function getCurrentUserId(): int
    {
        return $GLOBALS['id'];
    }

    public function getCurrentUserLogin(): string
    {
        return $GLOBALS['login'];
    }

    /**
     * @return string
     */
    public function getCurrentUserToken(): string
    {
        return $GLOBALS['token'];
    }

    public function generateNewToken(): string
    {
        return AuthenticationController::getJWT();
    }
}
