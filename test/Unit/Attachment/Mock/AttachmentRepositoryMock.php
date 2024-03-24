<?php

declare(strict_types=1);

namespace MaarchCourrier\Tests\Unit\Attachment\Mock;

use MaarchCourrier\Core\Domain\Attachment\Port\AttachmentInterface;
use MaarchCourrier\Core\Domain\Attachment\Port\AttachmentRepositoryInterface;
use MaarchCourrier\Core\Domain\MainResource\Port\MainResourceInterface;

class AttachmentRepositoryMock implements AttachmentRepositoryInterface
{
    /**
     * @var AttachmentInterface[]
     */
    public array $incomingMails = [];

    /**
     * @var AttachmentInterface[]
     */
    public array $nonIncomingMails = [];

    /**
     * @var AttachmentInterface[]
     */
    public array $attachmentsInSignatureBook = [];

    /**
     * @param MainResourceInterface $mainResource
     *
     * @return AttachmentInterface[]
     */
    public function getIncomingMailByMainResource(MainResourceInterface $mainResource): array
    {
        return $this->incomingMails;
    }

    /**
     * @param MainResourceInterface $mainResource
     *
     * @return AttachmentInterface[]
     */
    public function getNonIncomingMailNotInSignatureBookByMainResource(MainResourceInterface $mainResource): array
    {
        return $this->nonIncomingMails;
    }

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachmentsInSignatureBookByMainResource(MainResourceInterface $mainResource): array
    {
        return $this->attachmentsInSignatureBook;
    }
}
