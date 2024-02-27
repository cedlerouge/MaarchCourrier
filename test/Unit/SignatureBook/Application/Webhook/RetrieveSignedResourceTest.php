<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Application\Webhook;

use MaarchCourrier\SignatureBook\Application\Webhook\RetrieveSignedResource;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\StoreResourceProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\CurrentUserInformationsMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\CurlServiceMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\ResourceToSignRepositoryMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\StoreSignedResourceServiceMock;
use PHPUnit\Framework\TestCase;

class RetrieveSignedResourceTest extends TestCase
{
    private CurrentUserInformationsMock $currentUserRepositoryMock;

    private CurlServiceMock $curlServiceMock;
    private RetrieveSignedResource $retrieveSignedResource;

    private SignedResource $signedResource;
    private string $retrieveDocUri = "http://10.1.5.12/maarch-parapheur-api/rest/documents/11/content?mode=base64&type=esign";

    protected function setUp(): void
    {
        $this->currentUserRepositoryMock = new CurrentUserInformationsMock();
        $this->curlServiceMock = new CurlServiceMock();

        $this->retrieveSignedResource = new RetrieveSignedResource(
            $this->currentUserRepositoryMock,
            $this->curlServiceMock
        );

        $this->signedResource->setResIdSigned(10);
        $this->signedResource->setResIdMaster(100);
        $this->signedResource->setStatus('VAL');
    }

    protected function tearDown(): void
    {
        $this->signedResource->setResIdSigned(10);
        $this->signedResource->setResIdMaster(100);
        $this->signedResource->setStatus('VAL');
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws CurlRequestErrorProblem
     */
    public function testCanRetrieveSignedResource(): void
    {
        $signedResource = $this->retrieveSignedResource->retrieve($this->signedResource, $this->retrieveDocUri);
        $this->assertSame($signedResource->getResIdSigned(), $this->signedResource->getResIdSigned());
        $this->assertSame($signedResource->getResIdMaster(), $this->signedResource->getResIdMaster());
        $this->assertNotNull($signedResource->getEncodedContent());
    }

    /**
     * @throws CurlRequestErrorProblem
     */
    public function testCannotRetrieveSignedResourceIfUserTokenNotFound(): void
    {
        $this->currentUserRepositoryMock->token = '';
        $this->expectException(CurrentTokenIsNotFoundProblem::class);
        $this->retrieveSignedResource->retrieve($this->signedResource, $this->retrieveDocUri);
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     */
    public function testCannotRetrieveSignedResourceOnBadCurlRequest(): void
    {
        $this->curlServiceMock->badRequest = true;
        $this->curlServiceMock->httpCode = 403;
        $this->expectException(CurlRequestErrorProblem::class);
        $this->retrieveSignedResource->retrieve($this->signedResource, $this->retrieveDocUri);
    }
}
