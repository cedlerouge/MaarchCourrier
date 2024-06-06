<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Delete Group In Signatory Book Test
 * @author dev@maarch.org
 */

namespace Unit\SignatureBook\Application\Group;

use MaarchCourrier\Group\Domain\Group;
use MaarchCourrier\SignatureBook\Application\Group\DeleteGroupInSignatoryBook;
use MaarchCourrier\SignatureBook\Domain\Problem\GroupDeletionInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\SignatureServiceJsonConfigLoaderMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Group\MaarchParapheurGroupServiceMock;
use PHPUnit\Framework\TestCase;

class DeleteGroupInSignatoryBookTest extends TestCase
{
    private MaarchParapheurGroupServiceMock $maarchParapheurGroupServiceMock;
    private DeleteGroupInSignatoryBook $deleteGroupInSignatoryBook;
    private SignatureServiceJsonConfigLoaderMock $signatureServiceJsonConfigLoaderMock;

    protected function setUp(): void
    {
        $this->maarchParapheurGroupServiceMock = new MaarchParapheurGroupServiceMock();
        $this->signatureServiceJsonConfigLoaderMock = new SignatureServiceJsonConfigLoaderMock();
        $this->deleteGroupInSignatoryBook = new DeleteGroupInSignatoryBook(
            $this->maarchParapheurGroupServiceMock,
            $this->signatureServiceJsonConfigLoaderMock
        );
    }

    /**
     * @return void
     * @throws GroupDeletionInMaarchParapheurFailedProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testTheGroupIsDeletedInMaarchParapheurThenTrueIsReturned(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setExternalId($externalId);
        $deletedGroup = $this->deleteGroupInSignatoryBook->deleteGroup($group);
        $this->assertTrue($deletedGroup);
        $this->assertTrue($this->maarchParapheurGroupServiceMock->groupIsDeletedCalled);
    }

    public function testTheDeletionOfTheGroupInMaarchParapheurFailedThenAnErrorIsReturned(): void
    {
        $externalId['internalParapheur'] = 5;
        $group = (new Group())
            ->setExternalId($externalId);
        $this->maarchParapheurGroupServiceMock->groupIsDeleted =
            ['errors' => 'Error occurred during the deletion of the Maarch Parapheur group.'];
        $this->expectException(GroupDeletionInMaarchParapheurFailedProblem::class);
        $this->deleteGroupInSignatoryBook->deleteGroup($group);
    }
}
