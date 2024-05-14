<?php

namespace MaarchCourrier\SignatureBook\Application\User;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use MaarchCourrier\SignatureBook\Domain\Problem\CurrentTokenIsNotFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\SignatureBookNoConfigFoundProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\UserCreateInMaarchParapheurFailedProblem;
use MaarchCourrier\SignatureBook\Domain\Problem\UserUpdateInMaarchParapheurFailedProblem;

class CreateAndUpdateUserInSignatoryBook
{
    /**
     * @param SignatureBookUserServiceInterface $signatureBookUserService
     * @param CurrentUserInterface $currentUser
     * @param SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader
     */
    public function __construct(
        private readonly SignatureBookUserServiceInterface $signatureBookUserService,
        private readonly CurrentUserInterface $currentUser,
        private readonly SignatureServiceConfigLoaderInterface $signatureServiceConfigLoader
    ) {
    }

    /**
     * @param UserInterface $user
     * @return UserInterface
     * @throws CurrentTokenIsNotFoundProblem
     * @throws SignatureBookNoConfigFoundProblem
     * @throws UserCreateInMaarchParapheurFailedProblem
     * @throws UserUpdateInMaarchParapheurFailedProblem
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

        $externalId = (int)array_values($user->getExternalId());

        if (!empty($externalId)) {
            if ($this->signatureBookUserService->doesUserExists($externalId, $accessToken)) {
                $userIsUpdated = $this->signatureBookUserService->updateUser($user, $accessToken);
                if (!empty($userIsUpdated['errors'])) {
                    throw new UserUpdateInMaarchParapheurFailedProblem($userIsUpdated);
                }
            } else {
                $existingIds = $user->getExternalId();
                $maarchParapheurUserId = $this->signatureBookUserService->createUser($user, $accessToken);
                if (!empty($maarchParapheurUserId['errors'])) {
                    throw new UserCreateInMaarchParapheurFailedProblem($maarchParapheurUserId);
                } else {
                    $existingIds['internalParapheur'] = $maarchParapheurUserId;
                    $user->setExternalId($existingIds);
                }
            }
        } else {
            $maarchParapheurUserId = $this->signatureBookUserService->createUser($user, $accessToken);
            if (!empty($maarchParapheurUserId['errors'])) {
                throw new UserCreateInMaarchParapheurFailedProblem($maarchParapheurUserId);
            } else {
                $external['internalParapheur'] = $maarchParapheurUserId;
                $user->setExternalId($external);
            }
        }
        return $user;
    }
}
