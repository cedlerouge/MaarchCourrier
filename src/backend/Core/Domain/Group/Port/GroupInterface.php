<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Group Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Core\Domain\Group\Port;

use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeInterface;

interface GroupInterface
{
    public function getGroupId(): string;

    public function setGroupId(string $groupId): GroupInterface;

    public function getLabel(): string;

    public function setLabel(string $label): GroupInterface;

    public function getExternalId(): ?array;

    public function setExternalId(?array $externalId): GroupInterface;

    public function getPrivileges(): PrivilegeInterface;

    public function setPrivileges(PrivilegeInterface $privileges): GroupInterface;
}
