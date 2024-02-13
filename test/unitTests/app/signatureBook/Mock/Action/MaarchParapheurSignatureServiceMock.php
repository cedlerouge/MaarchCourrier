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

namespace MaarchCourrier\Tests\app\signatureBook\Mock\Action;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceInterface;

class MaarchParapheurSignatureServiceMock implements SignatureServiceInterface
{
    private $url;
    public array|bool $applySignature = true;
    public function setUrl(string $url): SignatureServiceInterface
    {
        $this->url;

        return $this;
    }

    public function applySignature(
        int $idDocument,
        string $hashSignature,
        array $signatures,
        string $certificate,
        string $signatureContentLength,
        string $signatureFieldName,
        ?string $tmpUniqueId,
        string $accessToken
    ): array|bool {
        return $this->applySignature;
    }
}
