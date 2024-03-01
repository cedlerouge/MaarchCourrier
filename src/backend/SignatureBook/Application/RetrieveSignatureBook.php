<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief continueCircuitAction class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Application;

use MaarchCourrier\Authorization\Domain\Problem\MainResourceOutOfPerimeterException;
use MaarchCourrier\Core\Domain\MainResource\MainResourceAccessControlInterface;
use MaarchCourrier\Core\Domain\Port\CurrentUserInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBook;

class RetrieveSignatureBook
{
    public function __construct(
        public CurrentUserInterface $currentUser,
        public MainResourceAccessControlInterface $mainResourceAccessControl
    ) {

    }

    /**
     * @throws MainResourceOutOfPerimeterException
     */
    public function getSignatureBook(int $userId, int $basketId, int $resId): array
    {
        if (!$this->mainResourceAccessControl->hasRightByResId($resId, $this->currentUser)) {
            throw new MainResourceOutOfPerimeterException();
        }
        return [];
    }
}
