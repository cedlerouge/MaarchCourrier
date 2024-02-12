<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve User Stamps Controller
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure\Controllers;

use MaarchCourrier\SignatureBook\Application\Stamp\RetrieveUserStamps;
use MaarchCourrier\SignatureBook\Domain\Problem\AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\SignatureRepository;
use MaarchCourrier\User\Domain\Problems\UserDoesNotExistProblem;
use MaarchCourrier\User\Infrastructure\Repository\UserRepository;
use Slim\Psr7\Request;
use SrcCore\http\Response;

class RetrieveUserStampsController
{
    /**
     * @throws AccessDeniedYouDoNotHavePermissionToAccessOtherUsersSignaturesProblem
     * @throws UserDoesNotExistProblem
     */
    public function getUserSignatureStamps(Request $request, Response $response, array $args): Response
    {
        $userRepository = new UserRepository();
        $signatureServiceRepository = new SignatureRepository();

        $retrieveUserStamps = new RetrieveUserStamps($userRepository, $signatureServiceRepository);
        return $response->withJson($retrieveUserStamps->getUserSignatures($args['id']));
    }
}
