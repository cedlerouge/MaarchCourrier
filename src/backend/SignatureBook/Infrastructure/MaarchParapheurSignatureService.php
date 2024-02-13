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
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceInterface;
use SrcCore\models\CurlModel;

class MaarchParapheurSignatureService implements SignatureServiceInterface
{
    private string $url;

    public function setUrl(string $url): MaarchParapheurSignatureService
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function applySignature(
        int $idDocument,
        string $hashSignature,
        array $signatures,
        string $certificate,
        string $signatureContentLength,
        string $signatureFieldName,
        ?string $tmpUniqueId,
        string $accessToken
    ): bool {
        $response = CurlModel::exec([
                'url'  => rtrim($this->url, '/') . '/rest/documents/' . $idDocument . '/actions/1',
                'bearerAuth'     => ['token' => $accessToken],
                'headers'       => [
                    'content-type: application/json',
                    'Accept: application/json'
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
        if ($response['code'] > 200) {
            return $response['response'];
        }
        return true;
    }
}
