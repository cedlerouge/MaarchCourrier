<?php

namespace MaarchCourrier\SignatureBook\Application\ProofFile;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookProofServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\ResourceToSignRepository;

class RetrieveProofFile
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly SignatureBookProofServiceInterface $proofService,
        private readonly ResourceToSignRepository $resourceToSignRepository,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader
    ) {
    }

    /**
     * @param  int  $resId
     * @param  bool  $isAttachment
     * @return array
     * @throws SignatureBookNoConfigFoundProblem
     */
    public function execute(int $resId, bool $isAttachment): array
    {
        //test existence resId
        $signatureBookConfig = $this->signatureServiceConfigLoader->getSignatureServiceConfig();
        if ($signatureBookConfig === null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $this->proofService->setConfig($signatureBookConfig);

        $infosDoc = ($isAttachment) ? $this->resourceToSignRepository->getAttachmentInformations($resId)
            : $this->resourceToSignRepository->getResourceInformations($resId);

        //test si external_id est renseigné
        $infosDoc = json_decode($infosDoc['external_id'], true);
        $idParapheur = $infosDoc['maarchParapheurApi'];

        $accessToken = $this->currentUser->generateNewToken();
        return $this->proofService->retrieveProofFile($idParapheur, $accessToken);
    }
}
