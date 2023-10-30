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
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ResourceDataMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ResourceFileMock;
use PHPUnit\Framework\TestCase;

class SendToPastellTest extends TestCase
{
    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfIdFolderIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->folder = ['error' => 'Erreur lors de la récupération de l\'id du dossier'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'Erreur lors de la récupération de l\'id du dossier'], $result);
    }

    /**
     * Testing when a folder is created returns an idFolder
     * @return void
     */
    public function testSendFolderReturnsIdFolderWhenCreated(): void
    {
        $pastellApiMock = new PastellApiMock();
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

    /**
     * Testing when data is sent to a folder returns an idFolder
     * @return void
     */
    public function testSendResourceReturnsIdFolderWhenCreated(): void
    {
        $pastellApiMock = new PastellApiMock();
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
        $resId = 42;
        $sousType = 'courrier';

        $result = $sendToPastell->sendResource($resId, $sousType);

        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }

    public function testSendResourceReturnsErrorWhenMainFileExtensionIsNotPDF(): void
    {
        $pastellApiMock = new PastellApiMock();
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
        $resId = 42;
        $adrMainInfo['filename'] = 'toto.txt';
        $resourceFile->adrMainInfo = 'Error: Document ' . $resId . ' is not converted in pdf';

        $result = $resourceFile->getMainResourceFilePath($resId);

        $this->assertSame('Error: Document ' . $resId . ' is not converted in pdf', $result);
    }

    /**
     * Testing sending datas if id folder is missing
     * @return void
     */
    public function testSendToPastellIsNotValidIfIdFolderIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->folder = [];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'Folder creation has failed'], $result);
    }

    /**
     * Testing sending datas with the right id folder
     * @return void
     */
    public function testSendToPastellIsValidIfIdFolderIsNotMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }

    /**
     * @return void
     */
    public function testSendToPastellIsNotValidIfIparapheurSousTypeReturnAnError(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->iParapheurSousType = ['error' => 'An error occurred !'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred !'], $result);
    }

    /**
     * @return void
     */
    public function testSendToPastellIsNotValidIfIparapheurSousTypeIsNotFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            'ls-document-pdf',
            'XELIANS COURRIER',
            'courrrier'
        );
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'Subtype does not exist in iParapheur'], $result);
    }

    /**
     * @return void
     */
    public function testSendToPastellIsValidIfIparapheurSousTypeIsFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }

    /**
     * @return void
     */
    public function testSendToPastellIsNotSentIfEditFolderFailed(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellApiMock->dataFolder = ['error' => 'An error occurred'];
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = '';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred'], $result);
    }

    /**
     * @return void
     */
    public function testSendToPastellIsNoSentIfUploadingMainFileFailed(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellApiMock->mainFile = ['error' => 'An error occurred'];
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred'], $result);
    }

    /**
     * @return void
     */
    public function testSendToPastellIsNotSentIfOrientationFailed(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellApiMock->orientation = ['error' => 'An error occurred'];
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock, $resourceData, $resourceFile, $processVisaWorkflow);

        $resId = 42;
        $title = '';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred'], $result);
    }
}
