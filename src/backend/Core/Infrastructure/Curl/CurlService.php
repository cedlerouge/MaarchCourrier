<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief CurlService class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Core\Infrastructure\Curl;

use MaarchCourrier\Core\Domain\Curl\CurlRequest;
use MaarchCourrier\Core\Domain\Curl\CurlResponse;
use MaarchCourrier\SignatureBook\Domain\Port\CurlServiceInterface;
use SrcCore\models\CurlModel;

class CurlService implements CurlServiceInterface
{
    public function call(CurlRequest $curlRequest): CurlRequest
    {
        $response = CurlModel::exec([
            'url'        => $curlRequest->getUrl(),
            'method'     => $curlRequest->getMethod(),
            'bearerAuth' => ['token' => $curlRequest->getAuthBearer()],
            'header'     => [
                'content-type: application/json',
                'Accept: application/json'
            ],
            'body'       => http_build_query($curlRequest->getBody())
        ]);

        $curlResponse = new CurlResponse($response['code'], $response['response']);
        $curlRequest->setCurlResponse($curlResponse);

        return $curlRequest;
    }
}
