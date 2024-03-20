<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief continueCircuitAction class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Application;

use MaarchCourrier\Authorization\Domain\Port\AccessControlServiceInterface;
use MaarchCourrier\Authorization\Domain\Problem\MainResourceOutOfPerimeterProblem;
use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceAccessControlInterface;
use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceRepositoryInterface;
use MaarchCourrier\Core\Domain\MainResource\Problem\ResourceDoesNotExistProblem;
use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBook;

class RetrieveSignatureBook
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly AccessControlServiceInterface $accessControlService,
        private readonly MainResourceAccessControlInterface $mainResourceAccessControl,
        private readonly MainResourceRepositoryInterface $mainResourceRepository,
        private readonly SignatureBookRepositoryInterface $signatureBookRepository
    ) {
    }

    /**
     * @param int $resId
     *
     * @return SignatureBook
     * @throws MainResourceOutOfPerimeterProblem
     * @throws ResourceDoesNotExistProblem
     */
    public function getSignatureBook(int $resId): SignatureBook
    {
        $resource = $this->mainResourceRepository->getMainResourceData($resId);
        if ($resource === null) {
            throw new ResourceDoesNotExistProblem();
        }

        if (!$this->mainResourceAccessControl->hasRightByResId($resource->getResId(), $this->currentUser->getCurrentUser())) {
            throw new MainResourceOutOfPerimeterProblem();
        }

        $resourcesToSign = [];
        $incomingMainResource = $this->signatureBookRepository->getIncomingMainResource($resource)[0];
        if (!empty($resource->getFilename()) && !empty($resource->getIntegrations()['inSignatureBook'])) {
            $resourcesToSign[] = $incomingMainResource;
        }
        $incomingAttachments = $this->signatureBookRepository->getIncomingAttachments($resource);
        $resourcesToSign = array_merge($resourcesToSign, $incomingAttachments);

        $resourcesAttached = $this->signatureBookRepository->getAttachments($resource);
        $canUpdateDocuments = $this->signatureBookRepository->canUpdateResourcesInSignatureBook($this->currentUser);

        foreach ($resourcesAttached as $resourceAttached) {
            $isCreator = $resourceAttached->getCreatorId() == $this->currentUser->getCurrentUserId();
            $canModify = $canUpdateDocuments || $isCreator;
            $canDelete = $canModify; // Deletion permission follows the same logic as modification permission.

            $resourceAttached->setCanModify($canModify);
            $resourceAttached->setCanDelete($canDelete);
        }

        if (!empty($resource->getFilename()) && empty($resource->getIntegrations()['inSignatureBook'])) {
            $incomingMainResource->setCanModify($canUpdateDocuments);
            $resourcesAttached[] = $incomingMainResource;
        }

        $canSignResources = $this->accessControlService->hasPrivilege('sign_document', $this->currentUser);
        $hasActiveWorkflow = $this->signatureBookRepository->doesMainResourceHasActiveWorkflow($resource);
        $workflowUserId = $this->signatureBookRepository->getWorkflowUserIdByCurrentStep($resource);

        $signatureBook = new SignatureBook();
        $signatureBook->setResourcesToSign($resourcesToSign)
            ->setResourcesAttached($resourcesAttached)
            ->setCanSignResources($canSignResources)
            ->setCanUpdateResources($canUpdateDocuments)
            ->setHasActiveWorkflow($hasActiveWorkflow)
            ->setIsCurrentWorkflowUser($workflowUserId == $this->currentUser->getCurrentUserId());

        return $signatureBook;
    }
}
