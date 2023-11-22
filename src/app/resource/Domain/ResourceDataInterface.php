<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource Data Interface
 * @author dev@maarch.org
 */

namespace Resource\Domain;

interface ResourceDataInterface
{
    public const ERROR_RESOURCE_DOES_NOT_EXIST = 'Document does not exist';
    public const ERROR_RESOURCE_HAS_NO_FILE = 'Document has no file';
    public const ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST = 'Docserver does not exist';
    public const ADR_RESOURCE_TYPES = ['PDF', 'SIGN', 'NOTE'];
    public const ERROR_RESOURCE_INCORRECT_VERSION = 'Incorrect version';

    /**
     * @param   int     $resId
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getMainResourceData(int $resId, array $select = ['*']): array;

    /**
     * @param   int     $resId
     * @param   int     $version
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getSignResourceData(int $resId, int $version, array $select = ['*']): array;

    /**
     * @param   string  $docserverId
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getDocserverDataByDocserverId(string $docserverId, array $select = ['*']): array;

    /**
     * Update resource fingerprint
     * 
     * @param   int     $resId
     * @param   string  $fingerprint
     * @return  void
     */
    public function updateFingerprint(int $resId, string $fingerprint): void;

    /**
     * @param   string  $name
     * @param   int     $maxLength  Default value is 250 length
     * @return  string
     */
    public function formatFilename(string $name, int $maxLength = 250): string;

    /**
     * Return the converted pdf from resource
     * 
     * @param   int     $resId  Resource id
     * @param   string  $collId Resource type id : letterbox_coll or attachments_coll
     * @return  array
     */
    public function getConvertedPdfById(int $resId, string $collId): array;

    /**
     * @param   int     $resId      Resource id
     * @param   string  $type       Resource converted format
     * @param   int     $version    Resource version
     * @return  array
     */
    public function getResourceVersion(int $resId, string $type, int $version): array;
}
