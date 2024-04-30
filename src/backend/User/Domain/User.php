<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User Class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\User\Domain;

use MaarchCourrier\Core\Domain\User\Port\UserInterface;

class User implements UserInterface
{
    private int $id;
    private array $externalId = [];
    private string $firstname;
    private string $lastname;
    private string $mail;
    private string $login;

    /**
     * Create User from an array
     *
     * @param array $array
     * @return User
     */
    public static function createFromArray(array $array = []): User
    {
        $user = new User();

        $user->setId($array['id'] ?? 0);

        return $user;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getExternalId(): array
    {
        return $this->externalId;
    }

    public function setExternalId(array $externalId): User
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): UserInterface
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): UserInterface
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function setMail(string $mail): UserInterface
    {
        $this->mail = $mail;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): UserInterface
    {
        $this->login = $login;
        return $this;
    }
}
