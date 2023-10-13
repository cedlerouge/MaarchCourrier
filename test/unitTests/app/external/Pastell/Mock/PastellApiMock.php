<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;

/**
 *
 */
class PastellApiMock implements PastellApiInterface
{

    /**
     * @var array
     */
    public array $version = [];

    /**
     * @var array|string[]
     */
    public array $entity = ['192', '193', '813'];

    /**
     * @var array|string[]
     */
    public array $connector = ['193', '776', '952'];

    /**
     * @var array
     */
    public array $flux = ['ls-document-pdf', 'test', 'not-pdf'];

    public array $iParapheurType = ['XELIANS COURRIER','TEST','PASTELL'];
    public array $iParapheurSousType = [];

    /**
     * @var array
     */
    public array $folder;

    /**
     * @param PastellConfig $config
     * @return string[]
     */
    public function getVersion(PastellConfig $config): array
    {
        return $this->version;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getEntity(PastellConfig $config): array
    {
        return $this->entity;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getConnector(PastellConfig $config): array
    {
        return $this->connector;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getDocumentType(PastellConfig $config): array
    {
        return $this->flux;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function createFolder(PastellConfig $config): array
    {
        return $this->folder;
    }
    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getIparapheurType(PastellConfig $config): array
    {
        return $this->iParapheurType;
    }

    /**
     * @param PastellConfig $config
     * @return array
     */
    public function getIparapheurSousType(PastellConfig $config): array
    {
        return $this->iParapheurSousType;
    }
}
