<?php
/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief WebhookValidation class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Application\Webhook;

use DateTime;
use MaarchCourrier\SignatureBook\Domain\Port\ResourceToSignRepositoryInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\AttachmentOutOfPerimeterProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceAlreadySignProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\ResourceIdMasterNotCorrespondingProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\RetrieveDocumentUrlEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\SignedResource;
use MaarchCourrier\User\Domain\Port\UserRepositoryInterface;
use MaarchCourrier\User\Domain\Problem\UserDoesNotExistProblem;

class WebhookValidation
{
    public function __construct(
        private readonly ResourceToSignRepositoryInterface $resourceToSignRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @param array $body
     * @return SignedResource
     * @throws AttachmentOutOfPerimeterProblem
     * @throws ResourceAlreadySignProblem
     * @throws ResourceIdEmptyProblem
     * @throws ResourceIdMasterNotCorrespondingProblem
     * @throws RetrieveDocumentUrlEmptyProblem
     * @throws CurrentTokenIsNotFoundProblem
     * @throws UserDoesNotExistProblem
     * @throws \Exception
     */
    public function validate(array $body, array $decodedToken): SignedResource
    {
        if (empty($body['retrieveDocUri'])) {
            throw new RetrieveDocumentUrlEmptyProblem();
        }

        if (empty($body['token']) || !isset($decodedToken['userId'])) {
            throw new CurrentTokenIsNotFoundProblem();
        }

        $currentUser = $this->userRepository->getUserById($decodedToken['userId']);
        if ($currentUser === null) {
            throw new UserDoesNotExistProblem();
        }

        $GLOBALS['id'] = $decodedToken['userId'];

        if (!isset($decodedToken['res_id'])) {
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
            $signedResource->setSignatureDate(new DateTime($body['signatureState']['updatedDate']));
        }

        if (isset($decodedToken['res_id_master'])) {
            if (!$this->resourceToSignRepository->checkConcordanceResIdAndResIdMaster(
                $decodedToken['res_id'],
                $decodedToken['res_id_master']
            )) {
                throw new ResourceIdMasterNotCorrespondingProblem(
                    $decodedToken['res_id'],
                    $decodedToken['res_id_master']
                );
            }

            $infosAttachment = $this->resourceToSignRepository->getAttachmentInformations($decodedToken['res_id']);

            if (empty($infosAttachment)) {
                throw new AttachmentOutOfPerimeterProblem();
            }

            if ($this->resourceToSignRepository->isAttachementSigned($decodedToken['res_id'])) {
                throw new ResourceAlreadySignProblem();
            }

            $signedResource->setResIdMaster($decodedToken['res_id_master']);
        } else {
            if ($this->resourceToSignRepository->isResourceSigned($decodedToken['res_id'])) {
                throw new ResourceAlreadySignProblem();
            }
        }

        $signedResource->setResIdSigned($decodedToken['res_id']);

        return $signedResource;
    }
}
