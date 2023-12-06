<?php

namespace MaarchCourrier\Tests\app\resource\Infrastructure;

use Docserver\controllers\DocserverController;
use MaarchCourrier\Tests\CourrierTestCase;
use Resource\controllers\ResController;
use SrcCore\http\Response;
use User\models\UserModel;

class EncryptedResourceTest extends CourrierTestCase
{
    private static $docId = null;
    private static $docserverId = 2;
    private static $encryptedDocserverId = null;
    private static $pathTemplate = '/tmp/unitTestMaarchCourrier/';
    private static $pathEncryptedTemplate = '/tmp/unitTestMaarchCourrierEncrypted/';

    public function setUp(): void
    {
    }

    public function testSetIsReadOnlyToTrueForMainDocOfUnencryptedDocserver()
    {
        // Arrange
        // The path should exist, if not create it
        if (!is_dir(self::$pathTemplate)) {
            mkdir(self::$pathTemplate);
        }
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

        // Act
        $response     = $docserverController->update($fullRequest, new Response(),['id' => self::$docserverId]);
        $responseBody = json_decode((string)$response->getBody());

        // Assert
        $this->assertIsInt($responseBody->docserver->id);
    }

    public function testCreateEncryptedDocserverForMainDoc()
    {
        // Arrange
        $args = [
            'docserver_id'      =>  'ENCRYPTED_FASTHD_MAN',
            'docserver_type_id' =>  'DOC',
            'device_label'      =>  'new encrypted docserver',
            'size_limit_number' =>  50000000000,
            'path_template'     =>  self::$pathEncryptedTemplate,
            'coll_id'           =>  'letterbox_coll',
            'is_encrypted'      =>  true
        ];
        $fullRequest = $this->createRequestWithBody('POST', $args);
        $docserverController = new DocserverController();

        // Act
        if (!is_dir(self::$pathEncryptedTemplate)) {
            mkdir(self::$pathEncryptedTemplate);
        }
        $response     = $docserverController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());

        // Assert
        self::$encryptedDocserverId = $responseBody->docserver;
        $this->assertIsInt(self::$encryptedDocserverId);
    }

    public function testCreateMainDoc()
    {
        // Arrange
        $GLOBALS['login'] = 'cchaplin';
        $userInfo = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        // Act
        $response      = $resController->create($fullRequest, new Response());
        $responseBody  = json_decode((string)$response->getBody());

        // Assert
        self::$docId = $responseBody->resId;
        $this->assertIsInt(self::$docId);
    }

    public function testCheckIfMainDocIsStoredInEncryptedDocserver()
    {
        // Arrange
        $resController  = new ResController();
        $request        = $this->createRequest('GET');

        // Act
        $response       = $resController->getResourceFileInformation($request, new Response(), ['resId' => self::$docId]);
        $responseBody   = json_decode((string)$response->getBody(), true);

        // Assert
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($responseBody['information']);
        $this->assertNotEmpty($responseBody['information']);
        $this->assertSame('txt', $responseBody['information']['format']);
        $this->assertNotEmpty($responseBody['information']['docserverPathFile']);
        $this->assertIsString($responseBody['information']['docserverPathFile']);
        $this->assertStringContainsString(self::$pathEncryptedTemplate, $responseBody['information']['docserverPathFile']);
    }

    public function testDeleteEncryptedMainDocDocserver()
    {
        // Arrange
        $docserverController = new DocserverController();
        $fullRequest = $this->createRequestWithBody('DELETE');

        // Act
        $response     = $docserverController->delete($fullRequest, new Response(), ['id' => self::$encryptedDocserverId]);
        $responseBody = json_decode((string)$response->getBody());

        // Assert
        $this->assertIsString($responseBody->success);
        $this->assertSame('success', $responseBody->success);
    }

    public function testSetBackReadOnlyToFalseForUnencryptedMainDocDocserver()
    {
        // Arrange
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

        // Act
        $response     = $docserverController->update($fullRequest, new Response(), ['id' => self::$docserverId]);
        $responseBody = json_decode((string)$response->getBody());

        // Assert
        $this->assertIsInt($responseBody->docserver->id);
        $this->assertSame(false, $responseBody->docserver->is_readonly);
    }
}
