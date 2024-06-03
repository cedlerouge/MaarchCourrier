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
    public function getLibelle(): string;

    /**
     * @param string $libelle
     * @return GroupInterface
     */
    public function setLibelle(string $libelle): GroupInterface;

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
     * @return string
     */
    public function getPrivilege(): string;

    /**
     * @param string $privilege
     * @return GroupInterface
     */
    public function setPrivilege(string $privilege): GroupInterface;
}
