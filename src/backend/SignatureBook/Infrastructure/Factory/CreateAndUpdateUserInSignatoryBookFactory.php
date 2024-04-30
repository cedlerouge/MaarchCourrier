<?php

namespace MaarchCourrier\SignatureBook\Infrastructure\Factory;

use MaarchCourrier\SignatureBook\Application\User\CreateAndUpdateUserInSignatoryBook;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurUserService;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureServiceJsonConfigLoader;
use MaarchCourrier\User\Infrastructure\CurrentUserInformations;

class CreateAndUpdateUserInSignatoryBookFactory
{
    public function create(): CreateAndUpdateUserInSignatoryBook
    {
        $currentUser = new CurrentUserInformations();
        $signatureBookUser = new MaarchParapheurUserService();
        $SignatureServiceConfigLoader = new SignatureServiceJsonConfigLoader();


        return new CreateAndUpdateUserInSignatoryBook(
            $signatureBookUser,
            $currentUser,
            $SignatureServiceConfigLoader
        );
    }
}
