<?php

namespace Unit\SignatureBook\Application\User;

use MaarchCourrier\SignatureBook\Application\User\CreateAndUpdateUserInSignatoryBook;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\CurrentUserInformationsMock;
use MaarchCourrier\User\Domain\User;
use PHPUnit\Framework\TestCase;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\User\MaarchParapheurUserServiceMock;

class CreateAndUpdateUserInSignatoryBookTest extends TestCase
{
    private MaarchParapheurUserServiceMock $signatureBookUserServiceMock;
    private CurrentUserInformationsMock $currentUserInformationsMock;
    private CreateAndUpdateUserInSignatoryBook $createAndUpdateUserInSignatoryBook;
    protected function setUp(): void
    {
        $this->signatureBookUserServiceMock = new MaarchParapheurUserServiceMock();
        $this->currentUserInformationsMock = new CurrentUserInformationsMock();
        $this->createAndUpdateUserInSignatoryBook = new CreateAndUpdateUserInSignatoryBook(
            $this->signatureBookUserServiceMock,
            $this->currentUserInformationsMock
        );
    }

    public function testTheUserHasNoExternalIdSoAnAccountIsCreatedInMaarchParapheur(): void
    {

        $ExpectedUser = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId([
                0 => 12
            ]);

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

    public function testTheUserAlreadyHasAnExternalIdAndItAlreadyExistsInMaarchParapheurThenTrueIsReturnedAndUserIsUpdate(): void
    {
        $ExpectedUser = (new User())
            ->setFirstname('firstname2')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId([
                0 => 12,
            ]);

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId([
                0 => 12
            ]);

        $this->signatureBookUserServiceMock->userExists = true;
        $newUser = $this->createAndUpdateUserInSignatoryBook->createAndUpdateUser($user);
        $this->assertTrue($this->signatureBookUserServiceMock->updateUserCalled);
        $this->assertEquals($ExpectedUser, $newUser);
    }

    public function testTheUserAlreadyHasAnExternalIdButItDoesNotExistInMaarchParapheurThenAnAccountIsCreated(): void
    {
        $ExpectedUser = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId([
                0 => 11,
                1 => 12,
            ]);

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setMail('mail')
            ->setLogin('userId')
            ->setExternalId([
                0 => 11
            ]);
        $this->signatureBookUserServiceMock->userExists = false;
        $newUser = $this->createAndUpdateUserInSignatoryBook->createAndUpdateUser($user);
        $this->assertTrue($this->signatureBookUserServiceMock->createUserCalled);
        $this->assertEquals($ExpectedUser, $newUser);
    }
}
