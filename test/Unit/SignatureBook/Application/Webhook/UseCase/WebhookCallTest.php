<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Application\Webhook\UseCase;

use MaarchCourrier\SignatureBook\Application\Webhook\RetrieveSignedResource;
use MaarchCourrier\SignatureBook\Application\Webhook\StoreSignedResource;
use MaarchCourrier\SignatureBook\Application\Webhook\UseCase\WebhookCall;
use MaarchCourrier\SignatureBook\Application\Webhook\WebhookValidation;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdMasterNotCorrespondingProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\StoreResourceProblem;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action\CurrentUserInformationsMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\CurlServiceMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\ResourceToSignRepositoryMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\StoreSignedResourceServiceMock;
use PHPUnit\Framework\TestCase;

class WebhookCallTest extends TestCase
{
    private WebhookCall $webhookCall;

    private array $bodySentByMP = [
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
        $currentUserInformations = new CurrentUserInformationsMock();
        $curlService = new CurlServiceMock();
        $resourceToSignRepository = new ResourceToSignRepositoryMock();
        $storeSignedResourceService = new StoreSignedResourceServiceMock();

        $webhookValidation = new WebhookValidation($resourceToSignRepository);
        $retrieveSignedResource = new RetrieveSignedResource($currentUserInformations, $curlService);
        $storeSignedResource = new StoreSignedResource($resourceToSignRepository, $storeSignedResourceService);

        $this->webhookCall = new WebhookCall($webhookValidation, $retrieveSignedResource, $storeSignedResource);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws CurlRequestErrorProblem
     * @throws StoreResourceProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     */
    public function testWebhookCallSuccess(): void
    {
        $id = $this->webhookCall->execute($this->bodySentByMP);
        $this->assertIsInt($id);
    }
}
