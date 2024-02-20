<?php

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\CurlRequest;
use MaarchCourrier\SignatureBook\Domain\Port\CurlServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignedResourceRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class RetrieveSignedResource
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly SignedResourceRepositoryInterface $signedResourceRepository,
        private readonly CurlServiceInterface $curlService
    ) {
    }

    /**
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws CurlRequestErrorProblem
     */
    public function retrieve(array $body): SignedResource
    {
        if (empty($body['retrieveDocUri'])) {
            throw new RetrieveDocumentUrlEmptyProblem();
        }

        $signedResource = new SignedResource();
        $signedResource->setStatus($body['signatureState']['state']);

        if ($body['signatureState']['updatedDate'] !== null) {
            $signedResource->setSignatureDate($body['signatureState']['updatedDate']);
        }

        $accessToken = $this->currentUser->getCurrentUserToken();
        if (empty($accessToken)) {
            throw new CurrentTokenIsNotFoundProblem();
        }

        $curlRequest = new CurlRequest();
        $curlRequest = $curlRequest->createFromArray([
            'url'    => $body['retrieveDocUri'],
            'method' => 'GET',
            'authBearer' => $accessToken
        ]);

        $curlRequest = $this->curlService->call($curlRequest);

        if ($curlRequest->getHttpCode() >= 300) {
            throw new CurlRequestErrorProblem($curlRequest->getHttpCode(), $curlRequest->getContentReturn());
        }

        $curlContent = $curlRequest->getContentReturn();

        if (!empty($curlContent['encodedDocument'])) {
            $signedResource->setEncodedContent($curlContent['encodedDocument']);
        }

        $signedResource->setUserSerialId($this->currentUser->getCurrentUserId());

        return $signedResource;
    }
}
