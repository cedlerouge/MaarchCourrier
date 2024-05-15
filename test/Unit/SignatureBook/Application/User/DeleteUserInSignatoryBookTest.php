<?php

namespace Unit\SignatureBook\Application\User;

use MaarchCourrier\SignatureBook\Application\User\DeleteUserInSignatoryBook;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureNotAppliedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\UserDeletionInMaarchParapheurFailedProblem;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\SignatureServiceJsonConfigLoaderMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\CurrentUserInformationsMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\User\MaarchParapheurUserServiceMock;
use MaarchCourrier\User\Domain\User;
use PHPUnit\Framework\TestCase;

class DeleteUserInSignatoryBookTest extends TestCase
{
    private MaarchParapheurUserServiceMock $signatureBookUserServiceMock;
    private CurrentUserInformationsMock $currentUserInformationsMock;
    private DeleteUserInSignatoryBook $deleteUserInSignatoryBook;
    private SignatureServiceJsonConfigLoaderMock $signatureServiceJsonConfigLoaderMock;

    protected function setUp(): void
    {
        $this->signatureBookUserServiceMock = new MaarchParapheurUserServiceMock();
        $this->currentUserInformationsMock = new CurrentUserInformationsMock();
        $this->signatureServiceJsonConfigLoaderMock = new SignatureServiceJsonConfigLoaderMock();
        $this->deleteUserInSignatoryBook = new DeleteUserInSignatoryBook(
            $this->signatureBookUserServiceMock,
            $this->currentUserInformationsMock,
            $this->signatureServiceJsonConfigLoaderMock
        );
    }

    /**
     * @return void
     * @throws CurrentTokenIsNotFoundProblem
     * @throws SignatureBookNoConfigFoundProblem
     * @throws UserDeletionInMaarchParapheurFailedProblem
     */
    public function testTheUserIsDeletedInMaarchParapheurSoIGetTrueWhichIsReturned(): void
    {
        $actualData['maarchParapheur'] = 10;
        $actualData['internalParapheur'] = 12;

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId($actualData);
        $deletedUser = $this->deleteUserInSignatoryBook->deleteUser($user);
        $this->assertTrue($this->signatureBookUserServiceMock->deleteUserCalled = true);
        $this->assertTrue($deletedUser);
    }

    /**
     * @return void
     * @throws CurrentTokenIsNotFoundProblem
     * @throws SignatureBookNoConfigFoundProblem
     * @throws UserDeletionInMaarchParapheurFailedProblem
     */
    public function testAnErrorOccurredWhenDeletingTheUserInMaarchParapheur()
    {
        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId');
        $this->signatureBookUserServiceMock->deletedUser = ['errors' => 'Failed to delete the user in Maarch Parapheur.'];
        $this->expectException(UserDeletionInMaarchParapheurFailedProblem::class);
        $this->deleteUserInSignatoryBook->deleteUser($user);
    }

}
