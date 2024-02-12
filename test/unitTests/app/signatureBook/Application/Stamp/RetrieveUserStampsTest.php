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
use MaarchCourrier\SignatureBook\Domain\Problem\AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem;
use MaarchCourrier\SignatureBook\Domain\UserSignature;
use MaarchCourrier\Tests\app\signatureBook\Mock\Stamp\SignatureRepositoryMock;
use MaarchCourrier\Tests\app\signatureBook\Mock\UserRepositoryMock;
use MaarchCourrier\User\Domain\Problems\UserDoesNotExistProblem;
use PHPUnit\Framework\TestCase;

class RetrieveUserStampsTest extends TestCase
{
    private ?int $PREVIOUS_GLOBAL_ID;
    private UserRepositoryMock $userRepository;
    private SignatureRepositoryMock $signatureService;
    private RetrieveUserStamps $retrieveUserStamps;

    protected function setUp(): void
    {
        if ($GLOBALS['id'] !== null) {
            $this->PREVIOUS_GLOBAL_ID = $GLOBALS['id'];
        }
        $this->userRepository = new UserRepositoryMock();
        $this->signatureService = new SignatureRepositoryMock();
        $this->retrieveUserStamps = new RetrieveUserStamps($this->userRepository, $this->signatureService);
    }

    protected function tearDown(): void
    {
        if ($this->PREVIOUS_GLOBAL_ID !== null) {
            $GLOBALS['id'] = $this->PREVIOUS_GLOBAL_ID;
        }
    }

    public function provideConnectedUserIds(): array
    {
        return [
            "Connected User Id '2'" => [
                'userId' => 2
            ],
            "Connected User Id '3'" => [
                'input' => 3
            ],
            "Connected User Id '4'" => [
                'input' => 4
            ],
            "Connected User Id '10'" => [
                'input' => 10
            ],
        ];
    }

    public function testCannotGetUserStampsIfTheUserDoesNotExistReturnAnException(): void
    {
        $this->userRepository->doesUserExist = false;

        $this->expectException(UserDoesNotExistProblem::class);

        $this->retrieveUserStamps->getUserSignatures(1);
    }

    /**
     * @dataProvider provideConnectedUserIds
     */
    public function testConnectedUserCannotRetrieveUserId1StampsReturnException(int $userId): void
    {
        $GLOBALS['id'] = $userId;

        $this->expectException(AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem::class);

        $this->retrieveUserStamps->getUserSignatures(1);
    }

    public function testConnectedUserCanRetrieveHisListOfEmptyStampsIfHeHasNoUserSignatures(): void
    {
        $GLOBALS['id'] = 1;
        $this->signatureService->doesSignatureStampsExist = false;

        $signatureStamps = $this->retrieveUserStamps->getUserSignatures(1);

        $this->assertIsArray($signatureStamps);
        $this->assertEmpty($signatureStamps);
    }

    public function testConnectedUserCanRetrieveHisListOfUserSignatures(): void
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
