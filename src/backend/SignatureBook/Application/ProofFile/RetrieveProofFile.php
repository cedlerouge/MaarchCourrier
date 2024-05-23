<?php

namespace MaarchCourrier\SignatureBook\Application\ProofFile;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookProofServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\SignatureBook\Infrastructure\Repository\ResourceToSignRepository;

class RetrieveProofFile
{
    public function __construct(
        private readonly SignatureBookProofServiceInterface $proofService,
        private readonly ResourceToSignRepository $resourceToSignRepository,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader
    ) {
    }

    public function execute(bool $isAttachment): array
    {
        $signatureBookConfig = $this->signatureServiceConfigLoader->getSignatureServiceConfig();
        if ($signatureBookConfig === null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $this->proofService->setConfig($signatureBookConfig);


        return [];
    }
}