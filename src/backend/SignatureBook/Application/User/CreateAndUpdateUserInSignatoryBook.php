<?php

namespace MaarchCourrier\SignatureBook\Application\User;

use MaarchCourrier\Core\Domain\User\Port\CurrentUserInterface;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;

class CreateAndUpdateUserInSignatoryBook
{
    public function __construct(
        private readonly SignatureBookUserServiceInterface $signatureBookUserService,
        private readonly CurrentUserInterface $currentUser
    ) {
    }
    public function createAndUpdateUser(UserInterface $user): UserInterface
    {
        $accessToken = $this->currentUser->getCurrentUserToken();
        if (!empty($user->getExternalId())) {
            if ($this->signatureBookUserService->doesUserExists($user->getExternalId(), $accessToken)) {
                $this->signatureBookUserService->updateUser($user, $accessToken);
            } else {
                $existingIds = $user->getExternalId();
                $maarchParapheurUserId = $this->signatureBookUserService->createUser($user, $accessToken);
                if (!in_array($maarchParapheurUserId, $existingIds)) {
                    $existingIds[] = $maarchParapheurUserId;
                }
                $user->setExternalId($existingIds);
            }
        } else {
            $maarchParapheurUserId[] = $this->signatureBookUserService->createUser($user, $accessToken);
            $user->setExternalId($maarchParapheurUserId);
        }
        return $user;
    }
}
