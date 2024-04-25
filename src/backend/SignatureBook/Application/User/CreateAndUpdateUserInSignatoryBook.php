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
        if (!empty($user->getExternalId())) {
        } else {
            $accessToken = $this->currentUser->getCurrentUserToken();
            $maarchParapheurUserId[] = $this->signatureBookUserService->createUser($user, $accessToken);
            $user->setExternalId($maarchParapheurUserId);
        }
        return $user;
    }
}
