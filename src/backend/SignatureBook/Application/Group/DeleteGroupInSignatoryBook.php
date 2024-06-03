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

namespace MaarchCourrier\SignatureBook\Application\Group;

use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookGroupServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupDeletionInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;

class DeleteGroupInSignatoryBook
{
    /**
     * @param SignatureBookGroupServiceInterface $signatureBookGroupService
     * @param SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader
     */
    public function __construct(
        private readonly SignatureBookGroupServiceInterface $signatureBookGroupService,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader,
    ) {
    }

    /**
     * @param GroupInterface $group
     * @return bool
     * @throws GroupDeletionInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function deleteGroup(GroupInterface $group): bool
    {
        $signatureBook = $this->signatureServiceConfigLoader->getSignatureServiceConfig();
        if ($signatureBook == null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $this->signatureBookGroupService->setConfig($signatureBook);
        $externalId = $group->getExternalId();
        if (!empty($externalId['internalParapheur'])) {
            $groupIsDeleted = $this->signatureBookGroupService->deleteGroup($group);
            if (!empty($groupIsDeleted['errors'])) {
                throw new GroupDeletionInMaarchParapheurFailedProblem($groupIsDeleted);
            }
        }
        return true;
    }
}
