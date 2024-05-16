<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\User;

use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookServiceConfig;

class MaarchParapheurUserServiceMock implements SignatureBookUserServiceInterface
{
    public bool $createUserCalled = false;
    public int|array $userCreated = 12;
    public bool $updateUserCalled = false;
    public bool|array $userUpdated = false;
    public array|bool $deletedUser = false;
    public bool $deleteUserCalled = false;
    public bool $userExists = false;

    public SignatureBookServiceConfig $config;

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     */
    public function createUser(UserInterface $user, string $accessToken): array|int
    {
        $this->createUserCalled = true;
        return $this->userCreated;
    }

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     */
    public function updateUser(UserInterface $user, string $accessToken): array|bool
    {
        $this->updateUserCalled = true;
        $user->setFirstname('firstname2');
        return $this->userUpdated;
    }

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|bool
     */
    public function deleteUser(UserInterface $user, string $accessToken): array|bool
    {
        $this->deleteUserCalled = true;
        return $this->deletedUser;
    }

    /**
     * @param int $id
     * @param string $accessToken
     * @return bool
     */
    public function doesUserExists(int $id, string $accessToken): bool
    {
        if (!$this->userExists) {
            return false;
        }
        return true;
    }

    /**
     * @param SignatureBookServiceConfig $config
     *
     * @return SignatureBookUserServiceInterface
     */
    public function setConfig(SignatureBookServiceConfig $config): SignatureBookUserServiceInterface
    {
        $this->config = $config;
        return $this;
    }
}
