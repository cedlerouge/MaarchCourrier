<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve Signature Book Controller
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure\Controller;

use Exception;
use MaarchCourrier\Authorization\Domain\Problem\MainResourceOutOfPerimeterProblem;
use MaarchCourrier\Authorization\Infrastructure\AccessControlService;
use MaarchCourrier\Authorization\Infrastructure\MainResourceAccessControlService;
use MaarchCourrier\Core\Domain\MainResource\Problem\ResourceDoesNotExistProblem;
use MaarchCourrier\SignatureBook\Application\RetrieveSignatureBook;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\SignatureBookRepository;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureBookConfigLoader;
use MaarchCourrier\User\Infrastructure\CurrentUserInformations;
use Resource\Infrastructure\ResourceData;
use SignatureBook\controllers\SignatureBookController;
use Slim\Psr7\Request;
use SrcCore\http\Response;

class RetrieveSignatureBookController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws MainResourceOutOfPerimeterProblem
     * @throws ResourceDoesNotExistProblem
     * @throws Exception
     */
    public function getSignatureBook(Request $request, Response $response, array $args): Response
    {
        $confLoader = new SignatureBookConfigLoader();
        $isEnable = $confLoader->getConfig()->isNewInternalParaph();

        if (!$isEnable) {
            $signatureBookController = new SignatureBookController();
            return $signatureBookController->getSignatureBook($request, $response, $args);
        }

        $retrieve = new RetrieveSignatureBook(
            new CurrentUserInformations(),
            new AccessControlService(),
            new MainResourceAccessControlService(),
            new ResourceData(),
            new SignatureBookRepository()
        );
        return $response->withJson($retrieve->getSignatureBook($args['resId']));
    }
}
