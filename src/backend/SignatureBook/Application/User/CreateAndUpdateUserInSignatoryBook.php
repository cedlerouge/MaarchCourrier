<?php

namespace MaarchCourrier\SignatureBook\Application\User;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;

class CreateAndUpdateUserInSignatoryBook
{
    public function __construct(
        private readonly SignatureBookUserServiceInterface $signatureBookUserService,
        private readonly CurrentUserInterface $currentUser,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader

    ) {
    }

    /**
     * @throws SignatureBookNoConfigFoundProblem
     * @throws CurrentTokenIsNotFoundProblem
     */
    public function createAndUpdateUser(UserInterface $user): UserInterface
    {
        $signatureBook = $this->signatureServiceConfigLoader->getSignatureServiceConfig();
        if ($signatureBook === null) {
            throw new SignatureBookNoConfigFoundProblem();
        }
        $this->signatureBookUserService->setConfig($signatureBook);

        $accessToken = $this->currentUser->getCurrentUserToken();
        if (empty($accessToken)) {
            throw new CurrentTokenIsNotFoundProblem();
        }


        if (!empty($user->getExternalId())) {
            if ($this->signatureBookUserService->doesUserExists($user->getExternalId(), $accessToken)) {
                $this->signatureBookUserService->updateUser($user, $accessToken);
            } else {
                $existingIds = $user->getExternalId();
                $existingIds['internalParapheur'] = $this->signatureBookUserService->createUser($user, $accessToken);


                $user->setExternalId($existingIds);
            }
        } else {
            $maarchParapheurUserId = $this->signatureBookUserService->createUser($user, $accessToken);
            $external['internalParapheur'] = $maarchParapheurUserId;
            $user->setExternalId($external);
        }
        return $user;
    }
}
