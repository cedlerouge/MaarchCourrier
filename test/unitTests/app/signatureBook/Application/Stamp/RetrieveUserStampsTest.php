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

use PHPUnit\Framework\TestCase;
use SignatureBook\Application\Stamp\RetrieveUserStamps;
use SignatureBook\Domain\Exceptions\UserDoesNotExistException;
use MaarchCourrier\Tests\app\signatureBook\Mock\Stamp\SignatureServiceMock;
use MaarchCourrier\Tests\app\signatureBook\Mock\UserRepositoryMock;
use SignatureBook\Domain\UserSignature;

class RetrieveUserStampsTest extends TestCase
{
    private UserRepositoryMock $userRepository;
    private SignatureServiceMock $signatureService;
    private RetrieveUserStamps $retrieveUserStamps;

    protected function setUp(): void
    {
        $this->userRepository = new UserRepositoryMock();
        $this->signatureService = new SignatureServiceMock();
        $this->retrieveUserStamps = new RetrieveUserStamps($this->userRepository, $this->signatureService);
    }

    public function testRetrieveUserStampsWithUserIdIs0AndReturnUserDoesNotExistException(): void
    {
        $this->expectException(UserDoesNotExistException::class);

        $this->retrieveUserStamps->getUserSignatures(0);
    }

    public function testRetrieveUserStampsWithUnknownUserAndReturnUserDoesNotExistException(): void
    {
        $this->userRepository->doesUserExist = false;

        $this->expectException(UserDoesNotExistException::class);

        $this->retrieveUserStamps->getUserSignatures(1);
    }

    public function testRetrieveUserStampsWithNoStampsToGetAndReturnEmptyList(): void
    {
        $this->signatureService->doesSignatureStampsExist = false;

        $signatureStamps = $this->retrieveUserStamps->getUserSignatures(1);

        $this->assertIsArray($signatureStamps);
        $this->assertEmpty($signatureStamps);
    }

    public function testRetrieveUserStampsWithMultipleSignatureStampsAndReturnAList(): void
    {
        $signatureStamps = $this->retrieveUserStamps->getUserSignatures(1);

        $this->assertIsArray($signatureStamps);
        $this->assertNotEmpty($signatureStamps);
        $this->assertIsObject($signatureStamps[0]);
        $this->assertIsObject($signatureStamps[1]);
        $this->assertInstanceOf(UserSignature::class, $signatureStamps[0]);
        $this->assertInstanceOf(UserSignature::class, $signatureStamps[1]);
    }
}
