<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief SignatureBookRepositoryInterface class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\ResourceAttached;
use MaarchCourrier\SignatureBook\Domain\ResourceToSign;
use Resource\Domain\Resource;

interface SignatureBookRepositoryInterface
{
    /**
     * @param Resource $resource
     *
     * @return ResourceToSign[]
     */
    public function getIncomingMainResourceAndAttachments(Resource $resource): array;

    /**
     * @param Resource $resource
     *
     * @return ResourceAttached[]
     */
    public function getAttachments(Resource $resource): array;

    public function canUpdateResourcesInSignatureBook(Resource $resource, CurrentUserInterface $currentUser): bool;

    /**
     * @param Resource $resource
     *
     * @return bool
     */
    public function doesMainResourceHasActiveWorkflow(Resource $resource): bool;

    /**
     * @param Resource $resource
     *
     * @return ?int
     */
    public function getWorkflowUserIdByCurrentStep(Resource $resource): ?int;
}
