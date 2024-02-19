<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief SignatureServiceInterface class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Domain\Port;

interface SignatureServiceInterface
{
    public function setConfig(SignatureServiceConfig $config): SignatureServiceInterface;

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
    ): array|bool;
}
