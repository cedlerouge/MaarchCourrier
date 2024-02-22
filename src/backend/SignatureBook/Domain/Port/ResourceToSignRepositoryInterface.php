<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

interface ResourceToSignRepositoryInterface
{
    public function getResourceInformations(int $resId): array;
    public function getAttachmentInformations(int $resId): array;
    public function createSignVersionForResource(int $resId, array $storeInformations): void;
    public function updateAttachementStatus(int $resId): void;
    public function isResourceSigned(int $resId): bool;
}
