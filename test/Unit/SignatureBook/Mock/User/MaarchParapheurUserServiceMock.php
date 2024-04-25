<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\User;

use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;

class MaarchParapheurUserServiceMock implements SignatureBookUserServiceInterface
{
    public int $id = 12;
    public bool $createUserCalled = false;

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     */
    public function createUser(UserInterface $user, string $accessToken): array|int
    {
        $this->createUserCalled = true;
        return $this->id;
    }

    /**
     * @return array|int
     */
    public function updateUser(): array|int
    {
        return $this->id;
    }

    /**
     * @return array|int
     */
    public function deleteUser(): array|int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function doesUserExists(int $id): bool
    {
        return false;
    }
}
