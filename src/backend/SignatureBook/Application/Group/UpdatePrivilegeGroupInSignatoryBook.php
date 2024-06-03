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
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdatePrivilegeInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;

class UpdatePrivilegeGroupInSignatoryBook
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
     * @param bool $addPrivilege
     * @return GroupInterface
     * @throws GroupUpdatePrivilegeInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function updatePrivilege(GroupInterface $group, bool $addPrivilege): GroupInterface
    {
        $signatureBook = $this->signatureServiceJsonConfigLoader->getSignatureServiceConfig();
        if ($signatureBook === null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $this->signatureBookGroupService->setConfig($signatureBook);

        $externalId = $group->getExternalId() ?? null;

        if (!empty($externalId)) {
            // Vérificatio
            $groupPrivileges = $this->signatureBookGroupService->getGroupPrivileges($group);
            $result = [];
            foreach ($groupPrivileges as $groupPrivilege) {
                if (
                    $groupPrivilege['id'] === 'indexation' ||
                    $groupPrivilege['id'] === 'manage_documents'
                ) {
                    $result[$groupPrivilege['id']] = $groupPrivilege;
                }
            }
            if ($addPrivilege === true) {
                if (
                    (isset($result['indexation']) && $result['indexation']['checked'] === false) ||
                    (isset($result['manage_documents']) && $result['manage_documents']['checked'] === false)
                ) {
                    $privilege = $group->getPrivilege();

                    if ($privilege === 'sign_document' || $privilege === 'visa_documents') {
                        $privilege = [
                            'indexation',
                            'manage_documents',
                        ];
                    }

                    foreach ($privilege as $privilegeValue) {
                        $isPrivilegeUpdated =
                            $this->signatureBookGroupService->updatePrivilege($group, $privilegeValue, true);
                        if (!empty($isPrivilegeUpdated['errors'])) {
                            throw new GroupUpdatePrivilegeInMaarchParapheurFailedProblem($isPrivilegeUpdated);
                        }
                    }
                }
            } else {
                if (
                    (isset($result['indexation']) && $result['indexation']['checked'] === true) ||
                    (isset($result['manage_documents']) && $result['manage_documents']['checked'] === true)
                ) {
                    $privilege = $group->getPrivilege();

                    if ($privilege === 'sign_document' || $privilege === 'visa_documents') {
                        $privilege = [
                            'indexation',
                            'manage_documents',
                        ];
                    }
                    $canDisable = $this->signatureBookGroupService->isPrivilegeIsChecked($group);

                    if ($canDisable) {
                        foreach ($privilege as $privilegeValue) {
                            $isPrivilegeUpdated =
                                $this->signatureBookGroupService->updatePrivilege($group, $privilegeValue, false);
                            if (!empty($isPrivilegeUpdated['errors'])) {
                                throw new GroupUpdatePrivilegeInMaarchParapheurFailedProblem($isPrivilegeUpdated);
                            }
                        }
                    }
                }
            }
        }
        return $group;
    }
}
