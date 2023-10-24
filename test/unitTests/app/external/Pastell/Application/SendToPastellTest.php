<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace unitTests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\SendToPastell;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ResourceDataMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ResourceFileMock;
use PHPUnit\Framework\TestCase;

class SendToPastellTest extends TestCase
{
    /**
     * Testing when a folder is created returns an idFolder
     * @return void
     */
    public function testSendFolderReturnsIdFolderWhenCreated(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $result = $sendToPastell->sendFolderToPastell(42, 'Toto', 'courrier', '/opt/my-document.pdf');

        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }

    // TODO cas d'erreurs retour d'api
//    public function testSendFolderReturnsIdFolderWhenCreated(): void
//    {
//        $pastellApiMock = new PastellApiMock();
//        $pastellApiMock->documentsDownload = [
//            'encodedFile' => 'toto'
//        ];
//        $pastellConfigMock = new PastellConfigMock();
//        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
//
//        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);
//
//        $result = $sendToPastell->sendFolderToPastell('Toto', 'courrier', '/opt/my-document.pdf');
//
//        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
//    }

    /**
     * Testing when data is sent to a folder returns an idFolder
     * @return void
     */
    public function testSendResourceReturnsIdFolderWhenCreated(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $result = $sendToPastell->sendResource(42, 'courrier');

        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }
}
