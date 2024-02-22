<?php

namespace MaarchCourrier\SignatureBook\Infrastructure\Repository;

use Attachment\models\AttachmentModel;
use Convert\models\AdrModel;
use MaarchCourrier\SignatureBook\Domain\Port\ResourceToSignRepositoryInterface;
use Resource\models\ResModel;

class ResourceToSignRepository implements ResourceToSignRepositoryInterface
{
    /**
     * @throws \Exception
     */
    public function getResourceInformations(int $resId): array
    {
        return ResModel::getById(['resId' => $resId, 'select' => ['version']]);
    }

    /**
     * @throws \Exception
     */
    public function getAttachmentInformations(int $resId): array
    {
        return AttachmentModel::getById([
            'id' => $resId,
            'select' => ['res_id_master', 'title', 'typist', 'identifier', 'recipient_id', 'recipient_type', 'format']
        ]);
    }

    /**
     * @throws \Exception
     */
    public function createSignVersionForResource(int $resId, array $storeInformations): void
    {
        $infosResource = $this->getResourceInformations($resId);

        AdrModel::createDocumentAdr([
            'resId'         => $resId,
            'type'          => 'SIGN',
            'docserverId'   => $storeInformations['docserver_id'],
            'path'          => $storeInformations['directory'],
            'filename'      => $storeInformations['file_destination_name'],
            'version'       => $infosResource['version'],
            'fingerprint'   => $storeInformations['fingerPrint']
        ]);

        AdrModel::deleteDocumentAdr([
            'where' => ['res_id = ?', 'type = ?', 'version = ?'],
            'data' => [$resId, 'TNL', $infosResource['version']]
        ]);
    }

    /**
     * @throws \Exception
     */
    public function updateAttachementStatus(int $resId): void
    {
        AttachmentModel::update([
            'set'   => ['status' => 'SIGN'],
            'where' => ['res_id = ?'],
            'data'  => [$resId]
        ]);
    }

    public function isResourceSigned(int $resId): bool
    {
        $signedDocument = AdrModel::getDocuments([
            'select'  => ['id'],
            'where'   => ['res_id = ?', 'type = ?'],
            'data'    => [$resId, ['PDF', 'SIGN']],
            'limit'   => 1
        ]);

        return (!empty($signedDocument));
    }
}
