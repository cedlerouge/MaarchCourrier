<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   User Signature
 * @author  dev@maarch.org
 */

namespace MaarchCourrier\Tests\app\signatureBook\Application\Stamp;

use MaarchCourrier\SignatureBook\Application\Stamp\RetrieveUserStamps;
use MaarchCourrier\SignatureBook\Domain\Problems\AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem;
use MaarchCourrier\SignatureBook\Domain\UserSignature;
use MaarchCourrier\Tests\app\signatureBook\Mock\Stamp\SignatureRepositoryMock;
use MaarchCourrier\Tests\app\signatureBook\Mock\UserRepositoryRepositoryMock;
use MaarchCourrier\User\Domain\Problems\UserDoesNotExistProblem;
use PHPUnit\Framework\TestCase;

class RetrieveUserStampsTest extends TestCase
{
    private ?int $PREVIOUS_GLOBAL_ID;
    private UserRepositoryRepositoryMock $userRepository;
    private SignatureRepositoryMock $signatureService;
    private RetrieveUserStamps $retrieveUserStamps;

    protected function setUp(): void
    {
        if ($GLOBALS['id'] !== null) {
            $this->PREVIOUS_GLOBAL_ID = $GLOBALS['id'];
        }
        $this->userRepository = new UserRepositoryRepositoryMock();
        $this->signatureService = new SignatureRepositoryMock();
        $this->retrieveUserStamps = new RetrieveUserStamps($this->userRepository, $this->signatureService);
    }

    protected function tearDown(): void
    {
        if ($this->PREVIOUS_GLOBAL_ID !== null) {
            $GLOBALS['id'] = $this->PREVIOUS_GLOBAL_ID;
        }
    }

    public function testRetrieveUserStampsWithUserIdIs0AndReturnUserDoesNotExistException(): void
    {
        $this->expectException(UserDoesNotExistProblem::class);

        $this->retrieveUserStamps->getUserSignatures(0);
    }

    public function testRetrieveUserStampsWithUnknownUserAndReturnUserDoesNotExistException(): void
    {
        $this->userRepository->doesUserExist = false;

        $this->expectException(UserDoesNotExistProblem::class);

        $this->retrieveUserStamps->getUserSignatures(1);
    }

    public function testRetrieveUserStampsFromUser2WithUser1ConnectedReturnException(): void
    {
        $GLOBALS['id'] = 1;

        $this->expectException(AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem::class);

        $this->retrieveUserStamps->getUserSignatures(2);
    }

    public function testRetrieveUserStampsWithNoStampsToGetAndReturnEmptyList(): void
    {
        $GLOBALS['id'] = 1;
        $this->signatureService->doesSignatureStampsExist = false;

        $signatureStamps = $this->retrieveUserStamps->getUserSignatures(1);

        $this->assertIsArray($signatureStamps);
        $this->assertEmpty($signatureStamps);
    }

    public function testRetrieveUserStampsWithMultipleSignatureStampsAndReturnAList(): void
    {
        $GLOBALS['id'] = 1;
        $signatureStamps = $this->retrieveUserStamps->getUserSignatures(1);

        $this->assertIsArray($signatureStamps);
        $this->assertNotEmpty($signatureStamps);
        $this->assertIsObject($signatureStamps[0]);
        $this->assertIsObject($signatureStamps[1]);
        $this->assertInstanceOf(UserSignature::class, $signatureStamps[0]);
        $this->assertInstanceOf(UserSignature::class, $signatureStamps[1]);
    }
}
