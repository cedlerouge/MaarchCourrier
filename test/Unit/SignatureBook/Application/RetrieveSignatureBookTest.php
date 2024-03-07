<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   Retrieve Signature Book Test
 * @author  dev@maarch.org
 */

namespace Unit\SignatureBook\Application;

use MaarchCourrier\Authorization\Domain\Problem\MainResourceOutOfPerimeterProblem;
use MaarchCourrier\Core\Domain\MainResource\Problem\ResourceDoesNotExistProblem;
use MaarchCourrier\SignatureBook\Application\RetrieveSignatureBook;
use MaarchCourrier\SignatureBook\Domain\ResourceAttached;
use MaarchCourrier\SignatureBook\Domain\ResourceToSign;
use MaarchCourrier\Tests\app\resource\Mock\ResourceDataMock;
use MaarchCourrier\Tests\Unit\Authorization\Mock\AccessControlServiceMock;
use MaarchCourrier\Tests\Unit\Authorization\Mock\MainResourceAccessControlServiceMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\CurrentUserInformationsMock;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\SignatureBookRepositoryMock;
use PHPUnit\Framework\TestCase;

class RetrieveSignatureBookTest extends TestCase
{
    private RetrieveSignatureBook $retrieveSignatureBook;
    private CurrentUserInformationsMock $currentUserInformationsMock;
    private AccessControlServiceMock $accessControlServiceMock;
    private MainResourceAccessControlServiceMock $mainResourceAccessControlServiceMock;
    private ResourceDataMock $resourceDataMock;
    private SignatureBookRepositoryMock $signatureBookRepositoryMock;

    protected function setUp(): void
    {
        $this->currentUserInformationsMock = new CurrentUserInformationsMock();
        $this->accessControlServiceMock = new AccessControlServiceMock();
        $this->mainResourceAccessControlServiceMock = new MainResourceAccessControlServiceMock();
        $this->resourceDataMock = new ResourceDataMock();
        $this->signatureBookRepositoryMock = new SignatureBookRepositoryMock();

        $this->retrieveSignatureBook = new RetrieveSignatureBook(
            $this->currentUserInformationsMock,
            $this->accessControlServiceMock,
            $this->mainResourceAccessControlServiceMock,
            $this->resourceDataMock,
            $this->signatureBookRepositoryMock
        );
    }

    /**
     * @throws ResourceDoesNotExistProblem
     * @throws MainResourceOutOfPerimeterProblem
     */
    public function testGetSignatureBookWhenUserHasNoRightAccessReturnAProblem(): void
    {
        $this->mainResourceAccessControlServiceMock->doesUserHasRight = false;
        $this->expectExceptionObject(new MainResourceOutOfPerimeterProblem());
        $this->retrieveSignatureBook->getSignatureBook(19, 1, 100);
    }

    public function testIfMainResourceDoesNotExistReturnAProblem(): void
    {
        $this->resourceDataMock->doesResourceExist = false;
        $this->expectExceptionObject(new ResourceDoesNotExistProblem());
        $this->retrieveSignatureBook->getSignatureBook(19, 1, 100);
    }

    public function testCannotUpdateDocumentsInSignatureBookIfBasketOrRedirectBasketParamIsDisableOrDoesNotExist(): void
    {
        $this->signatureBookRepositoryMock->isUpdateResourcesInSignatureBookBasket = false;
        $this->signatureBookRepositoryMock->isUpdateResourcesInSignatureBookRedirectBasket = false;

        $signatureBook = $this->retrieveSignatureBook->getSignatureBook(19, 1, 1);

        $this->assertNotEmpty($signatureBook->getResourcesToSign());
        $this->assertIsBool($signatureBook->isCanUpdateResources());
        $this->assertFalse($signatureBook->isCanUpdateResources());
    }

    public function testCanUpdateDocumentsInSignatureBookIfBasketParamIsEnable(): void
    {
        $signatureBook = $this->retrieveSignatureBook->getSignatureBook(19, 1, 1);

        $this->assertNotEmpty($signatureBook->getResourcesToSign());
        $this->assertIsBool($signatureBook->isCanUpdateResources());
        $this->assertTrue($signatureBook->isCanUpdateResources());
    }

    public function testCanUpdateDocumentsInSignatureBookIfUserDoesNotHaveBasketButHaveRedirectBasketAndParamIsEnable(): void
    {
        $this->signatureBookRepositoryMock->isUpdateResourcesInSignatureBookBasket = false;

        $signatureBook = $this->retrieveSignatureBook->getSignatureBook(19, 1, 1);

        $this->assertNotEmpty($signatureBook->getResourcesToSign());
        $this->assertIsBool($signatureBook->isCanUpdateResources());
        $this->assertTrue($signatureBook->isCanUpdateResources());
    }

    public function testGetSignatureBookWhenNoProblemOccurred(): void
    {
        $signatureBook = $this->retrieveSignatureBook->getSignatureBook(19, 1, 1);

        $this->assertNotEmpty($signatureBook->getResourcesToSign());
        $this->assertContainsOnlyInstancesOf(ResourceToSign::class, $signatureBook->getResourcesToSign());

        $this->assertNotEmpty($signatureBook->getResourcesAttached());
        $this->assertContainsOnlyInstancesOf(ResourceAttached::class, $signatureBook->getResourcesAttached());

        $this->assertIsBool($signatureBook->isCanSignResources());
        $this->assertIsBool($signatureBook->isCanUpdateResources());
        $this->assertIsBool($signatureBook->isHasActiveWorkflow());
        $this->assertIsBool($signatureBook->isCurrentWorkflowUser());
    }
}
