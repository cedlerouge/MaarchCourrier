<?php

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use MaarchCourrier\SignatureBook\Domain\Port\ResourceToSignRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdMasterNotCorrespondingProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class WebhookValidation
{
    public function __construct(private readonly ResourceToSignRepositoryInterface $resourceToSignRepository)
    {
    }

    /**
     * @param array $body
     * @return SignedResource
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     */
    public function validate(array $body): SignedResource
    {
        if (empty($body['retrieveDocUri'])) {
            throw new RetrieveDocumentUrlEmptyProblem();
        }

        if (!isset($body['payload']['res_id'])) {
            throw new ResourceIdEmptyProblem();
        }

        $signedResource = new SignedResource();

        $signedResource->setStatus($body['signatureState']['state']);

        if (!empty($body['signatureState']['message'])) {
            $signedResource->setMessageStatus(
                $body['signatureState']['message']
            );
        }

        if (!empty($body['signatureState']['error'])) {
            $signedResource->setMessageStatus(
                $body['signatureState']['error']
            );
        }

        if ($body['signatureState']['updatedDate'] !== null) {
            $signedResource->setSignatureDate($body['signatureState']['updatedDate']);
        }

        if (isset($body['payload']['res_id_master'])) {
            if (!$this->resourceToSignRepository->checkConcordanceResIdAndResIdMaster(
                $body['payload']['res_id'],
                $body['payload']['res_id_master']
            )) {
                throw new ResourceIdMasterNotCorrespondingProblem(
                    $body['payload']['res_id'],
                    $body['payload']['res_id_master']
                );
            }

            $infosAttachment = $this->resourceToSignRepository->getAttachmentInformations($body['payload']['res_id']);

            if (empty($infosAttachment)) {
                throw new AttachmentOutOfPerimeterProblem();
            }

            if ($this->resourceToSignRepository->isAttachementSigned($body['payload']['res_id'])) {
                throw new ResourceAlreadySignProblem();
            }

            $signedResource->setResIdMaster($body['payload']['res_id_master']);
        } else {
            if ($this->resourceToSignRepository->isResourceSigned($body['payload']['res_id'])) {
                throw new ResourceAlreadySignProblem();
            }
        }

        $signedResource->setResIdSigned($body['payload']['res_id']);

        return $signedResource;
    }
}
