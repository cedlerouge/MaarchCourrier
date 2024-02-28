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
use SrcCore\controllers\UrlController;
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
     * @param int $documentId
     * @param string $hashSignature
     * @param array $signatures
     * @param string $certificate
     * @param string $signatureContentLength
     * @param string $signatureFieldName
     * @param string|null $tmpUniqueId
     * @param string $accessToken
     * @param string $cookieSession
     * @param array $resourceToSign
     * @param array|null $webhook
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
        string $cookieSession,
        array $resourceToSign
    ): array|bool {

        $webhook = [
            'url' => UrlController::getCoreUrl() . '/signatureBook/webhook',
            'payload' => $resourceToSign
        ];

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
                    'tmpUniqueId'            => $tmpUniqueId/*,
                    'webhook'                => $webhook*/
                ]),
            ]);
        if ($response['code'] > 200) {
            return $response['response'];
        }
        return true;
    }
}
