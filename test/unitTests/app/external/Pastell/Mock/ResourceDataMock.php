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
    /**
     * @param int $resId
     * @return array
     */
    public function getMainResourceData(int $resId): array
    {
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
    public function getAttachmentsData(int $resId): array
    {
        // TODO: Implement getAttachmentsData() method.
    }
}
