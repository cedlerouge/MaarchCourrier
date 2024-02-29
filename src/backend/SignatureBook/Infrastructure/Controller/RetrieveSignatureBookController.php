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

namespace MaarchCourrier\SignatureBook\Infrastructure\Controller;

use SignatureBook\controllers\SignatureBookController;
use Slim\Psr7\Request;
use SrcCore\http\Response;
use SrcCore\models\CoreConfigModel;

class RetrieveSignatureBookController
{
    public function getSignatureBook(Request $request, Response $response, array $args): Response
    {
        #region Todo : refacto when SignatureBookConfigRepository is ready
        $config = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);
        $isEnable = $config['config']['newInternalParaph'] ?? false;
        #endregion

        if (!$isEnable) {
            $signatureBookController = new SignatureBookController();
            return $signatureBookController->getSignatureBook($request, $response, $args);
        }

        return $response->withJson(['JL' => true]);
    }
}
