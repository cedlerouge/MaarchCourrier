<?php

namespace MaarchCourrier\Tests\Functional\SignatureBook\Webhook;

use Attachment\controllers\AttachmentController;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\StoreResourceProblem;
use MaarchCourrier\SignatureBook\Infrastructure\Controller\WebhookController;
use MaarchCourrier\Tests\CourrierTestCase;
use SignatureBook\controllers\SignatureBookController;
use SrcCore\http\Response;
use SrcCore\models\CoreConfigModel;

class WebhookControllerTest extends CourrierTestCase
{
    private array $body = [];
    private int $idDocParapheur = 0;
    private int $resIdCourrier = 0;
    private int $resIdMasterCourrier = 0;
    private string $state = 'VAL';
    private string $urlParapheur = '';

    protected function setUp(): void
    {
        $defaultConfigParapheur = CoreConfigModel::getXmlLoaded(['path' => 'modules/visa/xml/remoteSignatoryBooks.xml']);
        $this->urlParapheur = (string)$defaultConfigParapheur->signatoryBook->url;

        $this->state = 'VAL';
    }

    private function createBody(): void
    {
        $this->body = [
            'identifier'     => 'TDy3w2zAOM41M216',
            'signatureState' => [
                'error'       => '',
                'state'       => $this->state,
                'message'     => '',
                'updatedDate' => null,
            ],
            'payload'        => [
                'res_id'        => $this->resIdCourrier,
                'idParapheur'   => $this->idDocParapheur,
                'res_id_master' => $this->resIdMasterCourrier
            ],
            'retrieveDocUri' => $this->urlParapheur . 'rest/documents/' . $this->idDocParapheur . '/content?mode=base64&type=esign'
        ];
    }


    /*public function testCanFetchAndStoreSignedResource(): void
    {
        $this->connectAsUser('ppetit');


        $this->resIdCourrier = 75;
        $this->idDocParapheur = 13;

        $this->createBody();

        $webhookController = new WebhookController();
        $fullRequest = $this->createRequestWithBody('POST', $this->body);

        $response = $webhookController->fetchAndStoreSignedDocumentOnWebhookTrigger($fullRequest, new Response(), []);
    }*/

    private function unsignAttachement(int $resId): void
    {
        $signatureBook = new SignatureBookController();
        $request = $this->createRequest('PUT');

        $signatureBook->unsignAttachment($request, new Response(), ['id' => $resId]);
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws CurlRequestErrorProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws StoreResourceProblem
     */
    public function testCanFetchAndStoreSignedAttachment(): void
    {
        $this->connectAsUser('ppetit');

        $this->resIdCourrier = 75;
        $this->resIdMasterCourrier = 157;
        $this->idDocParapheur = 13;

        $this->createBody();

        $webhookController = new WebhookController();
        $fullRequest = $this->createRequestWithBody('POST', $this->body);

        $response = $webhookController->fetchAndStoreSignedDocumentOnWebhookTrigger($fullRequest, new Response(), []);
        $jsonResource = json_decode((string)$response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertIsInt($jsonResource['id']);

        $this->unsignAttachement($this->resIdCourrier);
    }

    public function testCannotFetchAndStoreSignedAttachmentIfResIdNotSet(): void
    {
        $this->connectAsUser('ppetit');

        $this->resIdMasterCourrier = 157;
        $this->idDocParapheur = 13;

        $this->createBody();

        $webhookController = new WebhookController();
        $fullRequest = $this->createRequestWithBody('POST', $this->body);

        $response = $webhookController->fetchAndStoreSignedDocumentOnWebhookTrigger($fullRequest, new Response(), []);
        $jsonResource = json_decode((string)$response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 400);
        $this->assertSame($jsonResource['errors'], 'res_id is not set in payload');
    }

    public function testCannotFetchAndStoreSignedAttachmentIfIdParapheurNotSet(): void
    {
        $this->connectAsUser('ppetit');

        $this->resIdMasterCourrier = 157;
        $this->resIdCourrier = 75;

        $this->createBody();

        $webhookController = new WebhookController();
        $fullRequest = $this->createRequestWithBody('POST', $this->body);

        $response = $webhookController->fetchAndStoreSignedDocumentOnWebhookTrigger($fullRequest, new Response(), []);
        $jsonResource = json_decode((string)$response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 400);
        $this->assertSame($jsonResource['errors'], 'idParapheur is not set in payload');
    }

    public function testCannotFetchAndStoreSignedAttachmentIfWebhookUriSet(): void
    {
        $this->connectAsUser('ppetit');

        $this->idDocParapheur = 13;
        $this->resIdMasterCourrier = 157;
        $this->resIdCourrier = 75;

        $this->createBody();

        $this->body['retrieveDocUri'] = null;

        $webhookController = new WebhookController();
        $fullRequest = $this->createRequestWithBody('POST', $this->body);

        $response = $webhookController->fetchAndStoreSignedDocumentOnWebhookTrigger($fullRequest, new Response(), []);
        $jsonResource = json_decode((string)$response->getBody(), true);

        $this->assertSame($response->getStatusCode(), 400);
        $this->assertSame($jsonResource['errors'], 'retrieveDocUri is not set');
    }
}
