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

use Exception;
use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\DataToBeSentToTheParapheurAreEmpty;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureNotAppliedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundException;

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
     * @throws Exception
     */
    public function execute(int $resId, array $data, array $note): bool
    {
        $data['idDocument'] = intval($data['idDocument'] ?? 0) ;
        if (!$this->isNewSignatureBookEnabled) {
            return true;
        }
        $signatureBook = $this->signatureServiceConfigLoader->getSignatureServiceConfig();
        if ($signatureBook === null) {
            throw new SignatureBookNoConfigFoundException();
        }
        $accessToken = $this->currentUser->getCurrentUserToken();
        if (empty($accessToken)) {
            throw new CurrentTokenIsNotFoundProblem();
        }

        if (
               empty($data['idDocument'])
            || empty($data['hashSignature'])
            || empty($data['certificate'])
            || empty($data['signatureContentLength'])
            || empty($data['signatureFieldName'])
            || empty($data['cookieSession'])
        ) {
            throw new DataToBeSentToTheParapheurAreEmpty();
        }

        $applySuccess = $this->signatureService
            ->setUrl($signatureBook->getUrl())
            ->applySignature(
                $data['idDocument'],
                $data['hashSignature'],
                $data['signatures'] ?? [],
                $data['certificate'],
                $data['signatureContentLength'],
                $data['signatureFieldName'],
                $data['tmpUniqueId'] ?? null,
                $accessToken,
                $data['cookieSession']
            );
        if (is_array($applySuccess)) {
            throw new SignatureNotAppliedProblem($applySuccess['errors']);
        }

        return true;
    }
}
