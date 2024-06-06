<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Create And Update Group In Signatory Book Test
 * @author dev@maarch.org
 */

namespace Unit\SignatureBook\Application\Group;

use MaarchCourrier\Group\Domain\Group;
use MaarchCourrier\SignatureBook\Application\Group\CreateAndUpdateGroupInSignatoryBook;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupCreateInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupUpdateInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\SignatureServiceJsonConfigLoaderMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Group\MaarchParapheurGroupServiceMock;
use PHPUnit\Framework\TestCase;

class CreateAndUpdateGroupInSignatoryBookTest extends TestCase
{
    private MaarchParapheurGroupServiceMock $maarchParapheurGroupServiceMock;
    private CreateAndUpdateGroupInSignatoryBook $createAndUpdateGroupInSignatoryBook;
    private SignatureServiceJsonConfigLoaderMock $signatureServiceJsonConfigLoaderMock;
    protected function setUp(): void
    {
        $this->maarchParapheurGroupServiceMock = new MaarchParapheurGroupServiceMock();
        $this->signatureServiceJsonConfigLoaderMock = new SignatureServiceJsonConfigLoaderMock();
        $this->createAndUpdateGroupInSignatoryBook = new CreateAndUpdateGroupInSignatoryBook(
            $this->maarchParapheurGroupServiceMock,
            $this->signatureServiceJsonConfigLoaderMock
        );
    }

    /**
     * @return void
     * @throws GroupCreateInMaarchParapheurFailedProblem
     * @throws GroupUpdateInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testTheGroupHasNoExternalIdSoAnAccountIsCreatedInMaarchParapheur(): void
    {
        $dataExpected['internalParapheur'] = 5;
        $expectedGroup = (new Group())
            ->setLabel('test')
            ->setExternalId($dataExpected);

        $group = (new Group())
            ->setLabel('test')
            ->setExternalId(null);

        $newGroup = $this->createAndUpdateGroupInSignatoryBook->createAndUpdateGroup($group);
        $this->assertEquals($expectedGroup->getExternalId(), $newGroup->getExternalId());
        $this->assertTrue($this->maarchParapheurGroupServiceMock->groupCreateCalled);
    }

    /**
     * @throws SignatureBookNoConfigFoundProblem
     * @throws GroupUpdateInMaarchParapheurFailedProblem
     */
    public function testTheGroupHasNoExternalIdSoAnAccountIsCreatedInMaarchParapheurButAnErrorOccurred(): void
    {
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId(null);
        $this->maarchParapheurGroupServiceMock->groupCreated =
            ['errors' => 'Error occurred during the creation of the Maarch Parapheur group.'];
        $this->expectException(GroupCreateInMaarchParapheurFailedProblem::class);
        $this->createAndUpdateGroupInSignatoryBook->createAndUpdateGroup($group);
    }

    /**
     * @return void
     * @throws GroupCreateInMaarchParapheurFailedProblem
     * @throws GroupUpdateInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testTheGroupAlreadyHasAnExternalIdThenTrueIsReturnedAndUserIsUpdate(): void
    {
        $dataExpected['internalParapheur'] = 5;
        $expectedGroup = (new Group())
            ->setLabel('test2')
            ->setExternalId($dataExpected);

        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId);

        $this->maarchParapheurGroupServiceMock->groupUpdated = true;
        $this->createAndUpdateGroupInSignatoryBook->createAndUpdateGroup($group);
        $this->assertTrue($this->maarchParapheurGroupServiceMock->groupUpdateCalled);
    }

    public function testTheGroupAlreadyHasAnExternalIdButAnErrorOccurredDuringTheCreationInMaarchParapheur(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setLabel('test')
            ->setExternalId($externalId);
        $this->maarchParapheurGroupServiceMock->groupUpdated =
            ['errors' => 'Error occurred during the update of the Maarch Parapheur group.'];
        $this->expectException(GroupUpdateInMaarchParapheurFailedProblem::class);
        $this->createAndUpdateGroupInSignatoryBook->createAndUpdateGroup($group);
    }
}
