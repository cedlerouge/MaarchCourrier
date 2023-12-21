<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\resource\Mock;

use Resource\Domain\Docserver;
use Resource\Domain\Resource;
use Resource\Domain\ResourceConverted;
use Resource\Domain\Ports\ResourceDataInterface;
use SrcCore\models\TextFormatModel;

class ResourceDataMock implements ResourceDataInterface
{
    public bool $doesResourceExist = true;
    public bool $doesResourceFileExistInDatabase = true;
    public bool $doesResourceDocserverExist = true;
    public bool $doesResourceVersionExist = true;
    public bool $returnResourceWithoutFile = false;
    public bool $doesUserHasRights = true;
    public bool $isResourceDocserverEncrypted = false;
    public bool $doesFingerprint = false;
    public string $fingerprint = 'file fingerprint';

    public function getMainResourceData(int $resId): ?Resource
    {
        if (!$this->doesResourceExist) {
            return null;
        }

        $resourceFromDB = [
            'res_id'        => 1,
            'subject'       => 'Maarch Courrier Test',
            'docserver_id'  => 'FASTHD',
            'path'          => '2021/03/0001/',
            'filename'      => '0001_960655724.pdf',
            'version'       => 1,
            'fingerprint'   => $this->fingerprint,
            'format'        => 'pdf',
            'typist'        => 1
        ];

        $resource = Resource::createFromArray($resourceFromDB);

        if ($this->returnResourceWithoutFile || !$this->doesResourceFileExistInDatabase) {
            $resource->setDocserverId(null);
            $resource->setPath(null);
            $resource->setFilename(null);
            $resource->setFingerprint(null);
            $resource->setFormat(null);
        }

        return $resource;
    }

    public function getSignResourceData(int $resId, int $version): ?ResourceConverted
    {
        if (!$this->doesResourceExist) {
            return null;
        }

        return new ResourceConverted(
            1,
            1,
            'docId',
            1,
            'DocTest',
            'a/path/',
            'ResourceConvertedTest',
            $this->fingerprint
        );
    }

    public function getDocserverDataByDocserverId(string $docserverId): ?Docserver
    {
        if (empty($docserverId)) {
            return null;
        }

        if (!$this->doesResourceDocserverExist) {
            return null;
        }

        return new Docserver(
            1,
            'FASTHD',
            'DOC',
            '/tmp',
            $this->isResourceDocserverEncrypted
        );
    }

    public function updateFingerprint(int $resId, string $fingerprint): void
    {
        $this->doesFingerprint = true;
    }

    public function formatFilename(string $name, int $maxLength = 250): string
    {
        return TextFormatModel::formatFilename(['filename' => $name, 'maxLength' => $maxLength]);
    }

    /**
     * Return the converted pdf from resource
     *
     * @param   int     $resId  Resource id
     * @param   string  $collId Resource type id : letterbox_coll or attachments_coll
     */
    public function getConvertedPdfById(int $resId, string $collId): array
    {
        return [
            'docserver_id' => 'FASTHD',
            'path' => '2021/03/0001/',
            'filename' => '0001_960655724.pdf',
            'fingerprint' => 'file fingerprint'
        ];
    }

    /**
     * @param   int     $resId      Resource id
     * @param   string  $type       Resource converted format
     * @param   int     $version    Resource version
     *
     * @return  ?array
     */
    public function getResourceVersion(int $resId, string $type, int $version): ?array
    {
        if (!$this->doesResourceVersionExist) {
            return null;
        }

        return [
            'id' => 1,
            'res_id' => $resId,
            'type' => $type,
            'version' => $version,
            'docserver_id' => 'FASTHD',
            'path' => '2021/03/0001/',
            'filename' => '0001_960655724.pdf',
            'fingerprint' => 'file fingerprint'
        ];
    }

    /**
     * @param   int     $resId  Resource id
     * @param   string  $type   Resource converted format
     *
     * @return  ?ResourceConverted
     */
    public function getLatestResourceVersion(int $resId, string $type): ?ResourceConverted
    {
        if (!$this->doesResourceVersionExist) {
            return null;
        }

        return new ResourceConverted(1, $resId, $type, 1, 'FASTHD', '2021/03/0001/', '0001_960655724.pdf', 'file fingerprint');
    }

    /**
     * @param   int $resId      Resource id
     * @param   int $version    Resource version
     *
     * @return  ResourceConverted
     */
    public function getLatestPdfVersion(int $resId, int $version): ?ResourceConverted
    {
        return new ResourceConverted(1, $resId, 'PDF', 1, 'FASTHD', '2021/03/0001/', '0001_960655724.pdf', 'file fingerprint');
    }

    /**
     * Check if user has rights over the resource
     *
     * @param   int     $resId      Resource id
     * @param   int     $userId     User id
     *
     * @return  bool
     */
    public function hasRightByResId(int $resId, int $userId): bool
    {
        return $this->doesUserHasRights;
    }
}
