<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Signature Book User Service Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Group\Domain;

use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;

class Group implements GroupInterface
{
    private ?array $externalId;
    private string $libelle;
    private string $privilege;
    private string $id;

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): GroupInterface
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getExternalId(): ?array
    {
        return $this->externalId;
    }

    public function setExternalId(?array $externalId): GroupInterface
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getPrivilege(): string
    {
        return $this->privilege;
    }

    public function setPrivilege(string $privilege): GroupInterface
    {
        $this->privilege = $privilege;
        return $this;
    }

    public function getGroupId(): string
    {
        return $this->id;
    }

    public function setGroupId(string $id): GroupInterface
    {
        $this->id = $id;
        return $this;
    }
}
