<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\SignatureBook\Domain\SignedResource;

interface StoreSignedResourceServiceInterface
{
    public function storeResource(SignedResource $signedResource): array;
    public function storeAttachement(SignedResource $signedResource, array $attachment): int|array;
}
