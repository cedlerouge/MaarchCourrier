<?php

namespace Unit\SignatureBook\Application\User;

use MaarchCourrier\SignatureBook\Application\User\CreateAndUpdateUserInSignatoryBook;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\SignatureServiceJsonConfigLoaderMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\CurrentUserInformationsMock;
use MaarchCourrier\User\Domain\User;
use PHPUnit\Framework\TestCase;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\User\MaarchParapheurUserServiceMock;

class CreateAndUpdateUserInSignatoryBookTest extends TestCase
{
    private MaarchParapheurUserServiceMock $signatureBookUserServiceMock;
    private CurrentUserInformationsMock $currentUserInformationsMock;
    private CreateAndUpdateUserInSignatoryBook $createAndUpdateUserInSignatoryBook;
    private SignatureServiceJsonConfigLoaderMock $signatureServiceJsonConfigLoaderMock;
    protected function setUp(): void
    {
        $this->signatureBookUserServiceMock = new MaarchParapheurUserServiceMock();
        $this->currentUserInformationsMock = new CurrentUserInformationsMock();
        $this->signatureServiceJsonConfigLoaderMock = new SignatureServiceJsonConfigLoaderMock();
        $this->createAndUpdateUserInSignatoryBook = new CreateAndUpdateUserInSignatoryBook(
            $this->signatureBookUserServiceMock,
            $this->currentUserInformationsMock,
            $this->signatureServiceJsonConfigLoaderMock,
        );
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testTheUserHasNoExternalIdSoAnAccountIsCreatedInMaarchParapheur(): void
    {

        $dataExpected['internalParapheur'] = 12;
        $ExpectedUser = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId($dataExpected);

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId([]);


        $newUser = $this->createAndUpdateUserInSignatoryBook->createAndUpdateUser($user);

        $this->assertTrue($this->signatureBookUserServiceMock->createUserCalled);
        $this->assertEquals($ExpectedUser, $newUser);
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function testTheUserAlreadyHasAnExternalIdAndItAlreadyExistsInMaarchParapheurThenTrueIsReturnedAndUserIsUpdate(): void
    {
        $dataExpected['maarchParapheur'] = 10;
        $dataExpected['internalParapheur'] = 12;

        $ExpectedUser = (new User())
            ->setFirstname('firstname2')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId($dataExpected);

        $actualData['maarchParapheur'] = 10;
        $actualData['internalParapheur'] = 12;

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId($actualData);

        $this->signatureBookUserServiceMock->userExists = true;
        $newUser = $this->createAndUpdateUserInSignatoryBook->createAndUpdateUser($user);
        $this->assertTrue($this->signatureBookUserServiceMock->updateUserCalled);
        $this->assertEquals($ExpectedUser, $newUser);
    }

    /**
     * @throws SignatureBookNoConfigFoundProblem
     * @throws CurrentTokenIsNotFoundProblem
     */
    public function testTheUserAlreadyHasAnExternalIdButItDoesNotExistInMaarchParapheurThenAnAccountIsCreated(): void
    {
        $dataExpected['maarchParapheur'] = 10;
        $dataExpected['internalParapheur'] = 12;

        $ExpectedUser = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId($dataExpected);

        $actualData['maarchParapheur'] = 10;

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId($actualData);
        $this->signatureBookUserServiceMock->userExists = false;
        $newUser = $this->createAndUpdateUserInSignatoryBook->createAndUpdateUser($user);
        $this->assertTrue($this->signatureBookUserServiceMock->createUserCalled);
        $this->assertEquals($ExpectedUser, $newUser);
    }
}
