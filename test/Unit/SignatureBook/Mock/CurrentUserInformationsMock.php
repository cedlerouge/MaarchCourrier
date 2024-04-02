<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief CurrentUserRepositoryMock class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;

class CurrentUserInformationsMock implements CurrentUserInterface
{
    private int $userId = 13;
    public string $token = 'Bearer token test';
    public bool $newUserChanged = false;

    public function getCurrentUserId(): int
    {
        return $this->userId;
    }

    public function getCurrentUserLogin(): string
    {
        return '';
    }

    public function getCurrentUserToken(): string
    {
        return $this->token;
    }

    public function generateNewToken(): string
    {
        return $this->token;
    }

    public function setCurrentUser(int $userId): void
    {
        $this->newUserChanged = true;
        $this->userId = $userId;
    }
}
