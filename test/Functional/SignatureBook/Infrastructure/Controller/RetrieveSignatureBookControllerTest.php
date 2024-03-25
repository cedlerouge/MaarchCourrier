<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve Signature Book Controller Test
 * @author dev@maarch.org
 */

namespace Functional\SignatureBook\Infrastructure\Controller;

use Attachment\controllers\AttachmentController;
use Entity\controllers\ListInstanceController;
use MaarchCourrier\SignatureBook\Infrastructure\Controller\RetrieveSignatureBookController;
use MaarchCourrier\Tests\CourrierTestCase;
use Resource\controllers\ResController;
use Resource\controllers\ResourceListController;
use SrcCore\http\Response;

class RetrieveSignatureBookControllerTest extends CourrierTestCase
{
    private int $connectedUser = 19; //bbain
    private ?int $mainResourceId;

    protected function setUp(): void
    {
        $this->connectAsUser('mmanfred');
        $this->connectedUser = $GLOBALS['id'];

        $fileContent = file_get_contents('test/Functional/samples/test.txt');
        $encodedFile = base64_encode($fileContent);

        //create main document
        $this->mainResourceId = $this->createMainResource($encodedFile);

        //create attachments
        //  signable and intergrated
        //  not signable and intergrated
        //  signable and not intergrated
        $this->createAttachments($this->mainResourceId, $encodedFile);

        //create listinstance for main resource
        $this->createVisaCircuitForMainResource($this->mainResourceId);

        //send main resource to internal signature book
        $this->sendMainResourceToInternalSignatureBook($this->mainResourceId);

        //test when everything does well
        //$this->connectAsUser('bbain');
        //$this->connectedUser = $GLOBALS['id'];
    }

    private function createMainResource(string $encodedFileContent): int
    {
        $body = [
            'modelId'          => 1,
            'status'           => 'NEW',
            'encodedFile'      => $encodedFileContent,
            'format'           => 'txt',
            'confidentiality'  => false,
            'documentDate'     => '2019-01-01 17:18:47',
            'arrivalDate'      => '2019-01-01 17:18:47',
            'processLimitDate' => '2029-01-01',
            'doctype'          => 103,
            'destination'      => 4,
            'subject'          => 'Breaking News : Superman is alive - PHP unit',
            'typist'           => $this->connectedUser,
            'priority'         => 'poiuytre1357nbvc',
            'senders'          => [['type' => 'user', 'id' => 19]],
            'diffusionList'    => [["id" => 17, "mode" => "dest", "type" => "user"],["id" => 12, "mode" => "cc", "type" => "entity"], ["id" => 8, "mode" => "cc", "type" => "user"]],
            'integrations'     => ['inSignatureBook' => true]
        ];
        $fullRequest = $this->createRequestWithBody('POST', $body);

        $resController = new ResController();
        $response = $resController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        var_dump($responseBody);

        return (int)$responseBody->resId;
    }

    private function createAttachments(int $resIdMaster, string $encodedFileContent): void
    {
        //Signable and in signature book
        $body = [
            'title'         => 'Breaking News : Superman is alive - PHP unit',
            'type'          => 'response_project',
            'chrono'        => 'MAARCH/2024D/24',
            'resIdMaster'   => $resIdMaster,
            'encodedFile'   => $encodedFileContent,
            'format'        => 'txt',
            'typist'        => $this->connectedUser,
            'in_signature_book'  => true
        ];

        $fullRequest = $this->createRequestWithBody('POST', $body);

        $attachmentController = new AttachmentController();
        $response     = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        //(int)$responseBody->id
        var_dump($responseBody);

        //Not signable and in signature book
        $body['type'] = 'simple_attachment';
        $fullRequest = $this->createRequestWithBody('POST', $body);

        $attachmentController = new AttachmentController();
        $response     = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        var_dump($responseBody);

        //Signable and not in signature book
        $body['type'] = 'response_project';
        $body['in_signature_book'] = false;
        $fullRequest = $this->createRequestWithBody('POST', $body);

        $attachmentController = new AttachmentController();
        $response     = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        var_dump($responseBody);
    }

    private function createVisaCircuitForMainResource(int $resId): void
    {
        $body = [
            "resources" => [
                [
                    "resId" => $resId,
                    "listInstances" => [
                        [
                            "item_id"               => $this->connectedUser,
                            "item_type"             => "user",
                            "item_entity"           => "Pôle Jeunesse et Sport",
                            "labelToDisplay"        => "Barbara BAIN",
                            "externalId"            => null,
                            "difflist_type"         => "VISA_CIRCUIT",
                            "signatory"             => false,
                            "requested_signature"   => true,
                            "hasPrivilege"          => true,
                            "isValid"               => true,
                            "currentRole"           => "sign"
                        ]
                    ]
                ]
            ]
        ];
        $fullRequest = $this->createRequestWithBody('PUT', $body);

        $listInstanceController = new ListInstanceController();
        $response     = $listInstanceController->updateCircuits($fullRequest, new Response(), ['type' => 'visaCircuit']);
        $responseBody = json_decode((string)$response->getBody());
        var_dump('createVisaCircuitForMainResource', $responseBody);
    }

    private function sendMainResourceToInternalSignatureBook(int $mainResourceId): void
    {
        $body = [
            "resources" => [
                337
            ],
            "note" => [
                "content" => "",
                "entities" => []
            ]
        ];
        $fullRequest = $this->createRequestWithBody('PUT', $body);
        $args = [
            'userId'    => $this->connectedUser,
            'groupId'   => 2,
            'basketId'  => 4,
            'actionId'  => 414
        ];

        $resourceListController = new ResourceListController();
        $resourceListController->setAction($fullRequest, new Response(), $args);
    }

    public function testGetSignatureBookResources(): void
    {
        $args = [
            'userId'    => $this->connectedUser,
            'groupId'   => 4,
            'basketId'  => 16,
            'resId'     => $this->mainResourceId
        ];
        $fullRequest = $this->createRequestWithBody('GET', $args);

        $retrieveSignatureBookController = new RetrieveSignatureBookController();
        $response = $retrieveSignatureBookController->getSignatureBook($fullRequest, new Response(), $args);
        $responseBody = json_decode((string)$response->getBody());

        var_dump($responseBody);
    }
}
