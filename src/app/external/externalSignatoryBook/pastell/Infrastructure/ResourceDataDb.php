<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource data DB
 * @author dev@maarch.org
 */

declare(strict_types=1);

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Domain\ResourceDataInterface;
use Resource\models\ResModel;

class ResourceDataDb implements ResourceDataInterface
{
    /**
     * @param int $resId
     * @return array
     */
    public function getMainResourceData(int $resId): array
    {
        return ResModel::get([
            'select' => ['res_id', 'path', 'filename', 'docserver_id', 'format', 'category_id', 'external_id', 'integrations', 'subject'],
            'where'  => ['res_id = ?'],
            'data'   => [$resId]
        ]);
    }
}
