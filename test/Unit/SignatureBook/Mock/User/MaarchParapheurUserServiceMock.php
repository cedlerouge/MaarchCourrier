<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\User;

use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfig;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurUserService;

class MaarchParapheurUserServiceMock implements SignatureBookUserServiceInterface
{
    public int $id = 12;
    public bool $createUserCalled = false;
    public bool $updateUserCalled = false;

    public bool $userExists = false;

    public SignatureServiceConfig $config;

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
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     */
    public function updateUser(UserInterface $user, string $accessToken): array|int
    {
        $this->updateUserCalled = true;
        $user->setFirstname('firstname2');
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
     * @param array $ids
     * @param string $accessToken
     * @return bool
     */
    public function doesUserExists(array $ids, string $accessToken): bool
    {
        if (!$this->userExists) {
            return false;
        }
        return true;
    }

    public function setConfig(SignatureServiceConfig $config): SignatureBookUserServiceInterface
    {
        $this->config = $config;
        return $this;
    }
}
