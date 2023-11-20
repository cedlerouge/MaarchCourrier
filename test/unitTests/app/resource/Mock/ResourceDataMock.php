<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Resource\Domain\ResourceDataInterface;
use SrcCore\models\TextFormatModel;

class ResourceDataMock implements ResourceDataInterface
{
    public bool $doesRessourceExist = true;
    public bool $doesRessourceFileExistInDatabase = true;
    public bool $doesRessourceDocserverExist = true;

    /**
     * @param   int     $resId
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getMainResourceData(int $resId, array $select = ['*']): array
    {
        if (!$this->doesRessourceExist) {
            return [];
        }

        if (!$this->doesRessourceFileExistInDatabase) {
            return ['resId' => 1];
        }

        // if (!$this->doesRessourceDocserverExist) {
        //     return ['filename'];
        // }        

        return [
            'subject'       => 'Maarch Courrier Test',
            'docserver_id'  => 'FASTHD',
            'path'          => '2021/03/0001/',
            'filename'      => '0001_960655724.pdf',
            'fingerprint'   => 'file fingerprint'
        ];
    }

    /**
     * @param   int     $resId
     * @param   int     $version
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getSignResourceData(int $resId, int $version, array $select = ['*']): array
    {
        return [];
    }

    /**
     * @param   string  $docserverId
     * @param   array   $select, default value is ['*']
     * @return  array
     */
    public function getDocserverDataByDocserverId(string $docserverId, array $select = ['*']): array
    {
        if (!$this->doesRessourceDocserverExist) {
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
     * @return  string
     */
    public function formatFilename(string $name): string
    {
        return TextFormatModel::formatFilename(['filename' => $name, 'maxLength' => 250]);
    }
}