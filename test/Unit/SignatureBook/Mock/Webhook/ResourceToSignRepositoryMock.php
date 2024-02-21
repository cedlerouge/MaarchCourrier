<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook;

use MaarchCourrier\SignatureBook\Domain\Port\ResourceToSignRepositoryInterface;

class ResourceToSignRepositoryMock implements ResourceToSignRepositoryInterface
{
    private bool $signedVersionCreate = false;
    private bool $attachmentUpdated = false;

    public function getResourceInformations(int $resId): array
    {
        return [
            'version' => 1
        ];
    }

    public function getAttachmentInformations(int $resId): array
    {
        return [
            'res_id_master'  => 100,
            'title'          => 'PDF_Reponse_blocsignature',
            'typist'         => 19,
            'identifier'     => 'MAARCH/2024D/1000',
            'recipient_id'   => 6,
            'recipient_type' => 'contact',
            'format'         => 'pdf'
        ];
    }

    public function createSignVersionForResource(int $resId, array $storeInformations): void
    {
        $this->signedVersionCreate = true;
    }

    public function updateAttachementStatus(int $resId): void
    {
        $this->attachmentUpdated = true;
    }
}
