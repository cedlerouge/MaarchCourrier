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

namespace MaarchCourrier\Core\Domain\Group\Port;

use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeInterface;

interface GroupInterface
{
    /**
     * @return string
     */
    public function getGroupId(): string;

    /**
     * @param string $id
     * @return GroupInterface
     */
    public function setGroupId(string $id): GroupInterface;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     * @return GroupInterface
     */
    public function setLabel(string $label): GroupInterface;

    /**
     * @return array|null
     */
    public function getExternalId(): ?array;

    /**
     * @param array|null $externalId
     * @return GroupInterface
     */
    public function setExternalId(?array $externalId): GroupInterface;

    /**
     * @return PrivilegeInterface[]
     */
    public function getPrivileges(): array;

    /**
     * @param array $privileges
     * @return GroupInterface
     */
    public function setPrivileges(array $privileges): GroupInterface;
}
