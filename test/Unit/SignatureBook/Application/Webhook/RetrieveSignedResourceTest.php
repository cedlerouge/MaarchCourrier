<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Application\Webhook;

use MaarchCourrier\SignatureBook\Application\Webhook\RetrieveSignedResource;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\CurrentUserInformationsMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\CurlServiceMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\ResourceToSignRepositoryMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\StoreSignedResourceServiceMock;
use PHPUnit\Framework\TestCase;

class RetrieveSignedResourceTest extends TestCase
{
    private CurrentUserInformationsMock $currentUserRepositoryMock;
    private ResourceToSignRepositoryMock $resourceToSignRepositoryMock;
    private StoreSignedResourceServiceMock $storeSignedResourceServiceMock;
    private CurlServiceMock $curlServiceMock;
    private RetrieveSignedResource $retrieveSignedResource;

    private array $data = [
        'identifier'     => 'TDy3w2zAOM41M216',
        'signatureState' => [
            'error'       => '',
            'state'       => 'VAL',
            'message'     => '',
            'updatedDate' => null
        ],
        'payload'        => [
            'res_id'        => 10,
            'idParapheur'   => 10,
            'res_id_master' => 100
        ],
        'retrieveDocUri' => "http://10.1.5.12/maarch-parapheur-api/rest/documents/11/content?mode=base64&type=esign"
    ];

    protected function setUp(): void
    {
        $this->currentUserRepositoryMock = new CurrentUserInformationsMock();
        $this->resourceToSignRepositoryMock = new ResourceToSignRepositoryMock();
        $this->storeSignedResourceServiceMock = new StoreSignedResourceServiceMock();
        $this->curlServiceMock = new CurlServiceMock();

        $this->retrieveSignedResource = new RetrieveSignedResource(
            $this->currentUserRepositoryMock,
            $this->resourceToSignRepositoryMock,
            $this->storeSignedResourceServiceMock,
            $this->curlServiceMock
        );
    }

    protected function tearDown(): void
    {
        $this->data = [
            'identifier'     => 'TDy3w2zAOM41M216',
            'signatureState' => [
                'error'       => '',
                'state'       => 'VAL',
                'message'     => '',
                'updatedDate' => null
            ],
            'payload'        => [
                'res_id'        => 10,
                'idParapheur'   => 10,
                'res_id_master' => 100
            ],
            'retrieveDocUri' => "http://10.1.5.12/maarch-parapheur-api/rest/documents/11/content?mode=base64&type=esign"
        ];
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws CurlRequestErrorProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testCanRetrieveSignedResource(): void
    {
        $signedResource = $this->retrieveSignedResource->retrieve($this->data);
        $this->assertSame($signedResource->getResIdSigned(), $this->data['payload']['res_id']);
        $this->assertSame($signedResource->getResIdMaster(), $this->data['payload']['res_id_master']);
        $this->assertNotNull($signedResource->getEncodedContent());
    }

    public function testCannotRetrieveSignedResourceIfUrlIsEmpty(): void
    {
        $this->data['retrieveDocUri'] = "";
        $this->expectException(RetrieveDocumentUrlEmptyProblem::class);
        $this->retrieveSignedResource->retrieve($this->data);
    }

    public function testCannotRetrieveSignedResourceIfUserTokenNotFound(): void
    {
        $this->currentUserRepositoryMock->token = '';
        $this->expectException(CurrentTokenIsNotFoundProblem::class);
        $this->retrieveSignedResource->retrieve($this->data);
    }

    public function testCannotRetrieveSignedResourceOnBadCurlRequest(): void
    {
        $this->curlServiceMock->badRequest = true;
        $this->curlServiceMock->httpCode = 403;
        $this->expectException(CurlRequestErrorProblem::class);
        $this->retrieveSignedResource->retrieve($this->data);
    }
}
