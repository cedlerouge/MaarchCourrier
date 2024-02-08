<?php

namespace MaarchCourrier\Tests\app\signatureBook\Infrastructure\Stamp;

use MaarchCourrier\Tests\CourrierTestCase;
use SignatureBook\Infrastructure\Controllers\RetrieveUserStampsController;
use SrcCore\http\Response;
use User\controllers\UserController;

class RetrieveUserStampsControllerTest extends CourrierTestCase
{
    private function addUserSignature(int $userId): void
    {
        $userController = new UserController();
        $body = [
            'base64' => base64_encode(file_get_contents("install/samples/templates/2021/03/0001/0009_1477994073.jpg")),
            'label' => "signature-icon.jpg",
            'name' => "signature-icon.jpg",
        ];
        $fullRequest = $this->createRequestWithBody('POST', $body);
        $response = $userController->addSignature($fullRequest, new Response(), ['id' => $userId]);
        $responseBody = json_decode((string)$response->getBody());
    }

    public function testRetrieveUserStampsViaApi(): void
    {
        $userId = 19;
        $this->addUserSignature($userId);
        $retrieveUserStampsController = new RetrieveUserStampsController();
        $request = $this->createRequest('GET');

        $response = $retrieveUserStampsController->getUserSignatureStamps($request, new Response(), ['id' => $userId]);
        $userStamps = json_decode((string)$response->getBody(), true);

        $this->assertNotEmpty($userStamps);
        $this->assertIsArray($userStamps);
        $this->assertNotEmpty($userStamps[0]);
        $this->assertIsArray($userStamps[0]);
    }
}
