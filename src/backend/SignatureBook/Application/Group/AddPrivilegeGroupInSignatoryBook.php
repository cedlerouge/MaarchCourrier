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
use MaarchCourrier\SignatureBook\Domain\Problem\GetMaarchParapheurGroupPrivilegesFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdatePrivilegeInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;

class AddPrivilegeGroupInSignatoryBook
{
    public function __construct(
        private readonly SignatureBookGroupServiceInterface $signatureBookGroupService,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceJsonConfigLoader,
    ) {
    }

    /**
     * @param GroupInterface $group
     * @return GroupInterface
     * @throws GetMaarchParapheurGroupPrivilegesFailedProblem
     * @throws GroupUpdatePrivilegeInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function addPrivilege(GroupInterface $group): GroupInterface
    {
        $signatureBook = $this->signatureServiceJsonConfigLoader->getSignatureServiceConfig();
        if ($signatureBook === null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $this->signatureBookGroupService->setConfig($signatureBook);

        $externalId = $group->getExternalId() ?? null;

        if (!empty($externalId)) {
            $groupPrivileges = $this->signatureBookGroupService->getGroupPrivileges($group);
            if (!empty($groupPrivileges['errors'])) {
                throw new GetMaarchParapheurGroupPrivilegesFailedProblem($groupPrivileges);
            }
            $result = array_filter($groupPrivileges, function ($groupPrivilege) {
                return in_array($groupPrivilege['id'], ['indexation', 'manage_documents']);
            });
            $result = array_combine(array_column($result, 'id'), $result);
            if (
                    (isset($result['indexation']) && $result['indexation']['checked'] === false) ||
                    (isset($result['manage_documents']) && $result['manage_documents']['checked'] === false)
            ) {
                   $hasPrivileges = $group->getPrivileges();
                   $privileges = [];
                if ($hasPrivileges[0] == 'sign_document' || $hasPrivileges[0] == 'visa_documents') {
                    $privileges = [
                      'indexation',
                      'manage_documents',
                       ];
                }

                foreach ($privileges as $privilege) {
                    $isPrivilegeUpdated =
                       $this->signatureBookGroupService->updatePrivilege($group, $privilege, true);
                    if (!empty($isPrivilegeUpdated['errors'])) {
                           throw new GroupUpdatePrivilegeInMaarchParapheurFailedProblem($isPrivilegeUpdated);
                    }
                }
            }
        }
        return $group;
    }
}
