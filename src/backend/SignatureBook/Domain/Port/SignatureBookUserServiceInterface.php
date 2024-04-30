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
     * @return array|int
     */
    public function updateUser(UserInterface $user, string $accessToken): array|int;

    /**
     * @return array|int
     */
    public function deleteUser(): array|int;

    /**
     * @param array $ids
     * @param string $accessToken
     * @return bool
     */
    public function doesUserExists(array $ids, string $accessToken): bool;

    /**
     * @param SignatureServiceConfig $config
     * @return SignatureBookUserServiceInterface
     */
    public function setConfig(SignatureServiceConfig $config): SignatureBookUserServiceInterface;
}
