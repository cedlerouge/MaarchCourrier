<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief  SignatureBookRepository class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure\Repository;

use Attachment\models\AttachmentModel;
use Attachment\models\AttachmentTypeModel;
use Convert\controllers\ConvertPdfController;
use Entity\models\ListInstanceModel;
use Exception;
use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookResource;
use Resource\Domain\Resource;
use SrcCore\models\DatabaseModel;

class SignatureBookRepository implements SignatureBookRepositoryInterface
{
    /**
     * @param Resource $resource
     *
     * @return SignatureBookResource[]
     * @throws Exception
     */
    public function getIncomingMainResourceAndAttachments(Resource $resource): array
    {
        $resourcesToSign = [];

        if (!empty($resource->getFilename()) && !empty($resource->getIntegrations()['inSignatureBook'])) {
            $isConverted = ConvertPdfController::canConvert(['extension' => $resource->getFormat()]);

            $resourceToSign = new SignatureBookResource();
            $resourceToSign->setResId($resource->getResId())
                ->setTitle($resource->getSubject())
                ->setChrono($resource->getAltIdentifier())
                ->setSignedResId(null)
                ->setResType(0)
                ->setType(_MAIN_DOCUMENT)
                ->setIsConverted($isConverted);
            $resourcesToSign[] = $resourceToSign;
        }

        $attachmentTypeId = AttachmentTypeModel::getByTypeId(['typeId' => 'incoming_mail_attachment']);
        $attachmentTypeId = $attachmentTypeId['id'];

        $incomingMailAttachments = AttachmentModel::get([
            'select' => ['res_id', 'title', 'identifier', 'relation', 'attachment_type', 'format'],
            'where'  => ['res_id_master = ?', 'attachment_type = ?', "status not in ('DEL', 'TMP', 'OBS')"],
            'data'   => [$resource->getResId(), 'incoming_mail_attachment']
        ]);

        foreach ($incomingMailAttachments as $value) {
            $isConverted = ConvertPdfController::canConvert(['extension' => $value['format']]);

            $resourceToSign = new SignatureBookResource();
            $resourceToSign->setResId($value['res_id'])
                ->setTitle($value['title'])
                ->setChrono($value['identifier'] ?? '')
                ->setSignedResId($value['relation'])
                ->setResType($attachmentTypeId)
                ->setType($value['attachment_type'])
                ->setIsConverted($isConverted);
            $resourcesToSign[] = $resourceToSign;
        }

        return $resourcesToSign;
    }

    /**
     * @param Resource $resource
     *
     * @return SignatureBookResource[]
     * @throws Exception
     */
    public function getAttachments(Resource $resource): array
    {
        $resourcesAttached = [];

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

        $attachmentTypeId = '(select id from attachment_types where type_id = res_attachments.attachment_type) ';
        $attachmentTypeId .= 'as attachment_type_id';
        $attachments = AttachmentModel::get([
            'select'    => ['res_id', 'title', 'identifier', 'relation', $attachmentTypeId, 'attachment_type', 'format'],
            'where'     => [
                'res_id_master = ?', 'attachment_type != ?', "status not in ('DEL', 'OBS')", 'in_signature_book = TRUE'
            ],
            'data'      => [$resource->getResId(), 'incoming_mail_attachment'],
            'orderBy'   => [$orderBy]
        ]);

        foreach ($attachments as $value) {
            $isConverted = ConvertPdfController::canConvert(['extension' => $value['format']]);

            $resourceAttached = new SignatureBookResource();
            $resourceAttached->setResId($value['res_id'])
                ->setTitle($value['title'])
                ->setChrono($value['identifier'] ?? '')
                ->setSignedResId($value['relation'])
                ->setResType($value['attachment_type_id'])
                ->setType($value['attachment_type'])
                ->setIsConverted($isConverted);
            $resourcesAttached[] = $resourceAttached;
        }


        // if main resource is not integrated to signatureBook, then add to attachments
        if (!empty($resource->getFilename()) && empty($resource->getIntegrations()['inSignatureBook'])) {
            $isConverted = ConvertPdfController::canConvert(['extension' => $resource->getFormat()]);

            $resourceAttached = new SignatureBookResource();
            $resourceAttached->setResId($resource->getResId())
                ->setTitle($resource->getSubject())
                ->setChrono($resource->getAltIdentifier())
                ->setSignedResId(null)
                ->setResType(0)
                ->setType(_MAIN_DOCUMENT)
                ->setIsConverted($isConverted);
            $resourcesAttached[] = $resourceAttached;
        }

        return $resourcesAttached;
    }

    /**
     * @param Resource $resource
     * @param CurrentUserInterface $currentUser
     *
     * @return bool
     * @throws Exception
     */
    public function canUpdateResourcesInSignatureBook(
        Resource $resource,
        CurrentUserInterface $currentUser
    ): bool {

        $check = DatabaseModel::select([
            'select'    => ['true'],
            'table'     => ['groupbasket gb', 'usergroups ug', 'usergroup_content uc'],
            'left_join' => ['gb.group_id = ug.group_id', 'ug.id = uc.group_id'],
            'where'     => ['uc.user_id = ?', 'gb.list_event = ?', "gb.list_event_data->>'canUpdateDocuments' = ?"],
            'data'      => [$currentUser->getCurrentUserId(), 'signatureBookAction', 'true']
        ]);

        if (!empty($check)) {
            return true;
        }

        $redirectCheck = DatabaseModel::select([
            'select'    => ['true'],
            'table'      => ['groupbasket gb', 'usergroups ug', 'usergroup_content uc', 'redirected_baskets rb'],
            'left_join' => ['gb.group_id = ug.group_id', 'ug.id = uc.group_id', 'ug.id = rb.group_id '],
            'where'     => [
                'uc.user_id = rb.owner_user_id',
                'rb.actual_user_id = ?',
                'gb.list_event = ?',
                "gb.list_event_data->>'canUpdateDocuments' = ?"
            ],
            'data'      => [$currentUser->getCurrentUserId(), 'signatureBookAction', 'true']
        ]);

        if (!empty($redirectCheck)) {
            return true;
        }

        return false;
    }

    /**
     * @param Resource $resource
     *
     * @return bool
     */
    public function doesMainResourceHasActiveWorkflow(Resource $resource): bool
    {
        $listInstances = ListInstanceModel::get([
            'select'    => ['COUNT(*)'],
            'where'     => ['res_id = ?', 'item_mode in (?)', 'process_date IS NULL'],
            'data'      => [$resource->getResId(), ['visa', 'sign']]
        ]);

        return ((int)$listInstances[0]['count'] > 0);
    }

    /**
     * @param Resource $resource
     *
     * @return ?int
     */
    public function getWorkflowUserIdByCurrentStep(Resource $resource): ?int
    {
        $currentStep = ListInstanceModel::getCurrentStepByResId(['resId' => $resource->getResId()]);
        return $currentStep['item_id'] ?? null;
    }
}
