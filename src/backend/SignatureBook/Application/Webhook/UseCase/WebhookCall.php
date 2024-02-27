<?php

namespace MaarchCourrier\SignatureBook\Application\Webhook\UseCase;

use MaarchCourrier\SignatureBook\Application\Webhook\RetrieveSignedResource;
use MaarchCourrier\SignatureBook\Application\Webhook\StoreSignedResource;
use MaarchCourrier\SignatureBook\Application\Webhook\WebhookValidation;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdMasterNotCorrespondingProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\StoreResourceProblem;
use MaarchCourrier\SignatureBook\Infrastructure\CurlService;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\ResourceToSignRepository;
use MaarchCourrier\SignatureBook\Infrastructure\StoreSignedResourceService;
use MaarchCourrier\User\Infrastructure\CurrentUserInformations;

class WebhookCall
{
    public function __construct(
        private readonly WebhookValidation $webhookValidation,
        private readonly RetrieveSignedResource $retrieveSignedResource,
        private readonly StoreSignedResource $storeSignedResource
    )
    {
    }

    /**
     * @param array $body
     * @return int
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurlRequestErrorProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws StoreResourceProblem
     */
    public function execute(array $body): int
    {
        // Validation
        $signedResource = $this->webhookValidation->validate($body);

        // Retrieve
        $signedResource = $this->retrieveSignedResource->retrieve($signedResource, $body['retrieveDocUri']);

        // Store
        return $this->storeSignedResource->store($signedResource);
    }
}
