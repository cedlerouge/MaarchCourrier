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

namespace Resource\Infrastructure;

use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Resource\Domain\ResourceDataInterface;
use Resource\models\ResModel;
use SrcCore\models\TextFormatModel;

class ResourceDataDatabase implements ResourceDataInterface
{
    /**
     * @param   int     $resId
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getMainResourceData(int $resId, array $select = ['*']): array
    {
        return ResModel::getById([
            'resId'  => $resId,
            'select' => empty($select) ? ['*'] : $select
        ]);
    }

    /**
     * @param   int     $resId
     * @param   int     $version
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getSignResourceData(int $resId, int $version, array $select = ['*']): array
    {
        return AdrModel::getDocuments([
            'select' => empty($select) ? ['*'] : $select,
            'where'  => ['res_id = ?', 'type = ?', 'version = ?'],
            'data'   => [$resId, 'SIGN', $version],
            'limit'  => 1
        ]);
    }

    /**
     * @param   int     $resId
     * @param   int     $version
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getDocserverDataByDocserverId(string $docserverId, array $select = ['*']): array
    {
        return DocserverModel::getByDocserverId([
            'docserverId' => $docserverId, 
            'select' => empty($select) ? ['*'] : $select
        ]);
    }

    /**
     * Update resource fingerprint
     * 
     * @param   int     $resId
     * @param   string  $fingerprint
     * @return  void
     */
    public function updateFingerprint(int $resId, string $fingerprint): void
    {
        ResModel::update(['set' => ['fingerprint' => $fingerprint], 'where' => ['res_id = ?'], 'data' => [$resId]]);
    }

    /**
     * @param   string  $name
     * @return  string
     */
    public function formatFilename(string $name): string
    {
        return TextFormatModel::formatFilename(['filename' => $name, 'maxLength' => 250]);
    }
}
