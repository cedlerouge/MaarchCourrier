<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief MaarchParapheurSignatureServiceMock class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\Action;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfig;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceInterface;

class MaarchParapheurSignatureServiceMock implements SignatureServiceInterface
{
    private SignatureServiceConfig $config;
    public array|bool $applySignature = true;
    public function setConfig(SignatureServiceConfig $config): SignatureServiceInterface
    {
        $this->config = $config;
        return $this;
    }

    public function applySignature(
        int $documentId,
        string $hashSignature,
        array $signatures,
        string $certificate,
        string $signatureContentLength,
        string $signatureFieldName,
        ?string $tmpUniqueId,
        string $accessToken,
        string $cookieSession
    ): array|bool {
        return $this->applySignature;
    }
}
