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

class WebhookControllerTest extends CourrierTestCase
{
    private array $body = [];
    private int $idDocParapheur = 0;
    private int $resIdCourrier = 0;
    private int $resIdMasterCourrier = 0;
    private string $state = 'VAL';

    protected function setUp(): void
    {

    }

    protected function tearDown(): void
    {
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
            'retrieveDocUri' => 'http://10.1.5.12/maarch-parapheur-api/rest/documents/' . $this->idDocParapheur . '/content?mode=base64&type=esign'
        ];
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws CurlRequestErrorProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws StoreResourceProblem
     */
    public function testCanFetchAndStoreSignedResource(): void
    {
        $this->state = 'VAL';

        $this->resIdCourrier = 75;
        $this->idDocParapheur = 13;

        $this->createBody();

        $webhookController = new WebhookController();
        $fullRequest = $this->createRequestWithBody('POST', $this->body);

        $response = $webhookController->fetchAndStoreSignedDocumentOnWebhookTrigger($fullRequest, new Response(), []);
    }

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
        $this->state = 'VAL';

        $this->resIdCourrier = 75;
        $this->resIdMasterCourrier = 157;
        $this->idDocParapheur = 13;

        $this->createBody();

        $webhookController = new WebhookController();
        $fullRequest = $this->createRequestWithBody('POST', $this->body);

        $response = $webhookController->fetchAndStoreSignedDocumentOnWebhookTrigger($fullRequest, new Response(), []);
        $jsonResource = json_decode((string)$response->getBody(), true);

        $this->assertIsInt($jsonResource['id']);

        $this->unsignAttachement($this->resIdCourrier);
    }

    public function CannotFetchAndStoreSignedAttachmentIfResIdNotSet(): void
    {

    }

    public function CannotFetchAndStoreSignedAttachmentIfIdParapheurNotSet(): void
    {

    }

    public function CannotFetchAndStoreSignedAttachmentIfAttachmentResIdNotCorrespondingToResIdMaster(): void
    {

    }
}
