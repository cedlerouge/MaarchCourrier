<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

declare(strict_types=1);

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\ResourceDataInterface;

class ResourceDataMock implements ResourceDataInterface
{
    public bool $resourceExist = true;

    public array $attachments = [];

    public array $attachmentTypes = [];

    /**
     * @param int $resId
     * @return array
     */
    public function getMainResourceData(int $resId): array
    {
        if (!$this->resourceExist) {
            return [];
        }

        $integrations = [
            'inShipping'      => false,
            'inSignatureBook' => true
        ];

        return [
            'res_id'       => 42,
            'subject'      => 'blabablblalba',
            'integrations' => json_encode($integrations),
            'external_id'  => ''
        ];
    }

    /**
     * @param int $resId
     * @return array
     */
    public function getIntegratedAttachmentsData(int $resId): array
    {
        return $this->attachments;
    }

    public function getAttachmentTypes(): array
    {
        return $this->attachmentTypes;
    }
}
