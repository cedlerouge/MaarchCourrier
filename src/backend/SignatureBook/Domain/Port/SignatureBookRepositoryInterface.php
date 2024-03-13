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

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookResource;
use Resource\Domain\Resource;

interface SignatureBookRepositoryInterface
{
    /**
     * @param Resource $resource
     *
     * @return SignatureBookResource[]
     */
    public function getIncomingMainResourceAndAttachments(Resource $resource): array;

    /**
     * @param Resource $resource
     * @param ?CurrentUserInterface $currentUser
     *
     * @return SignatureBookResource[]
     */
    public function getAttachments(Resource $resource, CurrentUserInterface $currentUser = null): array;

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
