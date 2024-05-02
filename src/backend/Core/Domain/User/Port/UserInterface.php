<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User Repository Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Core\Domain\User\Port;

interface UserInterface
{
    /**
     * Create a user object of an array (keys/values) from the database
     * ```
     * User $user = User::createFromArray(['id' => 1, 'firstname' => 'Robert', 'lastname' => 'RENAUD',...]);
     * ```
     *
     * @param array $array
     * @return UserInterface
     */
    public static function createFromArray(array $array = []): UserInterface;

    public function getId(): int;

    public function setId(int $id): void;

    public function getExternalId(): array;

    public function setExternalId(array $externalId): UserInterface;

    public function getFirstname(): string;

    public function setFirstname(string $firstname): UserInterface;

    public function getLastname(): string;

    public function setLastname(string $lastname): UserInterface;

    public function getMail(): string;

    public function setMail(string $mail): UserInterface;

    public function getLogin(): string;

    public function setLogin(string $login): UserInterface;

    public function getPhone(): string;

    public function setPhone(string $phone): UserInterface;
}
