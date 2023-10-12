<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Domain;

class PastellConfig
{
    private string $url;
    private string $login;
    private string $password;
    private int $entity;
    private int $connector;
    private string $type;
    private string $sousType;

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @param int $entity
     * @param int $connector
     * @param string $type
     * @param string $sousType
     */
    public function __construct(
        string $url,
        string $login,
        string $password,
        int    $entity,
        int    $connector,
        string $type,
        string $sousType
    )
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->entity = $entity;
        $this->connector = $connector;
        $this->type = $type;
        $this->sousType = $sousType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): PastellConfig
    {
        $this->url = $url;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): PastellConfig
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): PastellConfig
    {
        $this->password = $password;
        return $this;
    }

    public function getEntity(): int
    {
        return $this->entity;
    }

    public function setEntity(int $entity): PastellConfig
    {
        $this->entity = $entity;
        return $this;
    }

    public function getConnector(): int
    {
        return $this->connector;
    }

    public function setConnector(int $connector): PastellConfig
    {
        $this->connector = $connector;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): PastellConfig
    {
        $this->type = $type;
        return $this;
    }

    public function getSousType(): string
    {
        return $this->sousType;
    }

    public function setSousType(string $sousType): PastellConfig
    {
        $this->sousType = $sousType;
        return $this;
    }
}
