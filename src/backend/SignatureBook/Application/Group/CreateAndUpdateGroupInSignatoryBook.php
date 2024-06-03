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
use MaarchCourrier\SignatureBook\Domain\Problem\GroupCreateInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdateInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;

class CreateAndUpdateGroupInSignatoryBook
{
    /**
     * @param SignatureBookGroupServiceInterface $signatureBookGroupService
     * @param SignatureServiceConfigLoaderInterface $signatureServiceJsonConfigLoader
     */
    public function __construct(
        private readonly SignatureBookGroupServiceInterface $signatureBookGroupService,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceJsonConfigLoader
    ) {
    }

    /**
     * @param GroupInterface $group
     * @return GroupInterface|bool
     * @throws GroupCreateInMaarchParapheurFailedProblem
     * @throws GroupUpdateInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function createAndUpdateGroup(GroupInterface $group): GroupInterface|bool
    {
        $signatureBook = $this->signatureServiceJsonConfigLoader->getSignatureServiceConfig();
        if ($signatureBook === null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $this->signatureBookGroupService->setConfig($signatureBook);

        $externalId = $group->getExternalId() ?? null;

        if (!empty($externalId)) {
            $groupIsUpdated = $this->signatureBookGroupService->updateGroup($group);
            if (!empty($groupIsUpdated['errors'])) {
                throw new GroupUpdateInMaarchParapheurFailedProblem($groupIsUpdated);
            }
            return true;
        } else {
            $maarchParapheurGroupId = $this->signatureBookGroupService->createGroup($group);
            if (!empty($maarchParapheurGroupId['errors'])) {
                throw new GroupCreateInMaarchParapheurFailedProblem($maarchParapheurGroupId);
            } else {
                $external['internalParapheur'] = $maarchParapheurGroupId;
                $group->setExternalId($external);
            }
        }
        return $group;
    }
}
