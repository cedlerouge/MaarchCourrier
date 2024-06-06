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

use MaarchCourrier\Authorization\Infrastructure\PrivilegeChecker;
use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;

class Group implements GroupInterface
{
    private string $id;
    private ?array $externalId;
    private string $label;

    /**
     * @return PrivilegeChecker
     */
    private array $privileges;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): GroupInterface
    {
        $this->label = $label;
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

    public function getGroupId(): string
    {
        return $this->id;
    }

    public function setGroupId(string $id): GroupInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPrivileges(): array
    {
        return $this->privileges;
    }

    /**
     * @param array $privileges
     * @return GroupInterface
     */
    public function setPrivileges(array $privileges): GroupInterface
    {
        $this->privileges = $privileges;
        return $this;
    }
}
