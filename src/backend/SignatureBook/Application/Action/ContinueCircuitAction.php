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
        $data['documentId'] = intval($data['documentId'] ?? 0);

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
            'documentId',
            'hashSignature',
            'certificate',
            'signatureContentLength',
            'signatureFieldName',
            'cookieSession'
        ];
        $missingData = [];

        $resourceToSign = [
            'resId' => $resId
        ];

        if ($data['digitalCertificate']) {
            foreach ($requiredData as $requiredDatum) {
                if (empty($data[$requiredDatum])) {
                    $missingData[] = $requiredDatum;
                }
            }

            if (!empty($missingData)) {
                throw new DataToBeSentToTheParapheurAreEmptyProblem($missingData);
            }

            $applySuccess = $this->signatureService
                ->setConfig($signatureBook)
                ->applySignature(
                    $data['documentId'],
                    $data['hashSignature'],
                    $data['signatures'] ?? [],
                    $data['certificate'],
                    $data['signatureContentLength'],
                    $data['signatureFieldName'],
                    $data['tmpUniqueId'] ?? null,
                    $accessToken,
                    $data['cookieSession'],
                    $resourceToSign
                );
            if (is_array($applySuccess)) {
                throw new SignatureNotAppliedProblem($applySuccess['errors']);
            }
        }

        return true;
    }
}
