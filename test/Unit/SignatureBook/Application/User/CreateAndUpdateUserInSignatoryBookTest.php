<?php

namespace Unit\SignatureBook\Application\User;

use MaarchCourrier\SignatureBook\Application\User\CreateAndUpdateUserInSignatoryBook;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\CurrentUserInformationsMock;
use MaarchCourrier\User\Domain\User;
use PHPUnit\Framework\TestCase;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\User\MaarchParapheurUserServiceMock;

class CreateAndUpdateUserInSignatoryBookTest extends TestCase
{


    protected function setUp(): void
    {

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testTheUserHasNoExternalIdSoAnAccountIsCreatedInMaarchParapheur()
    {
        $signatureBookUserServiceMock = new MaarchParapheurUserServiceMock();
        $currentUser = new CurrentUserInformationsMock();
        $createUser = new CreateAndUpdateUserInSignatoryBook($signatureBookUserServiceMock, $currentUser);
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
        $newUser = $createUser->createAndUpdateUser($user);
        $this->assertTrue($signatureBookUserServiceMock->createUserCalled);
        $this->assertEquals($ExpectedUser, $newUser);
    }
}
