<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   WebhookValidation test
 * @author  dev@maarch.org
 */

namespace MaarchCourrier\Tests\Unit\SignatureBook\Application\Webhook;

use MaarchCourrier\SignatureBook\Application\Webhook\WebhookValidation;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdMasterNotCorrespondingProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\ResourceToSignRepositoryMock;
use PHPUnit\Framework\TestCase;

class WebhookValidationTest extends TestCase
{
    private ResourceToSignRepositoryMock $resourceToSignRepositoryMock;
    private WebhookValidation $webhookValidation;
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
        $this->resourceToSignRepositoryMock = new ResourceToSignRepositoryMock();
        $this->webhookValidation = new WebhookValidation($this->resourceToSignRepositoryMock);
    }

    protected function tearDown(): void
    {
        $this->bodySentByMP = [
            'identifier'     => 'TDy3w2zAOM41M216',
            'signatureState' => [
                'error'       => '',
                'state'       => 'VAL',
                'message'     => '',
                'updatedDate' => null
            ],
            'payload'        => [
                'res_id'        => 10,
                'idParapheur'   => 11,
                'res_id_master' => 100
            ],
            'retrieveDocUri' => "http://10.1.5.12/maarch-parapheur-api/rest/documents/11/content?mode=base64&type=esign"
        ];

        $this->resourceToSignRepositoryMock->attachmentNotExists = false;
        $this->resourceToSignRepositoryMock->resourceAlreadySigned = false;
        $this->resourceToSignRepositoryMock->resIdConcordingWithResIdMaster = true;
    }


    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     */
    public function testValidationSuccess(): void
    {
        $signedResource = $this->webhookValidation->validate($this->bodySentByMP);
        $this->assertInstanceOf(SignedResource::class, $signedResource);
        $this->assertSame($signedResource->getResIdSigned(), $this->bodySentByMP['payload']['res_id']);
        $this->assertSame($signedResource->getResIdMaster(), $this->bodySentByMP['payload']['res_id_master']);
        $this->assertSame($signedResource->getStatus(), $this->bodySentByMP['signatureState']['state']);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testValidationErrorIfRetrieveUrlIsEmpty(): void
    {
        $this->bodySentByMP['retrieveDocUri'] = '';
        $this->expectException(RetrieveDocumentUrlEmptyProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceAlreadySignProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     */
    public function testValidationErrorIfResIdIsMissing(): void
    {
        unset($this->bodySentByMP['payload']['res_id']);
        $this->expectException(ResourceIdEmptyProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     */
    public function testValidationErrorIfResIdNotCorrespondingToResIdMaster(): void
    {
        $this->resourceToSignRepositoryMock->resIdConcordingWithResIdMaster = false;

        $this->expectException(ResourceIdMasterNotCorrespondingProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     */
    public function testValidationErrorIfResourceIsAlreadySigned(): void
    {
        $this->resourceToSignRepositoryMock->resourceAlreadySigned = true;

        unset($this->bodySentByMP['payload']['res_id_master']);

        $this->expectException(ResourceAlreadySignProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testValidationErrorIfAttachmentAlreadySigned(): void
    {
        $this->resourceToSignRepositoryMock->resourceAlreadySigned = true;

        $this->expectException(ResourceAlreadySignProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testValidationErrorIfAttachmentNotInPerimeter(): void
    {
        $this->resourceToSignRepositoryMock->attachmentNotExists = true;

        $this->expectException(AttachmentOutOfPerimeterProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP);
    }
}
