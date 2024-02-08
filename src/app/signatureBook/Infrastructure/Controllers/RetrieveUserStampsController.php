<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   User Signature
 * @author  dev@maarch.org
 */

namespace SignatureBook\Infrastructure\Controllers;

use SignatureBook\Application\Stamp\RetrieveUserStamps;
use SignatureBook\Infrastructure\Repository\SignatureServiceRepository;
use SignatureBook\Infrastructure\Repository\UserRepository;
use Slim\Psr7\Request;
use SrcCore\http\Response;
use Throwable;

class RetrieveUserStampsController
{
    public function getUserSignatureStamps(Request $request, Response $response, array $args): Response
    {
        $userRepository = new UserRepository();
        $signatureServiceRepository = new SignatureServiceRepository();

        try {
            $retrieveUserStamps = new RetrieveUserStamps($userRepository, $signatureServiceRepository);
            return $response->withJson($retrieveUserStamps->getUserSignatures($args['id']));
        } catch (Throwable $th) {
            return $response->withStatus($th->getCode())->withJson(['errors' => $th->getMessage()]);
        }
    }
}
