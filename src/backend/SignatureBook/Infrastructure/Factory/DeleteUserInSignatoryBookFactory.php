<?php

namespace MaarchCourrier\SignatureBook\Infrastructure\Factory;

use MaarchCourrier\SignatureBook\Application\User\DeleteUserInSignatoryBook;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurUserService;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureServiceJsonConfigLoader;
use MaarchCourrier\User\Infrastructure\CurrentUserInformations;

class DeleteUserInSignatoryBookFactory
{
    public static function create(): DeleteUserInSignatoryBook
    {
        $currentUser = new CurrentUserInformations();
        $signatureBookUser = new MaarchParapheurUserService();
        $SignatureServiceConfigLoader = new SignatureServiceJsonConfigLoader();

        return new DeleteUserInSignatoryBook(
            $signatureBookUser,
            $currentUser,
            $SignatureServiceConfigLoader,
        );
    }
}
