<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\Application;

use ExternalSignatoryBook\Application\DocumentLink;
use ExternalSignatoryBook\Domain\Exceptions\ParameterCanNotBeEmptyException;
use ExternalSignatoryBook\Domain\Exceptions\ParameterMustBeGreaterThanZeroException;
use MaarchCourrier\Tests\CourrierTestCase;
use unitTests\app\external\signatoryBook\Mock\AttachmentRepositoryMock;
use unitTests\app\external\signatoryBook\Mock\HistoryRepositoryMock;
use unitTests\app\external\signatoryBook\Mock\ResourceRepositoryMock;
use unitTests\app\external\signatoryBook\Mock\UserRepositoryMock;

class DocumentLinkTest extends CourrierTestCase
{
    private ResourceRepositoryMock $resourceRepositoryMock;
    private AttachmentRepositoryMock $attachmentRepositoryMock;
    private UserRepositoryMock $userRepositoryMock;
    private HistoryRepositoryMock $historyRepositoryMock;
    private DocumentLink $documentLink;

    protected function setUp(): void
    {
        $this->resourceRepositoryMock = new ResourceRepositoryMock();
        $this->attachmentRepositoryMock = new AttachmentRepositoryMock();
        $this->userRepositoryMock = new UserRepositoryMock();
        $this->historyRepositoryMock = new HistoryRepositoryMock();

        $this->documentLink = new DocumentLink(
            $this->userRepositoryMock,
            $this->resourceRepositoryMock,
            $this->attachmentRepositoryMock,
            $this->historyRepositoryMock
        );
    }

    public function testCannotRemoveDocumentLinkBecauseDocumentItemResId0()
    {
        $this->expectExceptionObject(new ParameterMustBeGreaterThanZeroException('docItemResId'));

        $this->documentLink->removeExternalLink(0, '', '', '');
    }

    public function testCannotRemoveDocumentLinkBecauseDocumentTitleIsEmpty()
    {
        $this->expectExceptionObject(new ParameterCanNotBeEmptyException('docItemTitle'));

        $this->documentLink->removeExternalLink(1, '', '', '');
    }

    public function testCannotRemoveDocumentLinkBecauseDocumentTypeIsEmpty()
    {
        $this->expectExceptionObject(new ParameterCanNotBeEmptyException('type'));

        $this->documentLink->removeExternalLink(1, 'Document from external parapheur', '', '');
    }

    public function testCannotRemoveDocumentLinkBecauseDocumentTypeIsNotResourceOrAttachment()
    {
        $this->expectExceptionObject(new ParameterCanNotBeEmptyException('type', implode(' or ', ['resource', 'attachment'])));

        $this->documentLink->removeExternalLink(1, 'Document from external parapheur', 'test', '');
    }

    public function testCannotRemoveDocumentLinkBecauseDocumentItemExternalIdIsEmpty()
    {
        $this->expectExceptionObject(new ParameterCanNotBeEmptyException('docItemExternalId'));

        $this->documentLink->removeExternalLink(1, 'Document from external parapheur', 'resource', '');
    }

    public function testRemoveResourceDocumentLinkSuccessfully()
    {
        $this->expectNotToPerformAssertions();
        $this->documentLink->removeExternalLink(1, 'Document from external parapheur', 'resource', '1234');
    }

    public function testRemoveAttachmentDocumentLinkSuccessfully()
    {
        $this->expectNotToPerformAssertions();
        $this->documentLink->removeExternalLink(1, 'Document from external parapheur', 'attachment', '1234');
    }
}
