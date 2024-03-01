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
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdMasterNotCorrespondingProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\UserRepositoryMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook\ResourceToSignRepositoryMock;
use MaarchCourrier\User\Domain\Problem\UserDoesNotExistProblem;
use PHPUnit\Framework\TestCase;

class WebhookValidationTest extends TestCase
{
    private ResourceToSignRepositoryMock $resourceToSignRepositoryMock;
    private WebhookValidation $webhookValidation;
    private UserRepositoryMock $userRepositoryMock;
    private array $bodySentByMP = [
        'identifier'     => 'TDy3w2zAOM41M216',
        'signatureState' => [
            'error'       => '',
            'state'       => 'VAL',
            'message'     => '',
            'updatedDate' => "2024-03-01T13:19:59+01:00"
        ],
        'token'          => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJyZXNfaWQiOjE1OSwidXNlcklkIjoxMH0.olM35fZrHlsYXTRceohEqijjIOqCNolVSbw0v5eKW78',
        'retrieveDocUri' => "http://10.1.5.12/maarch-parapheur-api/rest/documents/11/content?mode=base64&type=esign"
    ];

    private array $decodedToken = [
        'res_id' => 159,
        'userId' => 10
    ];

    protected function setUp(): void
    {
        $this->resourceToSignRepositoryMock = new ResourceToSignRepositoryMock();
        $this->userRepositoryMock = new UserRepositoryMock();
        $this->webhookValidation = new WebhookValidation(
            $this->resourceToSignRepositoryMock, $this->userRepositoryMock
        );
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
            'token'          => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJyZXNfaWQiOjE1OSwidXNlcklkIjoxMH0.olM35fZrHlsYXTRceohEqijjIOqCNolVSbw0v5eKW78',
            'retrieveDocUri' => "http://10.1.5.12/maarch-parapheur-api/rest/documents/11/content?mode=base64&type=esign"
        ];

        $this->resourceToSignRepositoryMock->attachmentNotExists = false;
        $this->resourceToSignRepositoryMock->resourceAlreadySigned = false;
        $this->resourceToSignRepositoryMock->resIdConcordingWithResIdMaster = true;
    }


    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws UserDoesNotExistProblem
     */
    public function testValidationSuccess(): void
    {
        $this->decodedToken = [
            'res_id'        => 159,
            'res_id_master' => 75,
            'userId'        => 10
        ];
        $signedResource = $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
        $this->assertInstanceOf(SignedResource::class, $signedResource);
        $this->assertSame($signedResource->getResIdSigned(), $this->decodedToken['res_id']);
        $this->assertSame($signedResource->getResIdMaster(), $this->decodedToken['res_id_master']);
        $this->assertSame($signedResource->getStatus(), $this->bodySentByMP['signatureState']['state']);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws UserDoesNotExistProblem
     */
    public function testValidationErrorIfRetrieveUrlIsEmpty(): void
    {
        $this->bodySentByMP['retrieveDocUri'] = '';
        $this->expectException(RetrieveDocumentUrlEmptyProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws UserDoesNotExistProblem
     */
    public function testValidationErrorIfResIdIsMissing(): void
    {
        unset($this->decodedToken['res_id']);
        $this->expectException(ResourceIdEmptyProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws UserDoesNotExistProblem
     */
    public function testValidationErrorIfResIdNotCorrespondingToResIdMaster(): void
    {
        $this->decodedToken = [
            'res_id'        => 159,
            'res_id_master' => 75,
            'userId'        => 10
        ];

        $this->resourceToSignRepositoryMock->resIdConcordingWithResIdMaster = false;

        $this->expectException(ResourceIdMasterNotCorrespondingProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws UserDoesNotExistProblem
     */
    public function testValidationErrorIfResourceIsAlreadySigned(): void
    {
        $this->resourceToSignRepositoryMock->resourceAlreadySigned = true;

        $this->expectException(ResourceAlreadySignProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws UserDoesNotExistProblem
     */
    public function testValidationErrorIfAttachmentAlreadySigned(): void
    {
        $this->decodedToken = [
            'res_id'        => 159,
            'res_id_master' => 75,
            'userId'        => 10
        ];

        $this->resourceToSignRepositoryMock->resourceAlreadySigned = true;

        $this->expectException(ResourceAlreadySignProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }

    /**
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws UserDoesNotExistProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testValidationErrorIfAttachmentNotInPerimeter(): void
    {
        $this->decodedToken = [
            'res_id'        => 159,
            'res_id_master' => 75,
            'userId'        => 10
        ];

        $this->resourceToSignRepositoryMock->attachmentNotExists = true;

        $this->expectException(AttachmentOutOfPerimeterProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceAlreadySignProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     */
    public function testValidationErrorIfUserNotExists(): void
    {
        $this->userRepositoryMock->doesUserExist = false;
        $this->expectException(UserDoesNotExistProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws UserDoesNotExistProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function testValidationErrorIfTokenIsNotSet(): void
    {
        unset($this->bodySentByMP['token']);
        $this->expectException(CurrentTokenIsNotFoundProblem::class);
        $this->webhookValidation->validate($this->bodySentByMP, $this->decodedToken);
    }
}
