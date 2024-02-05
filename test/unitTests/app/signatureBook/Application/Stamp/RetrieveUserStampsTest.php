<?php

namespace unitTests\app\signatureBook\Application\Stamp;

use PHPUnit\Framework\TestCase;
use SignatureBook\Application\Stamp\RetrieveUserStamps;
use SrcCore\Domain\Exceptions\UserDoesNotExistException;
use unitTests\app\signatureBook\Mock\Stamp\SignatureServiceMock;
use unitTests\app\signatureBook\Mock\UserMock;

class RetrieveUserStampsTest extends TestCase
{
    private UserMock $user;
    private SignatureServiceMock $signatureService;
    private RetrieveUserStamps $retrieveUserStamps;

    protected function setUp(): void
    {
        $this->user = new UserMock();
        $this->signatureService = new SignatureServiceMock();
        $this->retrieveUserStamps = new RetrieveUserStamps($this->user, $this->signatureService);
    }

    public function retrieveUserStampsWithUserIdIs0ReturnUserDoesNotExistException()
    {
        $this->user->doesUserExist = false;

        $this->expectExceptionObject(UserDoesNotExistException::class);

        $this->retrieveUserStamps->getUserSignatures(0);
    }
}
