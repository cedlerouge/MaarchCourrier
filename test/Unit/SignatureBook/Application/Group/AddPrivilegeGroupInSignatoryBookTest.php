<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Add Privilege Group In Signatory Book Test
 * @author dev@maarch.org
 */

namespace Unit\SignatureBook\Application\Group;

use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;
use MaarchCourrier\Group\Domain\Group;
use MaarchCourrier\SignatureBook\Application\Group\AddPrivilegeGroupInSignatoryBook;
use MaarchCourrier\SignatureBook\Domain\Problem\GetMaarchParapheurGroupPrivilegesFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdatePrivilegeInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\SignatureServiceJsonConfigLoaderMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Group\MaarchParapheurGroupServiceMock;
use PHPUnit\Framework\TestCase;

class AddPrivilegeGroupInSignatoryBookTest extends TestCase
{
    private MaarchParapheurGroupServiceMock $maarchParapheurGroupServiceMock;
    private AddPrivilegeGroupInSignatoryBook $addPrivilegeGroupInSignatoryBook;
    private SignatureServiceJsonConfigLoaderMock $signatureServiceJsonConfigLoaderMock;

    protected function setUp(): void
    {
        $this->maarchParapheurGroupServiceMock = new MaarchParapheurGroupServiceMock();
        $this->signatureServiceJsonConfigLoaderMock = new SignatureServiceJsonConfigLoaderMock();
        $this->addPrivilegeGroupInSignatoryBook = new AddPrivilegeGroupInSignatoryBook(
            $this->maarchParapheurGroupServiceMock,
            $this->signatureServiceJsonConfigLoaderMock
        );
    }

    /**
     * @return void
     * @throws GroupUpdatePrivilegeInMaarchParapheurFailedProblem
     * @throws GetMaarchParapheurGroupPrivilegesFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testThePrivilegeIsNotActivatedInMaarchParapheurSoWeActivateItAndTrueIsReturned(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId)
            ->setPrivileges(['sign_document']);

        $this->maarchParapheurGroupServiceMock->isGroupPrivilegesRecovery = true;
        $updatePrivilege = $this->addPrivilegeGroupInSignatoryBook->addPrivilege($group);
        $this->assertInstanceOf(GroupInterface::class, $updatePrivilege);
        $this->assertTrue($this->maarchParapheurGroupServiceMock->groupUpdatePrivilegeCalled);
    }

    /**
     * @return void
     * @throws GetMaarchParapheurGroupPrivilegesFailedProblem
     * @throws GroupUpdatePrivilegeInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testThePrivilegesIsNotActivatedInMaarchParapheurSoWeActivateItButAnErrorIsReturned(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId)
            ->setPrivileges(['sign_document']);

        $this->maarchParapheurGroupServiceMock->isGroupPrivilegesRecovery = true;
        $this->maarchParapheurGroupServiceMock->privilegesGroupUpdated =
            ['errors' => 'Error occurred during the creation of the Maarch Parapheur group.'];
        $this->expectException(GroupUpdatePrivilegeInMaarchParapheurFailedProblem::class);
        $this->addPrivilegeGroupInSignatoryBook->addPrivilege($group);
    }

    /**
     * @return void
     * @throws GetMaarchParapheurGroupPrivilegesFailedProblem
     * @throws GroupUpdatePrivilegeInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testTheRecoveryOfThePrivilegesGroupInMaarchParapheurFailedThenAnErrorIsReturned(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId);
        $this->maarchParapheurGroupServiceMock->isGroupPrivilegesRecovery = false;
        $this->expectException(GetMaarchParapheurGroupPrivilegesFailedProblem::class);
        $this->addPrivilegeGroupInSignatoryBook->addPrivilege($group);
    }
}
