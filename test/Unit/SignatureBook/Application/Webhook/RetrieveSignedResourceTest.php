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

    private array $returnFromCurlRequestParapheur = [];

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

        $this->returnFromCurlRequestParapheur = [
            'encodedDocument' => base64_encode(file_get_contents("install/samples/attachments/2021/03/0001/0003_1072724674.pdf")),
            'mimetype' => "application/pdf",
            'filename' => "PDF_signature.pdf"
        ];
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

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws CurlRequestErrorProblem
     */
    public function testCannotRetrieveSignedResourceIfUrlIsEmpty(): void
    {
        $this->data['retrieveDocUri'] = "";
        $this->expectException(RetrieveDocumentUrlEmptyProblem::class);
        $this->retrieveSignedResource->retrieve($this->data);
    }

    /**
     * @throws CurlRequestErrorProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testCannotRetrieveSignedResourceIfUserTokenNotFound(): void
    {
        $this->currentUserRepositoryMock->token = '';
        $this->expectException(CurrentTokenIsNotFoundProblem::class);
        $this->retrieveSignedResource->retrieve($this->data);
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testCannotRetrieveSignedResourceOnBadCurlRequest(): void
    {
        $this->curlServiceMock->badRequest = true;
        $this->curlServiceMock->httpCode = 403;
        $this->expectException(CurlRequestErrorProblem::class);
        $this->retrieveSignedResource->retrieve($this->data);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws StoreResourceProblem
     */
    public function testCanStoreSignedVersionOfResource(): void
    {
        $signedResource = new SignedResource();
        $signedResource->setResIdSigned(10);
        $signedResource->setStatus("VAL");
        $signedResource->setEncodedContent($this->returnFromCurlRequestParapheur['encodedDocument']);

        $newId = $this->retrieveSignedResource->store($signedResource);
        $this->assertSame($newId, $signedResource->getResIdSigned());
        $this->assertTrue($this->resourceToSignRepositoryMock->signedVersionCreate);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws StoreResourceProblem
     */
    public function testCannotStoreSignedVersionOfResourceIfResourceAlreadyHaveASignedVersion(): void
    {
        $this->resourceToSignRepositoryMock->resourceAlreadySigned = true;


        $signedResource = new SignedResource();
        $signedResource->setResIdSigned(10);
        $signedResource->setStatus("VAL");
        $signedResource->setEncodedContent($this->returnFromCurlRequestParapheur['encodedDocument']);

        $this->expectException(ResourceAlreadySignProblem::class);
        $newId = $this->retrieveSignedResource->store($signedResource);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     */
    public function testCannotStoreSignedVersionOfResourceIfStorageFunctionError(): void
    {
        $this->storeSignedResourceServiceMock->errorStorage = true;

        $signedResource = new SignedResource();
        $signedResource->setResIdSigned(10);
        $signedResource->setResIdMaster(null);
        $signedResource->setStatus("VAL");
        $signedResource->setEncodedContent($this->returnFromCurlRequestParapheur['encodedDocument']);

        $this->expectException(StoreResourceProblem::class);
        $newId = $this->retrieveSignedResource->store($signedResource);
    }


    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws StoreResourceProblem
     */
    public function testCanStoreSignedVersionOfAttachment(): void
    {
        $this->storeSignedResourceServiceMock->resIdNewSignedDoc = 1;

        $signedResource = new SignedResource();
        $signedResource->setResIdSigned(100);
        $signedResource->setResIdMaster(10);
        $signedResource->setStatus("VAL");
        $signedResource->setEncodedContent($this->returnFromCurlRequestParapheur['encodedDocument']);

        $newId = $this->retrieveSignedResource->store($signedResource);
        $this->assertSame($newId, $this->storeSignedResourceServiceMock->resIdNewSignedDoc);
        $this->assertTrue($this->resourceToSignRepositoryMock->attachmentUpdated);
    }

    /**
     * @throws ResourceAlreadySignProblem
     * @throws StoreResourceProblem
     */
    public function testCannotStoreSignedVersionOfAttachmentIfNotInPerimeter(): void
    {
        $this->resourceToSignRepositoryMock->attachmentNotExists = true;

        $signedResource = new SignedResource();
        $signedResource->setResIdSigned(100);
        $signedResource->setResIdMaster(10);
        $signedResource->setStatus("VAL");
        $signedResource->setEncodedContent($this->returnFromCurlRequestParapheur['encodedDocument']);

        $this->expectException(AttachmentOutOfPerimeterProblem::class);
        $newId = $this->retrieveSignedResource->store($signedResource);
    }
}
