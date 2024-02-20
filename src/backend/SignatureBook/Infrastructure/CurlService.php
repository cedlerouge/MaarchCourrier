<?php

namespace MaarchCourrier\SignatureBook\Infrastructure;

use MaarchCourrier\SignatureBook\Domain\CurlRequest;
use MaarchCourrier\SignatureBook\Domain\Ports\CurlServiceInterface;
use SrcCore\models\CurlModel;

class CurlService implements CurlServiceInterface
{
    public function call(CurlRequest $curlRequest): CurlRequest
    {
        echo print_r($curlRequest,true);
        $curlResponse = CurlModel::exec([
            'url'    => $curlRequest->getUrl(),
            'method' => $curlRequest->getMethod(),
            'bearerAuth'     => ['token' => $curlRequest->getAuthBearer()],
            'header' => [
                'content-type: application/json',
                'Accept: application/json'
            ],
            'body'   => http_build_query($curlRequest->getBody())
        ]);

        $curlRequest->setHttpCode($curlResponse['code']);
        $curlRequest->setContentReturn($curlResponse['response']);

        return $curlRequest;
    }
}
