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
     * @return array|int
     */
    public function updateUser(): array|int;

    /**
     * @return array|int
     */
    public function deleteUser(): array|int;

    /**
     * @param int $id
     * @return bool
     */
    public function doesUserExists(int $id): bool;
}
