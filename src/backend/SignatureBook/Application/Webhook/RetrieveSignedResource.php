<?php

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use MaarchCourrier\SignatureBook\Domain\Ports\CurlServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Ports\SignedResourceRepositoryInterface;

class RetrieveSignedResource
{
    public function __construct(
        private readonly SignedResourceRepositoryInterface $signedResourceRepository,
        private readonly CurlServiceInterface $curlService
    ) {
    }
}
