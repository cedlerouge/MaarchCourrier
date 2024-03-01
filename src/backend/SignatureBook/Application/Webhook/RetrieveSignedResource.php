<?php
/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief RetrieveSignedResource class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\CurlRequest;
use MaarchCourrier\SignatureBook\Domain\Port\CurlServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class RetrieveSignedResource
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly CurlServiceInterface $curlService
    ) {
    }

    /**
     * @param SignedResource $signedResource
     * @param string $urlRetrieveDoc
     * @return SignedResource
     * @throws CurlRequestErrorProblem
     */
    public function retrieve(SignedResource $signedResource, string $urlRetrieveDoc): SignedResource
    {
        $accessToken = $this->currentUser->generateNewToken();

        $curlRequest = new CurlRequest();
        $curlRequest = $curlRequest->createFromArray([
            'url'        => $urlRetrieveDoc,
            'method'     => 'GET',
            'authBearer' => $accessToken
        ]);

        $curlRequest = $this->curlService->call($curlRequest);

        if ($curlRequest->getCurlResponse()->getHttpCode() >= 300) {
            throw new CurlRequestErrorProblem(
                $curlRequest->getCurlResponse()->getHttpCode(),
                $curlRequest->getCurlResponse()->getContentReturn()
            );
        }

        $curlResponseContent = $curlRequest->getCurlResponse()->getContentReturn();

        if (!empty($curlResponseContent['encodedDocument'])) {
            $signedResource->setEncodedContent($curlResponseContent['encodedDocument']);
        }

        $signedResource->setUserSerialId($this->currentUser->getCurrentUserId());

        return $signedResource;
    }

}
