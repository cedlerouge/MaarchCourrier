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
    /**
     * @var ResourceToSign[]
     */
    private array $resourcesToSign;
    /**
     * @var ResourceAttached[]
     */
    private array $resourcesAttached;
    private bool $canSignResources;
    private bool $canUpdateResources;
    private bool $hasWorkflow;
    private bool $isCurrentWorkflowUser;

    public function __construct()
    {
        $this->resourcesToSign = [];
        $this->resourcesAttached = [];
        $this->canSignResources = false;
        $this->canUpdateResources = false;
        $this->hasWorkflow = false;
        $this->isCurrentWorkflowUser = false;
    }


    /**
     * @return ResourceToSign[]
     */
    public function getResourcesToSign(): array
    {
        return $this->resourcesToSign;
    }

    /**
     * @param ResourceToSign[] $resourcesToSign
     *
     * @return SignatureBook
     */
    public function setResourcesToSign(array $resourcesToSign): self
    {
        $this->resourcesToSign = $resourcesToSign;
        return $this;
    }

    /**
     * @param ResourceToSign $resourceToSign
     *
     * @return SignatureBook
     */
    public function addResourceToSign(ResourceToSign $resourceToSign): self
    {
        $this->resourcesToSign[] = $resourceToSign;
        return $this;
    }

    /**
     * @return ResourceAttached[]
     */
    public function getResourcesAttached(): array
    {
        return $this->resourcesAttached;
    }

    /**
     * @param ResourceAttached[] $resourcesAttached
     *
     * @return SignatureBook
     */
    public function setResourcesAttached(array $resourcesAttached): self
    {
        $this->resourcesAttached = $resourcesAttached;
        return $this;
    }

    /**
     * @param ResourceAttached $resourceAttached
     *
     * @return SignatureBook
     */
    public function addResourceAttached(ResourceAttached $resourceAttached): self
    {
        $this->resourcesAttached[] = $resourceAttached;
        return $this;
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
     *
     * @return SignatureBook
     */
    public function setCanUpdateResources(bool $canUpdateResources): self
    {
        $this->canUpdateResources = $canUpdateResources;
        return $this;
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
     *
     * @return SignatureBook
     */
    public function setHasWorkflow(bool $hasWorkflow): self
    {
        $this->hasWorkflow = $hasWorkflow;
        return $this;
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
     *
     * @return SignatureBook
     */
    public function setIsCurrentWorkflowUser(bool $isCurrentWorkflowUser): self
    {
        $this->isCurrentWorkflowUser = $isCurrentWorkflowUser;
        return $this;
    }

    public function jsonSerialize(): array
    {
        $array = [];

        $resourcesToSign = $this->getResourcesToSign();
        for ($i = 0; $i < count($resourcesToSign); $i++) {
            $array['resourcesToSign'][$i] = $resourcesToSign[$i]->jsonSerialize();
        }

        $resourcesAttached = $this->getResourcesAttached();
        for ($i = 0; $i < count($resourcesAttached); $i++) {
            $array['resourcesAttached'][$i] = $resourcesAttached[$i]->jsonSerialize();
        }

        return array_merge($array, [
            'canSignResources' => $this->isCanSignResources(),
            'canUpdateResources' => $this->isCanUpdateResources(),
            'hasWorkflow' => $this->isHasWorkflow(),
            'isCurrentWorkflowUser' => $this->isCurrentWorkflowUser(),
        ]);
    }
}
