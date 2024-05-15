<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\Core\Domain\User\Port\UserInterface;

interface SignatureBookUserServiceInterface
{
    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     */
    public function createUser(UserInterface $user, string $accessToken): array|int;

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|bool
     */
    public function updateUser(UserInterface $user, string $accessToken): array|bool;

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|bool
     */
    public function deleteUser(UserInterface $user, string $accessToken): array|bool;

    /**
     * @param int $id
     * @param string $accessToken
     * @return bool
     */
    public function doesUserExists(int $id, string $accessToken): bool;

    /**
     * @param SignatureServiceConfig $config
     * @return SignatureBookUserServiceInterface
     */
    public function setConfig(SignatureServiceConfig $config): SignatureBookUserServiceInterface;
}
