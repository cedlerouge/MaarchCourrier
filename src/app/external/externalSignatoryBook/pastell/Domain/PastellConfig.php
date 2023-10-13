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
    private string $documentType;
    private string $iParapheurType;
    private string $iParapheurSousType;

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @param int $entity
     * @param int $connector
     * @param string $documentType
     * @param string $iParapheurType
     * @param string $iParapheurSousType
     */
    public function __construct(
        string $url,
        string $login,
        string $password,
        int    $entity,
        int    $connector,
        string $documentType,
        string $iParapheurType,
        string $iParapheurSousType
    )
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->entity = $entity;
        $this->connector = $connector;
        $this->documentType = $documentType;
        $this->iParapheurType = $iParapheurType;
        $this->iParapheurSousType = $iParapheurSousType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEntity(): int
    {
        return $this->entity;
    }

    public function getConnector(): int
    {
        return $this->connector;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    public function getIparapheurType(): string
    {
        return $this->iParapheurType;
    }

    public function getIparapheurSousType(): string
    {
        return $this->iParapheurSousType;
    }

}
