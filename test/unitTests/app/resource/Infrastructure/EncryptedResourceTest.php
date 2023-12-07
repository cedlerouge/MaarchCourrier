<?php

namespace MaarchCourrier\Tests\app\resource\Infrastructure;

use Docserver\controllers\DocserverController;
use MaarchCourrier\Tests\CourrierTestCase;
use Resource\controllers\ResController;
use SrcCore\http\Response;

class EncryptedResourceTest extends CourrierTestCase
{
    private static int $encryptedDocserverId;
    private static string $pathTemplate = '/tmp/unitTestMaarchCourrier/';
    private static string $pathEncryptedTemplate = '/tmp/unitTestMaarchCourrierEncrypted/';

    protected function setUp(): void
    {
        // The path should exist, if not create it
        if (!is_dir(self::$pathTemplate)) {
            mkdir(self::$pathTemplate);
        }
    }

    protected function tearDown(): void
    {
        if (isset(self::$encryptedDocserverId)) {
            $this->deleteEncryptedDocserverForMainResourceDocument(self::$encryptedDocserverId);
        }
        $this->unlockUnencryptedMainDocumentDocserver();
    }

    public function testCheckIfEncryptedResourceFileIsLocatedInAEncryptedDocserver(): void
    {
        // Arrange
        $this->lockUnencryptedMainDocumentDocserver();
        self::$encryptedDocserverId = $this->createEncryptedDocserverForMainResourceDocument();
        $resId = $this->createResource();

        // Act
        $resourceInfo = $this->getResourceFileInformation($resId);

        // Assert
        $this->assertIsArray($resourceInfo['information']);
        $this->assertNotEmpty($resourceInfo['information']);
        $this->assertSame('txt', $resourceInfo['information']['format']);
        $this->assertNotEmpty($resourceInfo['information']['docserverPathFile']);
        $this->assertIsString($resourceInfo['information']['docserverPathFile']);
        $this->assertStringContainsString(self::$pathEncryptedTemplate, $resourceInfo['information']['docserverPathFile']);
    }

    private function lockUnencryptedMainDocumentDocserver(): void
    {
        $args = [
            'docserver_id'      =>  'FASTHD_MAN',
            'device_label'      =>  'Dépôt documentaire de numérisation manuelle',
            'size_limit_number' =>  50000000000,
            'path_template'     =>  self::$pathTemplate,
            'is_readonly'       =>  true,
            'is_encrypted'      =>  false
        ];
        $fullRequest = $this->createRequestWithBody('PUT', $args);
        $docserverController = new DocserverController();

        // id 2 is docserver FASTHD_MAN in data_fr.sql
        $docserverController->update($fullRequest, new Response(),['id' => 2]);
    }

    private function createEncryptedDocserverForMainResourceDocument(): int
    {
        $args = [
            'docserver_id'      =>  'ENCRYPTED_FASTHD_MAN',
            'docserver_type_id' =>  'DOC',
            'device_label'      =>  'new encrypted docserver',
            'size_limit_number' =>  50000000000,
            'path_template'     =>  self::$pathEncryptedTemplate,
            'coll_id'           =>  'letterbox_coll',
            'is_readonly'       =>  false,
            'is_encrypted'      =>  true
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);
        $docserverController = new DocserverController();

        $response     = $docserverController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());

        return $responseBody->docserver;
    }

    private function createResource(): int
    {
        $previousLogin = $GLOBALS['login'];
        $this->connectAsUser('cchaplin');

        $fileContent = file_get_contents('test/unitTests/samples/test.txt');

        $body = [
            'modelId'          => 1,
            'status'           => 'NEW',
            'encodedFile'      => base64_encode($fileContent),
            'format'           => 'txt',
            'confidentiality'  => false,
            'documentDate'     => '2023-12-01 17:18:47',
            'arrivalDate'      => '2023-12-01 17:18:47',
            'processLimitDate' => '2033-12-01',
            'doctype'          => 102,
            'destination'      => 15,
            'initiator'        => 15,
            'subject'          => 'Breaking News : Superman is alive - PHP unit',
            'typist'           => 19,
            'priority'         => 'poiuytre1357nbvc',
            'senders'          => [['type' => 'contact', 'id' => 1], ['type' => 'user', 'id' => 21], ['type' => 'entity', 'id' => 1]],
        ];
        $fullRequest = $this->createRequestWithBody('POST', $body);
        $resController = new ResController();

        $response      = $resController->create($fullRequest, new Response());
        $responseBody  = json_decode((string)$response->getBody());

        $this->connectAsUser($previousLogin);

        return $responseBody->resId;
    }

    private function getResourceFileInformation(int $resId): array
    {
        $resController  = new ResController();
        $request        = $this->createRequest('GET');

        $response = $resController->getResourceFileInformation($request, new Response(), ['resId' => $resId]);
        return json_decode((string)$response->getBody(), true);
    }

    private function deleteEncryptedDocserverForMainResourceDocument(int $id): void
    {
        $docserverController = new DocserverController();
        $fullRequest = $this->createRequestWithBody('DELETE');
        $docserverController->delete($fullRequest, new Response(), ['id' => $id]);
    }

    private function unlockUnencryptedMainDocumentDocserver(): void
    {
        $docserverController = new DocserverController();
        $args = [
            'docserver_id'      =>  'FASTHD_MAN',
            'device_label'      =>  'Dépôt documentaire de numérisation manuelle',
            'size_limit_number' =>  50000000000,
            'path_template'     =>  self::$pathTemplate,
            'is_readonly'       =>  false,
            'is_encrypted'      =>  false
        ];
        $fullRequest = $this->createRequestWithBody('PUT', $args);

        // id 2 is docserver FASTHD_MAN in data_fr.sql
        $docserverController->update($fullRequest, new Response(), ['id' => 2]);
    }
}
