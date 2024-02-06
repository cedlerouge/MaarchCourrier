<?php

namespace MaarchCourrier\Tests\app\signatureBook\Infrastructure\Stamp;

use MaarchCourrier\Tests\CourrierTestCase;
use SignatureBook\Infrastructure\Controllers\RetrieveUserStampsController;
use SrcCore\http\Response;

class RetrieveUserStampsControllerTest extends CourrierTestCase
{
    public function testRetrieveUserStampsViaApi(): void
    {
        $retrieveUserStampsController = new RetrieveUserStampsController();
        $request = $this->createRequest('GET');

        $response = $retrieveUserStampsController->getUserSignatureStamps($request, new Response(), ['id' => 19]);
        $userStamps = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($userStamps);
        $this->assertEmpty($userStamps);
    }
}
