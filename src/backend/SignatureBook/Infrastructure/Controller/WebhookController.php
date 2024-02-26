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
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
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
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws StoreResourceProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws ResourceAlreadySignProblem
     */
    public function fetchAndStoreSignedDocumentOnWebhookTrigger(Request $request, Response $response, array $args): Response
    {
        $resourceToSignRepository = new ResourceToSignRepository();
        $storeSignedResourceService = new StoreSignedResourceService();
        $currentUserInformations = new CurrentUserInformations();

        $body = $request->getParsedBody();
        $curlService = new CurlService();

        $retrieveSignedResource = new RetrieveSignedResource($currentUserInformations, $resourceToSignRepository, $storeSignedResourceService, $curlService);
        $signedResource = $retrieveSignedResource->retrieve($body);

        $id = $retrieveSignedResource->store($signedResource);

        return $response->withJson(['id' => $id]);
    }
}
