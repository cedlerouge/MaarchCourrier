<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ContinueCircuitActionTest class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Tests\unitTests\app\signatureBook\Application\Action;

use Exception;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\MaarchParapheurSignatureNotAppliedException;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundException;
use MaarchCourrier\Tests\app\signatureBook\Mock\Action\CurrentUserInformationsMock;
use MaarchCourrier\Tests\app\signatureBook\Mock\Action\MaarchParapheurSignatureServiceMock;
use MaarchCourrier\Tests\app\signatureBook\Mock\Action\SignatureServiceJsonConfigLoaderMock;
use PHPUnit\Framework\TestCase;
use MaarchCourrier\SignatureBook\Application\Action\ContinueCircuitAction;


class ContinueCircuitActionTest extends TestCase
{
    private ContinueCircuitAction $continueCircuitAction;

    private CurrentUserInformationsMock $currentUserRepositoryMock;

    private SignatureServiceJsonConfigLoaderMock $configLoaderMock;

    private MaarchParapheurSignatureServiceMock $signatureServiceMock;
    protected function setUp(): void
    {
        $this->currentUserRepositoryMock = new CurrentUserInformationsMock();
        $this->configLoaderMock = new SignatureServiceJsonConfigLoaderMock();
        $this->signatureServiceMock = new MaarchParapheurSignatureServiceMock();
        $this->continueCircuitAction = new ContinueCircuitAction(
            $this->currentUserRepositoryMock,
            $this->signatureServiceMock,
            $this->configLoaderMock,
            true
        );
    }

    /**
     * @throws Exception
     */
    public function testThePluginIsEnabledThenTrueIsReturned(): void
    {
        $result = $this->continueCircuitAction->execute(1, [], []);
        self::assertTrue($result);
    }

    public function testTheSignatureIsAppliedAndTrueIsReturned(): void
    {
        $this->signatureServiceMock->applySignature = ['errors' => 'error returned'];
        $data = [
            "idDocument" => 4,
            "signatures" => [],
            "hashSignature" => "a41584bdd99fbfeabc7b45f6fa89a4fa075b3070d44b869af35cea87a1584caa568f696d0c9dabad2481dfb
            bc016fd3562fa009d1b3f3cb31e76adfe5cd5b6026a30d5c1bf78e0d85250bd3709ac45a48276242abf3840f55f00ccbade965c202b
            e107c2df02622974c795bb07537de9a8df6cf0c9497c08f261e89ee4617bec",
            "signatureContentLength" => 30000,
            "signatureFieldName" => "Signature"
        ];
        $this->expectException(MaarchParapheurSignatureNotAppliedException::class);
        $this->continueCircuitAction->execute(1, $data, []);
    }

    public function testExceptionIsReturnedWhenNoSignatureBookConfigFound(): void
    {
        $this->configLoaderMock->signatureServiceConfigLoader = null;
        $this->expectException(SignatureBookNoConfigFoundException::class);
        $this->continueCircuitAction->execute(1, [], []);
    }

    public function testExceptionIsReturnedWhenNoTokenIsFound(): void
    {
        $this->currentUserRepositoryMock->token = '';
        $this->expectException(CurrentTokenIsNotFoundProblem::class);
        $this->continueCircuitAction->execute(1, [], []);
    }

    public function testAnExceptionIsReturnedDuringApplicationOfTheSignature(): void
    {
        $data = [
            "idDocument" => 4,
            "signatures" => [],
            "hashSignature" => "a41584bdd99fbfeabc7b45f6fa89a4fa075b3070d44b869af35cea87a1584caa568f696d0c9dabad2481dfb
            bc016fd3562fa009d1b3f3cb31e76adfe5cd5b6026a30d5c1bf78e0d85250bd3709ac45a48276242abf3840f55f00ccbade965c202b
            e107c2df02622974c795bb07537de9a8df6cf0c9497c08f261e89ee4617bec",
            "signatureContentLength" => 30000,
            "signatureFieldName" => "Signature",

        ];
        $result = $this->continueCircuitAction->execute(1, $data, []);
        $this->assertTrue($result);
    }

}
