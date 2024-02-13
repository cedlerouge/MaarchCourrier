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

namespace MaarchCourrier\SignatureBook\Infrastructure\Controllers;

use MaarchCourrier\SignatureBook\Application\Webhook\RetrieveSignedResource;
use MaarchCourrier\SignatureBook\Infrastructure\CurlService;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\SignedResourceRepository;
use Slim\Psr7\Request;
use SrcCore\http\Response;

class WebhookController
{
    public function fetchSignedDocumentOnWebhookTrigger(Request $request, Response $response, array $args): Response
    {
        $signedResourceRepository = new SignedResourceRepository();
        $curlService = new CurlService();

        $retrieveSignedResource = new RetrieveSignedResource($signedResourceRepository, $curlService);
    }
}
