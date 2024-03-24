<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Attachment Repository Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Core\Domain\Attachment\Port;

use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceInterface;

interface AttachmentRepositoryInterface
{
    /**
     * @param MainResourceInterface $mainResource
     * @return AttachmentInterface[]
     */
    public function getIncomingMailByMainResource(MainResourceInterface $mainResource): array;

    /**
     * @param MainResourceInterface $mainResource
     * @return AttachmentInterface[]
     */
    public function getNonIncomingMailNotInSignatureBookByMainResource(MainResourceInterface $mainResource): array;

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachmentsInSignatureBookByMainResource(MainResourceInterface $mainResource): array;
}
