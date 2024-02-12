<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief SignatureServiceConfigLoaderRepositoryMock class
 * @author dev@maarch.org
 */
namespace MaarchCourrier\Tests\app\signatureBook\Mock\Action;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfig;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;


class SignatureServiceJsonConfigLoaderMock implements SignatureServiceConfigLoaderInterface
{
    public ?SignatureServiceConfig $signatureServiceConfigLoader = null;

    public function __construct()
    {
        $this->signatureServiceConfigLoader = new SignatureServiceConfig(
            'test/url/maarch/parapheur/api'
        );
    }
    public function getSignatureServiceConfig(): ?SignatureServiceConfig
    {
        return $this->signatureServiceConfigLoader;
    }
}
