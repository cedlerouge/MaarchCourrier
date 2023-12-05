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

namespace Resource\Domain\Interfaces;

use Resource\Domain\Docserver;
use Resource\Domain\Resource;
use Resource\Domain\ResourceConverted;

interface ResourceDataInterface
{
    public const ADR_RESOURCE_TYPES = ['PDF', 'TNL', 'SIGN', 'NOTE'];
    // public const ERROR_RESOURCE_OUT_OF_PERIMETER = "Document out of perimeter";
    // public const ERROR_RESOURCE_DOES_NOT_EXIST = 'Document does not exist';
    // public const ERROR_RESOURCE_HAS_NO_FILE = 'Document has no file';
    // public const ERROR_RESOURCE_DOCSERVER_DOES_NOT_EXIST = 'Docserver does not exist';
    // public const ERROR_RESOURCE_INCORRECT_VERSION = 'Incorrect version';

    /**
     * @param   int     $resId
     * 
     * @return  Resource
     * 
     * @throws  ExceptionParameterMustBeGreaterThan|ExceptionResourceDoesNotExist
     */
    public function getMainResourceData(int $resId): Resource;

    /**
     * @param   int     $resId
     * @param   int     $version
     * 
     * @return  ResourceConverted
     * 
     * @throws  ExceptionParameterMustBeGreaterThan|ExceptionResourceDoesNotExist
     */
    public function getSignResourceData(int $resId, int $version): ResourceConverted;

    /**
     * @param   string  $docserverId
     * 
     * @return  Docserver
     * 
     * @throws  ExceptionParameterCanNotBeEmpty|ExceptionResourceDocserverDoesNotExist
     */
    public function getDocserverDataByDocserverId(string $docserverId): Docserver;

    /**
     * Update resource fingerprint
     * 
     * @param   int     $resId
     * @param   string  $fingerprint
     * 
     * @return  void
     */
    public function updateFingerprint(int $resId, string $fingerprint): void;

    /**
     * @param   string  $name
     * @param   int     $maxLength  Default value is 250 length
     * 
     * @return  string
     */
    public function formatFilename(string $name, int $maxLength = 250): string;

    /**
     * Return the converted pdf from resource
     * 
     * @param   int     $resId  Resource id
     * @param   string  $collId Resource type id : letterbox_coll or attachments_coll
     * 
     * @return  ResourceConverted
     * 
     * @throws  ExceptionParameterMustBeGreaterThan|ExceptionParameterCanNotBeEmptyAndShould|ExecptionConvertedResult
     */
    public function getConvertedPdfById(int $resId, string $collId): ResourceConverted;

    /**
     * @param   int     $resId      Resource id
     * @param   string  $type       Resource converted format
     * @param   int     $version    Resource version
     * 
     * @return  ?ResourceConverted
     * 
     * @throws  ExceptionParameterMustBeGreaterThan|ExceptionParameterCanNotBeEmptyAndShould
     */
    public function getResourceVersion(int $resId, string $type, int $version): ?ResourceConverted;

    /**
     * @param   int     $resId  Resource id
     * @param   string  $type   Resource converted format
     * 
     * @return  ResourceConverted
     * 
     * @throws  ExceptionParameterMustBeGreaterThan|ExceptionParameterCanNotBeEmptyAndShould|ExceptionResourceDoesNotExist
     */
    public function getLatestResourceVersion(int $resId, string $type): ResourceConverted;

    /**
     * Check if user has rights over the resource
     * 
     * @param   int     $resId      Resource id
     * @param   int     $userId     User id
     * 
     * @return  bool
     */
    public function hasRightByResId(int $resId, int $userId): bool;
}
