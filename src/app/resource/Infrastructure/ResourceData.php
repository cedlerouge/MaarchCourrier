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

use Convert\controllers\ConvertPdfController;
use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Resource\controllers\ResController;
use Resource\Domain\ResourceDataInterface;
use Resource\models\ResModel;
use SrcCore\models\TextFormatModel;

class ResourceData implements ResourceDataInterface
{
    /**
     * @param   int     $resId
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getMainResourceData(int $resId, array $select = ['*']): array
    {
        if ($resId <= 0) {
            return ['error' => "The 'resId' parameter must be greater than 0"];
        }

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
        if ($resId <= 0) {
            return ['error' => "The 'resId' parameter must be greater than 0"];
        }
        if ($version <= 0) {
            return ['error' => "The 'version' parameter must be greater than 0"];
        }

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
        if (empty($docserverId)) {
            return ['error' => "The 'docserverId' parameter can not be empty"];
        }

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
     * @param   int     $maxLength  Default value is 250 length
     * @return  string
     */
    public function formatFilename(string $name, int $maxLength = 250): string
    {
        return TextFormatModel::formatFilename(['filename' => $name, 'maxLength' => 250]);
    }

    /**
     * Return the converted pdf from resource
     * 
     * @param   int     $resId  Resource id
     * @param   string  $collId Resource type id : letterbox_coll or attachments_coll
     * @return  array
     */
    public function getConvertedPdfById(int $resId, string $collId): array
    {
        if ($resId <= 0) {
            return ['code' => 400, 'errors' => "The 'resId' parameter must be greater than 0"];
        }
        if (empty($collId) || ($collId !== 'letterbox_coll' && $collId !== 'attachments_coll')) {
            return ['code' => 400, 'errors' => "The 'collId' parameter can not be empty and should be 'letterbox_coll' or 'attachments_coll'"];
        }

        $convertedDocument = ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => $collId]);
        if (!empty($convertedDocument['errors'])) {
            return ['errors' => 'Conversion error : ' . $convertedDocument['errors']];
        }

        return $convertedDocument;
    }

    /**
     * @param   int     $resId      Resource id
     * @param   string  $type       Resource converted format
     * @param   int     $version    Resource version
     * @return  array
     */
    public function getResourceVersion(int $resId, string $type, int $version): array
    {
        if ($resId <= 0) {
            return ['error' => "The 'resId' parameter must be greater than 0"];
        }
        $checkThumbnailPageType = ctype_digit(str_replace('TNL', '', $type));
        if (empty($type) || (!in_array($type, $this::ADR_RESOURCE_TYPES) && !$checkThumbnailPageType)) {
            return ['error' => "The 'type' parameter should be : " . implode(', ', $this::ADR_RESOURCE_TYPES) . " or thumbnail page 'TNL*'"];
        }
        if ($version <= 0) {
            return ['error' => "The 'version' parameter must be greater than 0"];
        }

        $document = AdrModel::getDocuments([
            'select'    => ['id', 'docserver_id', 'path', 'filename', 'fingerprint'],
            'where'     => ['res_id = ?', 'type = ?', 'version = ?'],
            'data'      => [$resId, $type, $version]
        ]);
        return $document[0] ?? [];
    }

    /**
     * @param   int     $resId  Resource id
     * @param   string  $type   Resource converted format
     * @return  array
     */
    public function getLatestResourceVersion(int $resId, string $type): array
    {
        if ($resId <= 0) {
            return ['error' => "The 'resId' parameter must be greater than 0"];
        }
        if (empty($type) || !in_array($type, $this::ADR_RESOURCE_TYPES)) {
            return ['error' => "The 'type' parameter should be : " . implode(', ', $this::ADR_RESOURCE_TYPES)];
        }
        
        $document = AdrModel::getDocuments([
            'select'    => ['id', 'docserver_id', 'path', 'filename', 'fingerprint'],
            'where'     => ['res_id = ?', 'type = ?'],
            'data'      => [$resId, $type],
            'orderBy'   => ['version desc']
        ]);
        return $document[0] ?? [];
    }

    /**
     * Check if user has rights over the resource
     * 
     * @param   int     $resId      Resource id
     * @param   int     $userId     User id
     * @return  bool
     */
    public function hasRightByResId(int $resId, int $userId): bool
    {
        return ResController::hasRightByResId(['resId' => [$resId], 'userId' => $userId]);
    }
}
