<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Webhook Controller
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure\Controller;

use MaarchCourrier\SignatureBook\Application\Webhook\RetrieveSignedResource;
use MaarchCourrier\SignatureBook\Application\Webhook\StoreSignedResource;
use MaarchCourrier\SignatureBook\Application\Webhook\UseCase\WebhookCall;
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
use Slim\Psr7\Request;
use SrcCore\http\Response;

class WebhookController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws AttachmentOutOfPerimeterProblem
     * @throws CurlRequestErrorProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws StoreResourceProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     */
    public function fetchAndStoreSignedDocumentOnWebhookTrigger(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();

        //Initialisation
        $currentUserInformations = new CurrentUserInformations();
        $curlService = new CurlService();
        $resourceToSignRepository = new ResourceToSignRepository();
        $storeSignedResourceService = new StoreSignedResourceService();

        $webhookValidation = new WebhookValidation($resourceToSignRepository);
        $retrieveSignedResource = new RetrieveSignedResource($currentUserInformations, $curlService);
        $storeSignedResource = new StoreSignedResource($resourceToSignRepository, $storeSignedResourceService);

        $webhookCall = new WebhookCall($webhookValidation, $retrieveSignedResource, $storeSignedResource);
        $id = $webhookCall->execute($body);
        return $response->withJson(['id' => $id]);
    }
}
