<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve Signature Book Config Controller
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure\Controller;

use MaarchCourrier\SignatureBook\Application\Config\RetrieveConfig;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureBookConfigLoader;
use Slim\Psr7\Request;
use SrcCore\http\Response;

class RetrieveConfigController
{
    public function getConfig(Request $request, Response $response): Response
    {
        $retrieveConfig = new RetrieveConfig(new SignatureBookConfigLoader());
        return $response->withJson($retrieveConfig->getConfig());
    }
}
