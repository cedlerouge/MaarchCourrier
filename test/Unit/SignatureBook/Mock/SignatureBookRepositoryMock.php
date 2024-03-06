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

use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\ResourceAttached;
use MaarchCourrier\SignatureBook\Domain\ResourceToSign;
use Resource\Domain\Resource;

class SignatureBookRepositoryMock implements SignatureBookRepositoryInterface
{
    public bool $hasActiveWorkflow = true;
    public bool $isUpdateResourcesInSignatureBookBasket = true;
    public bool $isUpdateResourcesInSignatureBookRedirectBasket = true;

    /**
     * @param Resource $resource
     *
     * @return ResourceToSign[]
     */
    public function getIncomingMainResourceAndAttachments(Resource $resource): array
    {
        $resourcesToSign = [];

        $resourceToSign = new ResourceToSign();
        $resourceToSign->setResId($resource->getResId())
            ->setTitle("HellDivers 2 : How’d you like the TASTE of FREEDOM?")
            ->setChrono("MAARCH/2024A/34")
            ->setSignedResId(null)
            ->setResType(0);
        $resourcesToSign[] = $resourceToSign;

        return $resourcesToSign;
    }

    /**
     * @param Resource $resource
     *
     * @return ResourceAttached[]
     */
    public function getAttachments(Resource $resource): array
    {
        $resourcesAttached = [];

        $resourceAttached = new ResourceAttached();
        $resourceAttached->setResId(101)
            ->setTitle("HellDivers 2 : How’d you like the TASTE of FREEDOM?")
            ->setSignedResId(null)
            ->setResType(1);
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
}
