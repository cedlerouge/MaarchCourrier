<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Create And Update Group In Signatory Group Factory
 * @author dev@maarch.org
 */


namespace MaarchCourrier\SignatureBook\Infrastructure\Factory;

use MaarchCourrier\SignatureBook\Application\Group\CreateAndUpdateGroupInSignatoryBook;
use MaarchCourrier\SignatureBook\Infrastructure\MaarchParapheurGroupService;
use MaarchCourrier\SignatureBook\Infrastructure\SignatureServiceJsonConfigLoader;

class CreateAndUpdateGroupInSignatoryBookFactory
{
    /**
     * @return CreateAndUpdateGroupInSignatoryBook
     */
    public function create(): CreateAndUpdateGroupInSignatoryBook
    {
        $signatureBookGroup = new MaarchParapheurGroupService();
        $signatureBookConfigLoader = new SignatureServiceJsonConfigLoader();

        return new CreateAndUpdateGroupInSignatoryBook(
            $signatureBookGroup,
            $signatureBookConfigLoader,
        );
    }
}