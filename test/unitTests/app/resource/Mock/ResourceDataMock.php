<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Resource\Domain\ResourceDataInterface;
use Resource\Domain\ResourceDataType;
use SrcCore\models\TextFormatModel;

class ResourceDataMock implements ResourceDataInterface
{
    public bool $doesResourceExist = true;
    public bool $doesResourceFileExistInDatabase = true;
    public bool $doesResourceDocserverExist = true;
    public bool $doesResourceVersionExist = true;
    public bool $returnResourceWithoutFile = false;
    public bool $doesUserHasRights = true;

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
        if (!$this->doesResourceExist) {
            return [];
        }

        if (!$this->doesResourceFileExistInDatabase) {
            return ['resId' => 1];
        }

        $data = [
            'subject'       => 'Maarch Courrier Test',
            'docserver_id'  => 'FASTHD',
            'path'          => '2021/03/0001/',
            'filename'      => '0001_960655724.pdf',
            'fingerprint'   => 'file fingerprint',
            'format'        => 'pdf',
            'typist'        => 1,
            'version'       => 1
        ];

        if ($this->returnResourceWithoutFile) {
            $data['docserver_id']   = null;
            $data['path']           = null;
            $data['filename']       = null;
            $data['fingerprint']    = null;
            $data['format']         = null;
        }

        return $data;
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
        return [];
    }

    /**
     * @param   string  $docserverId
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getDocserverDataByDocserverId(string $docserverId, array $select = ['*']): array
    {
        if (empty($docserverId)) {
            return ['error' => "The 'docserverId' parameter can not be empty"];
        }
        if (!$this->doesResourceDocserverExist) {
            return [];
        }

        return ['path_template' => '/tmp', 'docserver_type_id' => 'DOC'];
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
        return;
    }

    /**
     * @param   string  $name
     * @param   int     $maxLength  Default value is 250 length
     * @return  string
     */
    public function formatFilename(string $name, int $maxLength = 250): string
    {
        return TextFormatModel::formatFilename(['filename' => $name, 'maxLength' => $maxLength]);
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
            return ['errors' => "The 'resId' parameter must be greater than 0"];
        }
        if (empty($collId) || ($collId !== 'letterbox_coll' && $collId !== 'attachments_coll')) {
            return ['errors' => "The 'collId' parameter can not be empty and should be 'letterbox_coll' or 'attachments_coll'"];
        }
        
        return [
            'docserver_id'  => 'FASTHD',
            'path'          => '2021/03/0001/',
            'filename'      => '0001_960655724.pdf',
            'fingerprint'   => 'file fingerprint'
        ];
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
        if (empty($type) || !in_array($type, $this::ADR_RESOURCE_TYPES)) {
            return ['error' => "The 'type' parameter should be : " . implode(', ', $this::ADR_RESOURCE_TYPES)];
        }
        if ($version <= 0) {
            return ['error' => "The 'version' parameter must be greater than 0"];
        }
        if (!$this->doesResourceVersionExist) {
            return [];
        }

        return [
            'docserver_id'  => 'FASTHD',
            'path'          => '2021/03/0001/',
            'filename'      => '0001_960655724.pdf',
            'fingerprint'   => 'file fingerprint'
        ];
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
        if (!$this->doesResourceVersionExist) {
            return [];
        }

        return [
            'docserver_id'  => 'FASTHD',
            'path'          => '2021/03/0001/',
            'filename'      => '0001_960655724.pdf',
            'fingerprint'   => 'file fingerprint'
        ];
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
        return $this->doesUserHasRights;
    }
}