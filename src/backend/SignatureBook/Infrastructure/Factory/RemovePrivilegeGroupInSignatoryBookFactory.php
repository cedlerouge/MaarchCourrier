<?php

namespace MaarchCourrier\SignatureBook\Infrastructure\Factory;

use MaarchCourrier\Authorization\Infrastructure\PrivilegeChecker;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurGroupService;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureServiceJsonConfigLoader;
use MaarchCourrier\SignatureBook\Application\Group\RemovePrivilegeGroupInSignatoryBook;

class RemovePrivilegeGroupInSignatoryBookFactory
{
    public function create(): RemovePrivilegeGroupInSignatoryBook
    {
        $signatureBookGroup = new MaarchParapheurGroupService();
        $signatureBookConfigLoader = new SignatureServiceJsonConfigLoader();
        $privilegeChecker = new PrivilegeChecker();

        return new RemovePrivilegeGroupInSignatoryBook(
            $signatureBookGroup,
            $signatureBookConfigLoader,
            $privilegeChecker
        );
    }
}
