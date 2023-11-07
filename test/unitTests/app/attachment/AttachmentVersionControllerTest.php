<?php
/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\attachment;

use Attachment\controllers\AttachmentController;
use Attachment\models\AttachmentModel;
use MaarchCourrier\Tests\CourrierTestCase;
use SrcCore\http\Response;

class AttachmentVersionControllerTest extends CourrierTestCase
{
    private static $originalAttachmentId = null;
    private static $versionAttachmentId = null;
    private static $signedVersionAttachmentId = null;

    public function testAddNewVersionSuccess()
    {
        $attachmentController = new AttachmentController();

        // ARRANGE
        /*
         * - Ajout d'une PJ
         */
        $fileContent = file_get_contents('test/unitTests/samples/test.txt');
        $encodedFile = base64_encode($fileContent);

        $args = [
            'title'        => 'Nouvelle PJ de test',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA'
        ];

        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$originalAttachmentId = $responseBody->id;

        // ACT : Ajout de la version
        $args = [
            'title'        => 'Nouvelle PJ de test - v2',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA',
            'originId'     => self::$originalAttachmentId
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$versionAttachmentId = $responseBody->id;

        // ASSERT
        $this->assertIsInt(self::$versionAttachmentId);

        // Test passage du status de la pièce d'origine à OBS
        $resOrigin = AttachmentModel::getById(['id' => self::$originalAttachmentId, 'select' => ['status']]);
        $this->assertSame('OBS', $resOrigin['status']);

        // Test status de la version + lien avec la PJ d'origine
        $resVersion = AttachmentModel::getById(['id' => self::$versionAttachmentId, 'select' => ['status', 'origin_id']]);
        $this->assertSame('A_TRA', $resVersion['status']);
        $this->assertSame(self::$originalAttachmentId, $resVersion['origin_id']);
    }

    public function testAddNewVersionFromOriginVersionError()
    {
        $attachmentController = new AttachmentController();

        // ARRANGE
        /*
         * - Ajout d'une PJ
         * - Ajout d'une version
         */
        $fileContent = file_get_contents('test/unitTests/samples/test.txt');
        $encodedFile = base64_encode($fileContent);

        $args = [
            'title'        => 'Nouvelle PJ de test',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA'
        ];

        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$originalAttachmentId = $responseBody->id;

        $args = [
            'title'        => 'Nouvelle PJ de test',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA',
            'originId'     => self::$originalAttachmentId
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$versionAttachmentId = $responseBody->id;

        // ACT : Ajout de la version à partir de la version
        $args = [
            'title'        => 'Nouvelle PJ de test - V2',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA',
            'originId'     => self::$versionAttachmentId
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$versionAttachmentId = $responseBody->id;

        // ASSERT
        // Attendu : Impossible de rajouter une nouvelle version à une PJ qui est déjà une version d'une autre
        $this->assertSame('Body originId can not be a version, it must be the original version', $responseBody['errors']);
    }

    public function testAddNewVersionFromSignedPjError()
    {
        $attachmentController = new AttachmentController();

        // ARRANGE
        /*
         * - Ajout d'une PJ
         * - Ajout d'une réponse signée à partir de la PJ
         */
        $fileContent = file_get_contents('test/unitTests/samples/test.txt');
        $encodedFile = base64_encode($fileContent);

        $args = [
            'title'        => 'Nouvelle PJ de test',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA'
        ];

        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$originalAttachmentId = $responseBody->id;

        $args = [
            'title'        => 'Nouvelle PJ signée',
            'type'         => 'signed_response',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'TRA',
            'originId'     => self::$originalAttachmentId
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$signedVersionAttachmentId = $responseBody->id;

        // ACT : Ajout de la version à partir de la réponse signée
        $args = [
            'title'        => 'Nouvelle PJ signée - v2',
            'type'         => 'signed_response',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'TRA',
            'originId'     => self::$signedVersionAttachmentId
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());

        // ASSERT
        // Attendu : Impossible de rajouter une nouvelle version à une PJ dont le statut est signé (SIGN)
        $this->assertSame("Body originId has not an authorized status. Origin status is either 'SIGN' or 'FRZ'", $responseBody['errors']);
    }

    public function testAddNewSignedPjToAnotherPjWithVersionSuccess()
    {
        $attachmentController = new AttachmentController();

        // ARRANGE
        /*
         * - Ajout d'une PJ
         * - Ajout d'une version
         */
        $fileContent = file_get_contents('test/unitTests/samples/test.txt');
        $encodedFile = base64_encode($fileContent);

        $args = [
            'title'        => 'Nouvelle PJ de test',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA'
        ];

        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$originalAttachmentId = $responseBody->id;

        $args = [
            'title'        => 'Nouvelle PJ de test - v2',
            'type'         => 'response_project',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'A_TRA',
            'originId'     => self::$originalAttachmentId
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$versionAttachmentId = $responseBody->id;

        // ACT : Ajout d'une réponse signée à une PJ ayant une version
        $args = [
            'title'        => 'Nouvelle PJ signée',
            'type'         => 'signed_response',
            'resIdMaster'  => 100,
            'encodedFile'  => $encodedFile,
            'recipientId'  => 19,
            'format'       => 'txt',
            'status'       => 'TRA',
            'originId'     => self::$originalAttachmentId
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);

        $response = $attachmentController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        self::$signedVersionAttachmentId = $responseBody->id;

        // ASSERT
        $this->assertIsInt(self::$signedVersionAttachmentId);

        // Test si la PJ d'origine a toujours un status OBS
        $resOrigin = AttachmentModel::getById(['id' => self::$originalAttachmentId, 'select' => ['status']]);
        $this->assertSame('OBS', $resOrigin['status']);

        // Test si le status de la version est passé à SIGN
        $resVersion = AttachmentModel::getById(['id' => self::$versionAttachmentId, 'select' => ['status']]);
        $this->assertSame('SIGN', $resVersion['status']);

        // Test si la version signée est bien attachée à la version et non à la PJ d'origine
        $resSignedVersion = AttachmentModel::getById(['id' => self::$signedVersionAttachmentId, 'select' => ['status', 'origin_id', 'origin']]);
        $this->assertSame('TRA', $resSignedVersion['status']);
        $this->assertNull($resSignedVersion['origin_id']);
        $this->assertSame(self::$versionAttachmentId . ',res_attachments', $resSignedVersion['origin']);
    }
}
