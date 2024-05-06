<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief continueCircuitAction class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Application\Action;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\DataToBeSentToTheParapheurAreEmptyProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\NoDocumentsInSignatureBookForThisId;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureNotAppliedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;

class ContinueCircuitAction
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly SignatureServiceInterface $signatureService,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader,
        private readonly bool $isNewSignatureBookEnabled
    ) {
    }

    /**
     * @param int $resId
     * @param array $data
     * @param array $note
     * @return bool
     * @throws CurrentTokenIsNotFoundProblem
     * @throws DataToBeSentToTheParapheurAreEmptyProblem
     * @throws SignatureBookNoConfigFoundProblem
     * @throws SignatureNotAppliedProblem
     */
    public function execute(int $resId, array $data, array $note): bool
    {
        if (!$this->isNewSignatureBookEnabled) {
            return true;
        }

        $signatureBook = $this->signatureServiceConfigLoader->getSignatureServiceConfig();
        if ($signatureBook === null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $accessToken = $this->currentUser->getCurrentUserToken();
        if (empty($accessToken)) {
            throw new CurrentTokenIsNotFoundProblem();
        }

        $requiredData = [
            'resId',
            'documentId',
            'hashSignature',
            'certificate',
            'signatureContentLength',
            'signatureFieldName',
            'cookieSession'
        ];


        if (isset($data[$resId])) {
            foreach ($data[$resId] as $document) {
                $missingData = [];

                foreach ($requiredData as $requiredDatum) {
                    if (empty($document[$requiredDatum])) {
                        $missingData[] = $requiredDatum;
                    }
                }

                if (!empty($missingData)) {
                    throw new DataToBeSentToTheParapheurAreEmptyProblem($missingData);
                }

                $document['documentId'] = intval($document['documentId'] ?? 0);

                $resourceToSign = [
                    'resId' => $document['resId']
                ];

                if (isset($document['isAttachment']) && $document['isAttachment']) {
                    $resourceToSign['resIdMaster'] = $resId;
                }

                $applySuccess = $this->signatureService
                    ->setConfig($signatureBook)
                    ->applySignature(
                        $document['documentId'],
                        $document['hashSignature'],
                        $document['signatures'] ?? [],
                        $document['certificate'],
                        $document['signatureContentLength'],
                        $document['signatureFieldName'],
                        $document['tmpUniqueId'] ?? null,
                        $accessToken,
                        $document['cookieSession'],
                        $resourceToSign
                    );
                if (is_array($applySuccess)) {
                    throw new SignatureNotAppliedProblem($applySuccess['errors']);
                }
            }
        } else {
            throw new NoDocumentsInSignatureBookForThisId();
        }

        return true;
    }
}
