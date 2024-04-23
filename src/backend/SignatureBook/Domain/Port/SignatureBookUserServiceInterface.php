<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

interface SignatureBookUserServiceInterface
{
    public function createUser(): array|int;
    public function updateUser(): array|int;
    public function deleteUser(): array|int;
    public function doesUserExists(int $id): bool;
}
