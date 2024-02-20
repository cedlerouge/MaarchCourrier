<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\SignatureBook\Domain\CurlRequest;

interface CurlServiceInterface
{
    public function call(CurlRequest $curlRequest): CurlRequest;
}
