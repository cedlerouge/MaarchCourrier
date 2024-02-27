<?php

namespace MaarchCourrier\SignatureBook\Infrastructure;

use MaarchCourrier\SignatureBook\Domain\CurlRequest;
use MaarchCourrier\SignatureBook\Domain\CurlResponse;
use MaarchCourrier\SignatureBook\Domain\Port\CurlServiceInterface;
use SrcCore\models\CurlModel;

class CurlService implements CurlServiceInterface
{
    public function call(CurlRequest $curlRequest): CurlRequest
    {
        $response = CurlModel::exec([
            'url'    => $curlRequest->getUrl(),
            'method' => $curlRequest->getMethod(),
            'bearerAuth'     => ['token' => $curlRequest->getAuthBearer()],
            'header' => [
                'content-type: application/json',
                'Accept: application/json'
            ],
            'body'   => http_build_query($curlRequest->getBody())
        ]);

        $curlResponse = new CurlResponse($response['code'], $response['response']);
        $curlRequest->setCurlResponse($curlResponse);

        return $curlRequest;
    }
}
