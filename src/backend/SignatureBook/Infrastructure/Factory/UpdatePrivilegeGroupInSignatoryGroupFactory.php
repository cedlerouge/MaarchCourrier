<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Signature Book User Service Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure\Factory;

use MaarchCourrier\SignatureBook\Application\Group\UpdatePrivilegeGroupInSignatoryBook;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurGroupService;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureServiceJsonConfigLoader;

class UpdatePrivilegeGroupInSignatoryGroupFactory
{
    public function create(): UpdatePrivilegeGroupInSignatoryBook
    {
        $signatureBookGroup = new MaarchParapheurGroupService();
        $signatureBookConfigLoader = new SignatureServiceJsonConfigLoader();

        return new UpdatePrivilegeGroupInSignatoryBook(
            $signatureBookGroup,
            $signatureBookConfigLoader,
        );
    }
}
