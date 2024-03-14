<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   SignatureBookRepositoryMock
 * @author  dev@maarch.org
 */

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookResource;
use Resource\Domain\Resource;

class SignatureBookRepositoryMock implements SignatureBookRepositoryInterface
{
    public bool $hasActiveWorkflow = true;
    public bool $isUpdateResourcesInSignatureBookBasket = true;
    public bool $isUpdateResourcesInSignatureBookRedirectBasket = true;
    public int $workflowUserId = 19;
    public bool $isCurrentWorkflowUser = true;

    /**
     * @param Resource $resource
     * @param ?CurrentUserInterface $currentUser
     *
     * @return SignatureBookResource[]
     */
    public function getIncomingMainResourceAndAttachments(Resource $resource, CurrentUserInterface $currentUser = null): array
    {
        $resourcesToSign = [];

        $resourceToSign = new SignatureBookResource();
        $resourceToSign->setResId($resource->getResId())
            ->setTitle("HellDivers 2 : How’d you like the TASTE of FREEDOM?")
            ->setChrono("MAARCH/2024A/34")
            ->setType('main_document')
            ->setTypeLabel(_MAIN_DOCUMENT);
        $resourcesToSign[] = $resourceToSign;

        return $resourcesToSign;
    }

    /**
     * @param Resource $resource
     * @param ?CurrentUserInterface $currentUser
     *
     * @return SignatureBookResource[]
     */
    public function getAttachments(Resource $resource, CurrentUserInterface $currentUser = null): array
    {
        $resourcesAttached = [];

        $resourceAttached = new SignatureBookResource();
        $resourceAttached->setResId(101)
            ->setResIdMaster($resource->getResId())
            ->setTitle("HellDivers 2 : How’d you like the TASTE of FREEDOM?")
            ->setType('simple_attachment')
            ->setTypeLabel(_MAIN_DOCUMENT);
        $resourcesAttached[] = $resourceAttached;

        return $resourcesAttached;
    }

    public function canUpdateResourcesInSignatureBook(
        Resource $resource,
        CurrentUserInterface $currentUser
    ): bool {

        if ($this->isUpdateResourcesInSignatureBookBasket ||
            $this->isUpdateResourcesInSignatureBookRedirectBasket) {
            return true;
        }

        return false;
    }

    public function doesMainResourceHasActiveWorkflow(Resource $resource): bool
    {
        return $this->hasActiveWorkflow;
    }

    public function getWorkflowUserIdByCurrentStep(Resource $resource): ?int
    {
        if ($this->isCurrentWorkflowUser) {
            return $this->workflowUserId;
        }
        return 2;
    }
}
