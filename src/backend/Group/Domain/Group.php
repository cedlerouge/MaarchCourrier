<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Group
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Group\Domain;

use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;

class Group implements GroupInterface
{
    private string $groupId;
    private ?array $externalId;
    private string $label;

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
        return $this->groupId;
    }

    public function setGroupId(string $groupId): GroupInterface
    {
        $this->groupId = $groupId;
        return $this;
    }
}
