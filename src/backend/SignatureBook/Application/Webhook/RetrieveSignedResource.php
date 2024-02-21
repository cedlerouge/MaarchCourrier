<?php

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\CurlRequest;
use MaarchCourrier\SignatureBook\Domain\Port\CurlServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\ResourceToSignRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\Port\StoreSignedResourceServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurlRequestErrorProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\StoreResourceProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class RetrieveSignedResource
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly ResourceToSignRepositoryInterface $resourceToSignRepository,
        private readonly StoreSignedResourceServiceInterface $storeSignedResourceService,
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
        if ($body['payload']['res_id'] !== null) {
            $signedResource->setResIdSigned($body['payload']['res_id']);
        }

        if ($body['payload']['res_id_master'] !== null) {
            $signedResource->setResIdMaster($body['payload']['res_id_master']);
        }

        return $signedResource;
    }

    /**
     * @throws AttachmentOutOfPerimeterProblem
     * @throws StoreResourceProblem
     */
    public function store(SignedResource $signedResource): int
    {
        if ($signedResource->getResIdMaster() !== null) {
            $attachment = $this->resourceToSignRepository->getAttachmentInformations($signedResource->getResIdSigned());
            if (empty($attachment)) {
                throw new AttachmentOutOfPerimeterProblem();
            } else {
                $id = $this->storeSignedResourceService->storeAttachement($signedResource, $attachment);
                $this->resourceToSignRepository->updateAttachementStatus($signedResource->getResIdSigned());
            }
        } else {
            $storeResource = $this->storeSignedResourceService->storeResource($signedResource);
            if (!empty($storeResult['errors'])) {
                throw new StoreResourceProblem($storeResult['errors']);
            } else {
                $this->resourceToSignRepository->createSignVersionForResource($signedResource->getResIdSigned(), $storeResource);
                $id = $signedResource->getResIdSigned();
            }
        }

        return $id;
    }
}
