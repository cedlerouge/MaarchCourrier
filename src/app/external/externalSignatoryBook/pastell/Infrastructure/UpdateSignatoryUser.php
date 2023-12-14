<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief update signatory pastell
 * @author dev@maarch.org
 */

namespace ExternalSignatoryBook\pastell\Infrastructure;

use Attachment\models\AttachmentModel;
use ExternalSignatoryBook\pastell\Domain\UpdateSignatoryUserInterface;
use Resource\models\ResModel;

class UpdateSignatoryUser implements UpdateSignatoryUserInterface
{
    /**
     * @param int $resId
     * @param string $type
     * @param string $signatoryUser
     * @return void
     */
    public function updateDocumentExternalStateSignatoryUser(int $resId, string $type, string $signatoryUser): void
    {
        if ($type == 'resource') {
            ResModel::update([
                'where'   => ['res_id = ?'],
                'data'    => [$resId],
                'postSet' => [
                    'external_state' => "jsonb_set(external_state::jsonb, '{signatoryUser}', '\"$signatoryUser\"')"
                ]
            ]);
        } else {
            AttachmentModel::update([
                'where'   => ['res_id = ?'],
                'data'    => [$resId],
                'postSet' => [
                    'external_state' => "jsonb_set(external_state::jsonb, '{signatoryUser}', '\"$signatoryUser\"')"
                ]
            ]);
        }
    }
}
