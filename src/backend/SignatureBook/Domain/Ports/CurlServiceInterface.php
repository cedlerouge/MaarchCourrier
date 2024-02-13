<?php

namespace MaarchCourrier\SignatureBook\Domain\Ports;

use MaarchCourrier\SignatureBook\Domain\CurlRequest;

interface CurlServiceInterface
{
    public function call(CurlRequest $curlRequest): CurlRequest;
}
