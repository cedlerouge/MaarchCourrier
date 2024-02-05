<?php

namespace unitTests\app\signatureBook\Mock;

use SrcCore\Domain\Ports\UserInterface;
use SrcCore\Domain\User;

class UserMock implements UserInterface
{
    public bool $doesUserExist = true;

    public function getUserById(int $userId): ?User
    {
        if (!$this->doesUserExist) {
            return null;
        }
        return User::createFromArray(['id' => 1]);
    }
}
