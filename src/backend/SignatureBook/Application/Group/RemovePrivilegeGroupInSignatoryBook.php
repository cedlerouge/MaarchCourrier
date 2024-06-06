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

use Exception;
use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeCheckerInterface;
use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookGroupServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Privilege\SignDocumentPrivilege;
use MaarchCourrier\SignatureBook\Domain\Privilege\VisaDocumentPrivilege;
use MaarchCourrier\SignatureBook\Domain\Problem\GetMaarchParapheurGroupPrivilegesFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdatePrivilegeInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;

class RemovePrivilegeGroupInSignatoryBook
{
    public function __construct(
        private readonly SignatureBookGroupServiceInterface $signatureBookGroupService,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceJsonConfigLoader,
        private readonly PrivilegeCheckerInterface $privilegeChecker,
    ) {
    }

    /**
     * @param GroupInterface $group
     * @return GroupInterface
     * @throws SignatureBookNoConfigFoundProblem
     * @throws GroupUpdatePrivilegeInMaarchParapheurFailedProblem
     * @throws GetMaarchParapheurGroupPrivilegesFailedProblem
     * @throws Exception
     */
    public function removePrivilege(GroupInterface $group): GroupInterface
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
                (isset($result['indexation']) && $result['indexation']['checked'] === true) ||
                (isset($result['manage_documents']) && $result['manage_documents']['checked'] === true)
            ) {
                $groupPrivileges = $group->getPrivileges();
                $hasPrivilege = false;
                $privileges = [];
                if ($groupPrivileges[0] == 'sign_document') {
                    $privileges = [
                        'indexation',
                        'manage_documents',
                    ];
                    $hasPrivilege =
                        $this->privilegeChecker->hasGroupPrivilege($group, new VisaDocumentPrivilege());
                } elseif ($groupPrivileges[0] == 'visa_documents') {
                    $privileges = [
                        'indexation',
                        'manage_documents',
                    ];
                    $hasPrivilege =
                        $this->privilegeChecker->hasGroupPrivilege($group, new SignDocumentPrivilege());
                }
                if (!$hasPrivilege) {
                    foreach ($privileges as $privilege) {
                        $isPrivilegeUpdated =
                            $this->signatureBookGroupService->updatePrivilege($group, $privilege, false);
                        if (!empty($isPrivilegeUpdated['errors'])) {
                            throw new GroupUpdatePrivilegeInMaarchParapheurFailedProblem($isPrivilegeUpdated);
                        }
                    }
                }
            }
        }
        return $group;
    }
}
