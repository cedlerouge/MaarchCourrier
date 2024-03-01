<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ListInstance class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class SignatureBook implements JsonSerializable
{
    private ResourcesToSign $resourcesToSign;
    private ResourcesAttached $resourcesAttached;
    private bool $canSignResources;
    private bool $canUpdateResources;
    private bool $hasWorkflow;
    private bool $isCurrentWorkflowUser;

    /**
     * @return ResourcesToSign
     */
    public function getResourcesToSign(): ResourcesToSign
    {
        return $this->resourcesToSign;
    }

    /**
     * @param ResourcesToSign $resourcesToSign
     */
    public function setResourcesToSign(ResourcesToSign $resourcesToSign): void
    {
        $this->resourcesToSign = $resourcesToSign;
    }

    /**
     * @return ResourcesAttached
     */
    public function getResourcesAttached(): ResourcesAttached
    {
        return $this->resourcesAttached;
    }

    /**
     * @param ResourcesAttached $resourcesAttached
     */
    public function setResourcesAttached(ResourcesAttached $resourcesAttached): void
    {
        $this->resourcesAttached = $resourcesAttached;
    }

    /**
     * @return bool
     */
    public function isCanSignResources(): bool
    {
        return $this->canSignResources;
    }

    /**
     * @param bool $canSignResources
     */
    public function setCanSignResources(bool $canSignResources): void
    {
        $this->canSignResources = $canSignResources;
    }

    /**
     * @return bool
     */
    public function isCanUpdateResources(): bool
    {
        return $this->canUpdateResources;
    }

    /**
     * @param bool $canUpdateResources
     */
    public function setCanUpdateResources(bool $canUpdateResources): void
    {
        $this->canUpdateResources = $canUpdateResources;
    }

    /**
     * @return bool
     */
    public function isHasWorkflow(): bool
    {
        return $this->hasWorkflow;
    }

    /**
     * @param bool $hasWorkflow
     */
    public function setHasWorkflow(bool $hasWorkflow): void
    {
        $this->hasWorkflow = $hasWorkflow;
    }

    /**
     * @return bool
     */
    public function isCurrentWorkflowUser(): bool
    {
        return $this->isCurrentWorkflowUser;
    }

    /**
     * @param bool $isCurrentWorkflowUser
     */
    public function setIsCurrentWorkflowUser(bool $isCurrentWorkflowUser): void
    {
        $this->isCurrentWorkflowUser = $isCurrentWorkflowUser;
    }

    public function jsonSerialize(): array
    {
        $array = [
            'resourcesToSign' => $this->getResourcesToSign()->jsonSerialize(),
            'resourcesAttached' => $this->getResourcesAttached()->jsonSerialize(),
            'canSignResources' => $this->isCanSignResources(),
            'canUpdateResources' => $this->isCanUpdateResources(),
            'hasWorkflow' => $this->isHasWorkflow(),
            'isCurrentWorkflowUser' => $this->isCurrentWorkflowUser(),
        ];

        return $array;
    }
}
