<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Remove Privilege Group In Signatory Book Test
 * @author dev@maarch.org
 */

namespace Unit\SignatureBook\Application\Group;

use MaarchCourrier\Group\Domain\Group;
use MaarchCourrier\SignatureBook\Application\Group\RemovePrivilegeGroupInSignatoryBook;
use MaarchCourrier\SignatureBook\Domain\Privilege\SignDocumentPrivilege;
use MaarchCourrier\SignatureBook\Domain\Privilege\VisaDocumentPrivilege;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdatePrivilegeInMaarchParapheurFailedProblem;
use MaarchCourrier\Tests\Unit\Authorization\Mock\PrivilegeCheckerMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\SignatureServiceJsonConfigLoaderMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Group\MaarchParapheurGroupServiceMock;
use PHPUnit\Framework\TestCase;

class RemovePrivilegeGroupInSignatoryBookTest extends TestCase
{
    private MaarchParapheurGroupServiceMock $maarchParapheurGroupServiceMock;
    private RemovePrivilegeGroupInSignatoryBook $removePrivilegeGroupInSignatoryBook;
    private SignatureServiceJsonConfigLoaderMock $signatureServiceJsonConfigLoaderMock;
    private PrivilegeCheckerMock $privilegeCheckerMock;

    protected function setUp(): void
    {
        $this->maarchParapheurGroupServiceMock = new MaarchParapheurGroupServiceMock();
        $this->signatureServiceJsonConfigLoaderMock = new SignatureServiceJsonConfigLoaderMock();
        $this->privilegeCheckerMock = new PrivilegeCheckerMock();
        $this->removePrivilegeGroupInSignatoryBook = new RemovePrivilegeGroupInSignatoryBook(
            $this->maarchParapheurGroupServiceMock,
            $this->signatureServiceJsonConfigLoaderMock,
            $this->privilegeCheckerMock,
        );
    }
    public function testThePrivilegesAreActivatedAndWhenOneIsDeactivatedButTheSecondIsStillActivateItIsNotUpdatedInMaarchParapheur(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId)
            ->setPrivileges(['sign_document']);

        $this->maarchParapheurGroupServiceMock->isGroupPrivilegesRecovery = true;
        $this->maarchParapheurGroupServiceMock->privilegeIsChecked = false;
        $this->maarchParapheurGroupServiceMock->checked = true;
        $this->privilegeCheckerMock->hasGroupPrivilege = true;
        $this->removePrivilegeGroupInSignatoryBook->removePrivilege($group);
        $this->assertFalse($this->maarchParapheurGroupServiceMock->groupUpdatePrivilegeCalled);
        $this->assertTrue($this->privilegeCheckerMock->hasGroupPrivilegeCalled);
    }

    /**
     * @dataProvider privilegeProvider
     */
    public function testOnlyOnePrivilegeIsActivatedAndWhenIDeactivateItThePrivilegesAreUpdatedInMaarchParapheur($privilege): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId)
            ->setPrivileges([$privilege]);

        $this->maarchParapheurGroupServiceMock->isGroupPrivilegesRecovery = true;
        $this->maarchParapheurGroupServiceMock->privilegeIsChecked = false;
        $this->maarchParapheurGroupServiceMock->checked = true;
        $this->privilegeCheckerMock->hasGroupPrivilege = false;

        $this->removePrivilegeGroupInSignatoryBook->removePrivilege($group);

        $this->assertTrue($this->maarchParapheurGroupServiceMock->groupUpdatePrivilegeCalled);
    }

    public function privilegeProvider(): array
    {
        return [
            ['sign_document', VisaDocumentPrivilege::class],
            ['visa_documents', SignDocumentPrivilege::class],
        ];
    }

    public function testOnlyOnePrivilegeIsActivatedAndWhenIDeactivateItAndErrorIsReturned(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId)
            ->setPrivileges(['sign_document']);

        $this->maarchParapheurGroupServiceMock->isGroupPrivilegesRecovery = true;
        $this->maarchParapheurGroupServiceMock->privilegeIsChecked = false;
        $this->maarchParapheurGroupServiceMock->checked = true;
        $this->privilegeCheckerMock->hasGroupPrivilege = false;
        $this->maarchParapheurGroupServiceMock->privilegesGroupUpdated = [
            'errors' => 'Error occurred during the update of the group privilege in Maarch Parapheur.'
        ];
        $this->expectException(GroupUpdatePrivilegeInMaarchParapheurFailedProblem::class);
        $this->removePrivilegeGroupInSignatoryBook->removePrivilege($group);
    }
}
