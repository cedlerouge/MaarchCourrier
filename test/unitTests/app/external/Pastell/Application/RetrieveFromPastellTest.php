<?php

namespace MaarchCourrier\Tests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\RetrieveFromPastell;
use ExternalSignatoryBook\pastell\Domain\PastellStates;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use PHPUnit\Framework\TestCase;

class RetrieveFromPastellTest extends TestCase
{
    public function testRetrieveToPastellIsNotValidIfResIdsIsNotFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resIds = [1, 2, 42, 60];
        $idFolder = 'dsfq';


        $result = $retrieveToPastell->retrieve($resIds, $idFolder);

        $this->assertEmpty($result);

    }

    /*public function testIfOneResIdIsFoundInTheMiddleOfTheList(): void
    {
        $pastellApiMock = new PastellApiMock();
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resIds = [1, 2, 42, 60];


        $result = $retrieveToPastell->retrieve($resIds);

        $this->assertNotEmpty($result);
        $this->assertSame([42], $result);

    }*/

    public function testHandleValidateRetrieveTheFileInBase64(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $result = $retrieveToPastell->handleValidate($resId, $idFolder, false);

        $this->assertNotEmpty($result);
        $this->assertSame(
            [
                'status'      => 'validated',
                'format'      => 'pdf',
                'encodedFile' => 'toto'
            ],
            $result
        );
    }


    public function testHandleValidateVisaWorkFlowIsCalledIfIsSigned(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];

        $processVisaWorkflow = new ProcessVisaWorkflowSpy();

        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $retrieveToPastell->handleValidate($resId, $idFolder, true);

        $this->assertTrue($processVisaWorkflow->processVisaWorkflowCalled);
    }

    public function testHandleValidateVisaWorkFlowIsCalledIfIsNotSigned(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];

        $processVisaWorkflow = new ProcessVisaWorkflowSpy();

        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $retrieveToPastell->handleValidate($resId, $idFolder, false);

        $this->assertFalse($processVisaWorkflow->processVisaWorkflowCalled);
    }
    public function testHandleValidateTheDownloadFileReturnAnError(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'error' => 'Je suis ton erreur'
        ];

        $processVisaWorkflow = new ProcessVisaWorkflowSpy();

        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $result = $retrieveToPastell->handleValidate($resId, $idFolder, true);

        $this->assertSame(['error' => 'Je suis ton erreur'], $result);
    }

    public function testHandleRefusedRetrieveTheNoteContent(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->LogDossier = [''];
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $result = $retrieveToPastell->handleRefused($resId, $idFolder);

        $this->assertNotEmpty($result);
        $this->assertSame(
            [
                'status'      => 'refused',
                'content'      => 'blahblahblah',
            ],
            $result
        );
    }
    public function testParseLogIparapheurReturnCodeIsAnError(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'KO';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $retrieveToPastell->parseLogIparapheur($resId, $idFolder);

        $this->assertSame(['error' => '[ Log KO in iParapheur : ]'], $result);
    }



}
