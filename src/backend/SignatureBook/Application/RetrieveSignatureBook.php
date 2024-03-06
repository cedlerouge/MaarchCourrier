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

use MaarchCourrier\Authorization\Domain\Problem\MainResourceOutOfPerimeterProblem;
use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceAccessControlInterface;
use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceRepositoryInterface;
use MaarchCourrier\Core\Domain\MainResource\Problem\ResourceDoesNotExistProblem;
use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBook;

class RetrieveSignatureBook
{
    public function __construct(
        public CurrentUserInterface $currentUser,
        public MainResourceAccessControlInterface $mainResourceAccessControl,
        public MainResourceRepositoryInterface $mainResourceRepository,
        public SignatureBookRepositoryInterface $signatureBookRepository
    ) {
    }

    /**
     * @throws MainResourceOutOfPerimeterProblem
     * @throws ResourceDoesNotExistProblem
     */
    public function getSignatureBook(int $userId, int $basketId, int $resId): SignatureBook
    {
        if (!$this->mainResourceAccessControl->hasRightByResId($resId, $this->currentUser)) {
            throw new MainResourceOutOfPerimeterProblem();
        }

        $resource = $this->mainResourceRepository->getMainResourceData($resId);
        if ($resource === null) {
            throw new ResourceDoesNotExistProblem();
        }

        $resourcesToSign = $this->signatureBookRepository->getIncomingMainResourceAndAttachments($resource);
        $resourcesAttached = $this->signatureBookRepository->getAttachments($resource);
        $canUpdateDocuments = $this->signatureBookRepository->canUpdateResourcesInSignatureBook($resource, $this->currentUser);
        $hasActiveWorkflow = $this->signatureBookRepository->doesMainResourceHasActiveWorkflow($resource);

        $signatureBook = new SignatureBook();
        $signatureBook->setResourcesToSign($resourcesToSign)
            ->setResourcesAttached($resourcesAttached)
            ->setCanUpdateResources($canUpdateDocuments)
            ->setHasWorkflow($hasActiveWorkflow);

        return $signatureBook;
    }
}
