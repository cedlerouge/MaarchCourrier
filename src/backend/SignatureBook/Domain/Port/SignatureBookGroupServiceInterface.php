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

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookServiceConfig;

interface SignatureBookGroupServiceInterface
{
    public function createGroup(GroupInterface $group): array|int;

    public function updateGroup(GroupInterface $group): array|bool;

    public function deleteGroup(GroupInterface $group): array|bool;

    public function setConfig(SignatureBookServiceConfig $config): SignatureBookGroupServiceInterface;

    public function getGroupPrivileges(GroupInterface $group): bool|array;

    public function updatePrivilege(GroupInterface $group, string $privilege, bool $checked): array|bool;
}
