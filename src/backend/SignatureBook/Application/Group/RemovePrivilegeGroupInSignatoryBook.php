<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Remove privilege group in signatory book
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Application\Group;

use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeCheckerInterface;
use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeInterface;
use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookGroupServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Privilege\SignDocumentPrivilege;
use MaarchCourrier\SignatureBook\Domain\Privilege\VisaDocumentPrivilege;
use MaarchCourrier\SignatureBook\Domain\Problem\GetSignatureBookGroupPrivilegesFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdatePrivilegeInSignatureBookFailedProblem;
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
     * @param PrivilegeInterface $privilege
     * @return GroupInterface
     * @throws GetSignatureBookGroupPrivilegesFailedProblem
     * @throws GroupUpdatePrivilegeInSignatureBookFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function removePrivilege(GroupInterface $group, PrivilegeInterface $privilege): GroupInterface
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
                throw new GetSignatureBookGroupPrivilegesFailedProblem($groupPrivileges);
            }
            if (!$groupPrivileges) {
                $hasPrivilege = false;
                $privileges = [];
                if ($privilege instanceof (SignDocumentPrivilege::class)) {
                    $privileges = [
                        'indexation',
                        'manage_documents',
                    ];
                    $hasPrivilege =
                        $this->privilegeChecker->hasGroupPrivilege($group, new VisaDocumentPrivilege());
                } elseif ($privilege instanceof (VisaDocumentPrivilege::class)) {
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
                            throw new GroupUpdatePrivilegeInSignatureBookFailedProblem($isPrivilegeUpdated);
                        }
                    }
                }
            }
        }
        return $group;
    }
}
