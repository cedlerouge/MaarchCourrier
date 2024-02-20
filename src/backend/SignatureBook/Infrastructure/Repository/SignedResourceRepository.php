<?php

namespace MaarchCourrier\SignatureBook\Infrastructure\Repository;

use MaarchCourrier\SignatureBook\Domain\Port\SignedResourceRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class SignedResourceRepository implements SignedResourceRepositoryInterface
{

    public function attachToOriginalDocument(SignedResource $signedResource): void
    {
        // TODO: Implement attachToOriginalDocument() method.
    }
}
