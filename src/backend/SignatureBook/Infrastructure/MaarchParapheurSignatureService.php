<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief MaarchParapheurSignatureService class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure;

use Exception;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfig;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceInterface;
use SrcCore\models\CurlModel;

class MaarchParapheurSignatureService implements SignatureServiceInterface
{
    private SignatureServiceConfig $config;

    public function setConfig(SignatureServiceConfig $config): MaarchParapheurSignatureService
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @throws Exception
     */
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
        $response = CurlModel::exec([
                'url'  => rtrim($this->config->getUrl(), '/') . '/rest/documents/' . $documentId . '/actions/1',
                'bearerAuth'     => ['token' => $accessToken],
                'headers'       => [
                    'content-type: application/json',
                    'Accept: application/json',
                    'cookie: PHPSESSID=' . $cookieSession
                ],
                'method'        => 'PUT',
                'body'      => json_encode([
                    'hashSignature'          => $hashSignature,
                    'signatures'             => $signatures,
                    'certificate'            => $certificate,
                    'signatureContentLength' => $signatureContentLength,
                    'signatureFieldName'     => $signatureFieldName,
                    'tmpUniqueId'            => $tmpUniqueId
                ]),
            ]);
        if ($response['code'] >= 400) {
            return $response['response'] ?? ['errors' => 'Error occurred while applying the signature'];
        }
        return true;
    }
}
