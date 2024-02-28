<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   WebhookCall test
 * @author  dev@maarch.org
 */

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
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\SignatureHistoryServiceMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\StoreSignedResourceServiceMock;
use PHPUnit\Framework\TestCase;

class WebhookCallTest extends TestCase
{
    private WebhookCall $webhookCall;

    private SignatureHistoryServiceMock $historyService;

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
        $this->historyService = new SignatureHistoryServiceMock();

        $webhookValidation = new WebhookValidation($resourceToSignRepository);
        $retrieveSignedResource = new RetrieveSignedResource($currentUserInformations, $curlService);
        $storeSignedResource = new StoreSignedResource($resourceToSignRepository, $storeSignedResourceService);

        $this->webhookCall = new WebhookCall(
            $webhookValidation,
            $retrieveSignedResource,
            $storeSignedResource,
            $this->historyService
        );
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
        $return = $this->webhookCall->execute($this->bodySentByMP);
        $this->assertTrue($this->historyService->addedInHistoryValidation);
        $this->assertIsInt($return);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws CurlRequestErrorProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws StoreResourceProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     */
    public function testWebhookCallRefusState(): void
    {
        $this->bodySentByMP['signatureState']['state'] = 'REF';
        $this->bodySentByMP['signatureState']['message'] = 'Tout est ok';

        $return = $this->webhookCall->execute($this->bodySentByMP);
        $this->assertTrue($this->historyService->addedInHistoryRefus);
        $this->assertIsArray($return);
        $this->assertSame(
            $return['message'],
            'Status of signature is ' . $this->bodySentByMP['signatureState']['state'] . " : " . $this->bodySentByMP['signatureState']['message']
        );
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurlRequestErrorProblem
     * @throws StoreResourceProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceAlreadySignProblem
     */
    public function testWebhookCallErrorState(): void
    {
        $this->bodySentByMP['signatureState']['state'] = 'ERROR';
        $this->bodySentByMP['signatureState']['error'] = 'Error during signature';

        $return = $this->webhookCall->execute($this->bodySentByMP);
        $this->assertTrue($this->historyService->addedInHistoryError);
        $this->assertIsArray($return);
        $this->assertSame(
            $return['message'],
            'Status of signature is ' . $this->bodySentByMP['signatureState']['state'] . " : " . $this->bodySentByMP['signatureState']['error']
        );
    }
}
