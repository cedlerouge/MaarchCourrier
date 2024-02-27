<?php

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use MaarchCourrier\SignatureBook\Domain\Port\ResourceToSignRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\Port\StoreSignedResourceServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\StoreResourceProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class StoreSignedResource
{
    public function __construct(
        private readonly ResourceToSignRepositoryInterface $resourceToSignRepository,
        private readonly StoreSignedResourceServiceInterface $storeSignedResourceService
    )
    {
    }

    public function store(SignedResource $signedResource): int
    {
        if ($signedResource->getResIdMaster() !== null) { //pour les PJ
            $attachment = $this->resourceToSignRepository->getAttachmentInformations($signedResource->getResIdSigned());
            $id = $this->storeSignedResourceService->storeAttachement($signedResource, $attachment);
            $this->resourceToSignRepository->updateAttachementStatus($signedResource->getResIdSigned());
        } else { //pour les resources
            $storeResource = $this->storeSignedResourceService->storeResource($signedResource);
            if (!empty($storeResource['errors'])) {
                throw new StoreResourceProblem($storeResource['errors']);
            } else {
                $this->resourceToSignRepository->createSignVersionForResource(
                    $signedResource->getResIdSigned(),
                    $storeResource
                );
                $id = $signedResource->getResIdSigned();
            }
        }

        return $id;
    }
}
