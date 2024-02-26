<?php

namespace MaarchCourrier\SignatureBook\Infrastructure\Controller;

use MaarchCourrier\SignatureBook\Application\Config\RetrieveConfig;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\SignatureBookConfigRepository;
use Slim\Psr7\Request;
use SrcCore\http\Response;

class RetrieveConfigController
{
    public function getConfig(Request $request, Response $response): Response
    {
        $retrieveConfig = new RetrieveConfig(new SignatureBookConfigRepository());
        return $response->withJson($retrieveConfig->getConfig());
    }
}
