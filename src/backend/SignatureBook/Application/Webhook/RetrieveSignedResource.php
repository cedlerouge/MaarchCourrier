<?php

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use MaarchCourrier\SignatureBook\Domain\CurlRequest;
use MaarchCourrier\SignatureBook\Domain\Ports\CurlServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Ports\SignedResourceRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\Problems\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problems\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class RetrieveSignedResource
{
    public function __construct(
        private readonly SignedResourceRepositoryInterface $signedResourceRepository,
        private readonly CurlServiceInterface $curlService
    ) {
    }

    /**
     * @throws RetrieveDocumentUrlEmptyProblem
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

        $curlRequest = new CurlRequest();
        $curlRequest = $curlRequest->createFromArray([
            'url'    => $body['retrieveDocUri'],
            'method' => 'GET'
        ]);

        $curlRequest = $this->curlService->call($curlRequest);

        if ($curlRequest->getHttpCode() >= 300) {
            throw new CurlRequestErrorProblem($curlRequest->getHttpCode(), $curlRequest->getContentReturn());
        }

        $curlContent = $curlRequest->getContentReturn();

        if (!empty($curlContent['encodedDocument'])) {
            $signedResource->setEncodedContent($curlContent['encodedDocument']);
        }

        return $signedResource;
    }
}
