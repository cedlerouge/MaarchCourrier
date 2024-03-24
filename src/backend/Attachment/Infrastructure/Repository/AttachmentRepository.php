<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief AttachmentRepository class
 * @author dev@maarch.org
 */

declare(strict_types=1);

namespace MaarchCourrier\Attachment\Infrastructure\Repository;

use Attachment\models\AttachmentModel;
use Attachment\models\AttachmentTypeModel;
use MaarchCourrier\Attachment\Domain\Attachment;
use MaarchCourrier\Attachment\Domain\AttachmentType;
use MaarchCourrier\Core\Domain\Attachment\Port\AttachmentRepositoryInterface;
use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceInterface;
use MaarchCourrier\Core\Domain\User\Port\UserFactoryInterface;
use MaarchCourrier\DocumentStorage\Domain\Document;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    public function __construct(
        private readonly UserFactoryInterface $userFactory
    ) {
    }

    /**
     * @param MainResourceInterface $mainResource
     *
     * @return Attachment[]
     * @throws \Exception
     */
    public function getIncomingMailByMainResource(MainResourceInterface $mainResource): array
    {
        $attachmentTypeLabel = AttachmentTypeModel::getByTypeId(['typeId' => 'incoming_mail_attachment']);
        $attachmentTypeLabel = $attachmentTypeLabel['label'];

        $attachmentType = (new AttachmentType())
            ->setType('incoming_mail_attachment')
            ->setLabel($attachmentTypeLabel);

        $data = AttachmentModel::get([
            'select' => [
                'res_id', 'res_id_master', 'title', 'identifier', 'relation', 'attachment_type', 'format', 'typist'
            ],
            'where'  => ['res_id_master = ?', 'attachment_type = ?', "status not in ('DEL', 'TMP', 'OBS')"],
            'data'   => [$mainResource->getResId(), 'incoming_mail_attachment']
        ]);

        /** @var Attachment[] $attachments */
        $attachments = [];
        foreach ($data as $attachment) {
            //$typist = $this->userFactory->createUser([
            //    'id' => $attachment['typist']
            //]);

            $typist = $this->userFactory->createRetrieveUser()->getUserById($attachment['typist']);

            $attachments[] = (new Attachment())
                ->setResId($attachment['res_id'])
                ->setMainResource($mainResource)
                ->setTitle($attachment['title'])
                ->setChrono($attachment['identifier'])
                ->setTypist($typist)
                ->setRelation($attachment['relation'])
                ->setType($attachmentType);
        }

        return $attachments;
    }

    /**
     * @inheritDoc
     */
    public function getNonIncomingMailNotInSignatureBookByMainResource(MainResourceInterface $mainResource): array
    {
        $attachmentTypes = AttachmentTypeModel::get(['select' => ['type_id', 'label', 'icon', 'signable']]);
        $attachmentTypes = array_column($attachmentTypes, null, 'type_id');

        $orderBy = "CASE attachment_type WHEN 'response_project' THEN 1";
        $c = 2;
        foreach ($attachmentTypes as $value) {
            if ($value['signable'] && $value['type_id'] != 'response_project') {
                $orderBy .= " WHEN '{$value['type_id']}' THEN {$c}";
                ++$c;
            }
        }
        $orderBy .= " ELSE {$c} END, validation_date DESC NULLS LAST, creation_date DESC";

        foreach ($attachmentTypes as $type => $attachmentType) {
            $attachmentTypes[$type] = (new AttachmentType())
                ->setType($type)
                ->setLabel($attachmentType['label']);
        }

        $data = AttachmentModel::get([
            'select'    => [
                'res_id', 'res_id_master', 'title', 'identifier', 'relation', 'attachment_type',
                'format', 'typist'
            ],
            'where'     => [
                'res_id_master = ?', 'attachment_type != ?', "status not in ('DEL', 'OBS')", 'in_signature_book = TRUE'
            ],
            'data'      => [$mainResource->getResId(), 'incoming_mail_attachment'],
            'orderBy'   => [$orderBy]
        ]);

        /** @var Attachment[] $attachments */
        $attachments = [];
        foreach ($data as $attachment) {
            //$typist = $this->userFactory->createUser([
            //    'id' => $attachment['typist']
            //]);

            $typist = $this->userFactory->createRetrieveUser()->getUserById($attachment['typist']);

            $attachments[] = (new Attachment())
                ->setResId($attachment['res_id'])
                ->setMainResource($mainResource)
                ->setTitle($attachment['title'])
                ->setChrono($attachment['identifier'])
                ->setTypist($typist)
                ->setRelation($attachment['relation'])
                ->setType($attachmentTypes[$attachment['attachment_type']]);
        }

        return $attachments;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachmentsInSignatureBookByMainResource(MainResourceInterface $mainResource): array
    {
        $attachmentTypes = AttachmentTypeModel::get(['select' => ['type_id', 'label', 'signable']]);
        $attachmentTypes = array_column($attachmentTypes, null, 'type_id');

        foreach ($attachmentTypes as $type => $attachmentType) {
            $attachmentTypes[$type] = (new AttachmentType())
                ->setType($type)
                ->setLabel($attachmentType['label'])
                ->setSignable($attachmentType['signable']);
        }

        $data = AttachmentModel::get([
            'select' => [
                'res_id', 'res_id_master', 'title', 'identifier', 'relation', 'attachment_type', 'filename', 'format',
                'typist'
            ],
            'where'  => ['res_id_master = ?', 'in_signature_book = ?', "status not in ('DEL', 'TMP', 'OBS')"],
            'data'   => [$mainResource->getResId(), 'true']
        ]);

        /** @var Attachment[] $attachments */
        $attachments = [];
        foreach ($data as $attachment) {
            //$typist = $this->userFactory->createUser([
            //    'id' => $attachment['typist']
            //]);

            $typist = $this->userFactory->createRetrieveUser()->getUserById($attachment['typist']);

            $document = (new Document())
                ->setFileName($attachment['filename'])
                ->setFileExtension($attachment['format']);

            $attachments[] = (new Attachment())
                ->setResId($attachment['res_id'])
                ->setMainResource($mainResource)
                ->setTitle($attachment['title'])
                ->setChrono($attachment['identifier'] ?? '')
                ->setTypist($typist)
                ->setRelation($attachment['relation'])
                ->setType($attachmentTypes[$attachment['attachment_type']])
                ->setDocument($document);
        }

        return $attachments;
    }
}
