<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief RetrieveProofFileController Controller
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure\Controller;

use MaarchCourrier\SignatureBook\Application\ProofFile\RetrieveProofFile;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurProofService;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\ResourceToSignRepository;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureServiceJsonConfigLoader;
use Slim\Psr7\Request;
use SrcCore\http\Response;

class RetrieveProofFileController
{
    public function getProofFile(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();

        $resourceToSignRepository = new ResourceToSignRepository();
        $maarchParapheurProofService = new MaarchParapheurProofService();
        $SignatureServiceConfigLoader = new SignatureServiceJsonConfigLoader();

        $retrieveProofFile = new RetrieveProofFile(
            $maarchParapheurProofService,
            $resourceToSignRepository,
            $SignatureServiceConfigLoader
        );

        $result = $retrieveProofFile->execute(isset($queryParams['isAttachment']));
        return $response->withJson([]);
    }
}