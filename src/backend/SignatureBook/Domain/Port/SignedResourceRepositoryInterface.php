<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\SignatureBook\Domain\SignedResource;

interface SignedResourceRepositoryInterface
{
    public function attachToOriginalDocument(SignedResource $signedResource): void;
}
