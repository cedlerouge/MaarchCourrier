<?php

namespace MaarchCourrier\SignatureBook\Infrastructure;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;

class MaarchParapheurUserService implements SignatureBookUserServiceInterface
{
    /**
     * @param int $id
     * @return bool
     */
    public function doesUserExists(int $id): bool
    {
        // TODO: Implement doesUserExists() method.
        return true;
    }

    /**
     * @return array|int
     */
    public function createUser(): array|int
    {
        // TODO: Implement createUser() method.
        return [];
    }

    /**
     * @return array|int
     */
    public function updateUser(): array|int
    {
        // TODO: Implement updateUser() method.
        return [];
    }

    /**
     * @return array|int
     */
    public function deleteUser(): array|int
    {
        // TODO: Implement deleteUser() method.
        return [];
    }
}
