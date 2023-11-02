<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\RetrieveFromPastell;
use ExternalSignatoryBook\pastell\Application\SendToPastell;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ParseIParapheurLogMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ResourceDataMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ResourceFileMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\SendToPastellSpy;
use PHPUnit\Framework\TestCase;

class SendToPastellTest extends TestCase
{
    /**
     * Test sendData when folder created
     * @return void
     */
    public function testSendDataReturnsIdFolderWhenCreated(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $result = $sendToPastell->sendData(42);

        $this->assertSame(
            ['sended' => [
                'letterbox_coll' => [
                    42 => 'hfqvhv' ?? null
                ]]
            ], $result);
    }

    /**
     * Test sendData failed when id folder is missing
     * @return void
     */
    public function testSendDataReturnsAnErrorWhenIdFolderIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->folder = ['error' => 'No folder ID retrieved from Pastell'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $result = $sendToPastell->sendData(42);

        $this->assertSame([
            'error' => 'No folder ID retrieved from Pastell'
        ], $result);
    }

    /**
     * Testing conf when folder ID is not valid
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

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

    /**
     * Test sendResource when main file extension is not PDF
     * @return void
     */
    public function testSendResourceReturnsErrorWhenMainFileExtensionIsNotPDF(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $resourceFile->adrMainInfo = 'Error';
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

        $this->assertSame(['error' => 'Document ' . $resId . ' is not converted in pdf'], $result);
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }

    /**
     * Test sending datas when iParapheur subtype returns an error
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred !'], $result);
    }

    /**
     * Test sending datas when iParapheur subtype not found
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'Subtype does not exist in iParapheur'], $result);
    }

    /**
     * Test sending datas when iParapheur subtype found
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }

    /**
     * Test sending datas failed when edit folder failed
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 42;
        $title = '';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred'], $result);
    }

    /**
     * Test sending datas failed when uploading main file failed
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 42;
        $title = 'blablabla';
        $sousType = 'courrier';
        $filePath = '';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred'], $result);
    }

    /**
     * Test sending datas failed when orientation action failed
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
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 42;
        $title = '';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(['error' => 'An error occurred'], $result);
    }

    /**
     * Test sending datas failed when send-iparapheur action failed
     * @return void
     */
    public function testSendToPastellIsNotSentIfSendIparapheurIsNotTrue(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellApiMock->documentDetails['actionPossibles'] = ['send-iparapheur'];
        $pastellApiMock->sendIparapheur = false;
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 0;
        $title = '';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath);

        $this->assertSame(
            ['error' => 'L\'action « send-iparapheur »  n\'est pas permise : Le dernier état du document (send-iparapheur) ne permet pas de déclencher cette action'],
            $result
        );
    }

    public function testNonSignableAttachementIsSentAsAnAnnex(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellApiMock->documentDetails['actionPossibles'] = ['send-iparapheur'];
        $pastellApiMock->sendIparapheur = false;
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();

        $resourceData->attachmentTypes = [
            'type_signable'     => [
                'signable' => true
            ],
            'type_not_signable' => [
                'signable' => false
            ]
        ];
        $resourceData->attachments = [
            [
                'res_id'          => 1,
                'attachment_type' => 'type_not_signable',
                'fingerprint'     => 'azerty'
            ],
            [
                'res_id'          => 2,
                'attachment_type' => 'type_signable',
                'fingerprint'     => 'azerty'
            ]
        ];

        $resourceFile = new ResourceFileMock();
        $resourceFile->attachmentFilePath = '/path/to/attachment.pdf';
        $sendToPastell = new SendToPastellSpy(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 0;
        $sousType = 'courrier';

        $sendToPastell->sendResource($resId, $sousType);

        $this->assertSame(
            [
                '/path/to/attachment.pdf'
            ],
            $sendToPastell->annexes
        );
    }

    public function testPastellIsCalledForEveryAnnexUploaded(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellApiMock->documentDetails['actionPossibles'] = ['send-iparapheur'];
        $pastellApiMock->sendIparapheur = false;
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $resId = 0;
        $title = '';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';
        $annexes = [
            '/path/to/attachment1.pdf',
            '/path/to/attachment2.pdf',
        ];

        $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath, $annexes);

        $this->assertSame(
            [
                [
                    'nb' => 0,
                    'filePath' => '/path/to/attachment1.pdf'
                ],
                [
                    'nb' => 1,
                    'filePath' => '/path/to/attachment2.pdf'
                ]
            ],
            $pastellApiMock->uploadedAnnexes
        );
    }

    public function testWhenAnnexUploadFailsWeUploadTheFolderAnyway(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $resourceData = new ResourceDataMock();
        $resourceFile = new ResourceFileMock();
        $sendToPastell = new SendToPastell(
            $pastellConfigCheck,
            $pastellApiMock,
            $pastellConfigMock,
            $resourceData,
            $resourceFile,
            $processVisaWorkflow
        );

        $pastellApiMock->uploadAnnexError = ['error' => 'Error uploading annex'];

        $resId = 0;
        $title = '';
        $sousType = 'courrier';
        $filePath = '/test/toto.pdf';
        $annexes = [
            '/path/to/attachment1.pdf',
            '/path/to/attachment2.pdf',
        ];

        $result = $sendToPastell->sendFolderToPastell($resId, $title, $sousType, $filePath, $annexes);

        $this->assertSame([], $pastellApiMock->uploadedAnnexes);
        $this->assertSame(['idFolder' => 'hfqvhv'], $result);
    }
}
