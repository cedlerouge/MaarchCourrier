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
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurProofService;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\ResourceToSignRepository;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureServiceJsonConfigLoader;
use MaarchCourrier\User\Infrastructure\CurrentUserInformations;
use Slim\Psr7\Request;
use SrcCore\http\Response;

class RetrieveProofFileController
{
    /**
     * @param  Request  $request
     * @param  Response  $response
     * @param  array  $args
     * @return Response
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function getProofFile(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();

        $currentUserInformations = new CurrentUserInformations();
        $resourceToSignRepository = new ResourceToSignRepository();
        $maarchParapheurProofService = new MaarchParapheurProofService();
        $SignatureServiceConfigLoader = new SignatureServiceJsonConfigLoader();

        $retrieveProofFile = new RetrieveProofFile(
            $currentUserInformations,
            $maarchParapheurProofService,
            $resourceToSignRepository,
            $SignatureServiceConfigLoader
        );

        $result = $retrieveProofFile->execute($args['resId'], isset($queryParams['isAttachment']));

        $response->write(base64_decode($result['encodedProofDocument']));
        $response = $response->withAddedHeader(
            'Content-Disposition',
            "inline; filename=maarch_history_proof." . $result['format']
        );

        return $response->withHeader('Content-Type', 'application/zip');
    }
}
