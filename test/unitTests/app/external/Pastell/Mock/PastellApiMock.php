<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;

class PastellApiMock implements PastellApiInterface
{
    public array $version = [];
    public array $entity = ['192', '193', '813'];
    public array $connector = ['193', '776', '952'];
    public array $flux = ['ls-document-pdf', 'test', 'not-pdf'];
    public array $iParapheurType = ['XELIANS COURRIER', 'TEST', 'PASTELL'];
    public array $folder = ['idFolder' => 'hfqvhv'];
    public array $iParapheurSousType = ['courrier', 'réponse au citoyen'];
    public array $documentDetails = [];
    public array $mainFile = [];
    public array $dataFolder = [];

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
    public function getIparapheurType(PastellConfig $config): array
    {
        return $this->iParapheurType;
    }

    /**
     * @param PastellConfig $config
     * @return array|string[]
     */
    public function createFolder(PastellConfig $config): array
    {
        return $this->folder;
    }

    /**
     * @param PastellConfig $config
     * @param string $idDocument
     * @return array|string[]
     */
    public function getIparapheurSousType(PastellConfig $config, string $idDocument): array
    {
        return $this->iParapheurSousType;
    }

    /**
     * @param PastellConfig $config
     * @param string $idDocument
     * @return array|string[]
     */
    public function editFolder(PastellConfig $config, string $idDocument): array
    {
        return $this->dataFolder;
    }

    /**
     * @param PastellConfig $config
     * @param string $idDocument
     * @return array
     */
    public function uploadMainFile(PastellConfig $config, string $idDocument): array
    {
        return $this->mainFile;
    }

    /**
     * @param PastellConfig $config
     * @param string $idDocument
     * @return array
     */
    public function getDocumentDetail(PastellConfig $config, string $idDocument): array
    {
        return $this->documentDetails;
    }
}
