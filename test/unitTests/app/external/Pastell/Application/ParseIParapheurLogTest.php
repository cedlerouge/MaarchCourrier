<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace unitTests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\ParseIParapheurLog;
use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use PHPUnit\Framework\TestCase;

class ParseIParapheurLogTest extends TestCase
{
    /**
     * @return void
     */
    public function testParseLogIparapheurReturnCodeIsAnError(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'KO';
        $pastellApiMock->journalXml->MessageRetour->severite = 'INFO';
        $pastellApiMock->journalXml->MessageRetour->message = 'error';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $parseIParapheurLog->parseLogIparapheur($resId, $idFolder);

        $this->assertSame(['error' => 'Log KO in iParapheur : [INFO] error'], $result);
    }

    /**
     * @return array[]
     */
    public function validatedStateProvider(): array
    {
        return [
            'visa' => ['VisaOK'],
            'sign' => ['CachetOK'],
        ];
    }

    /**
     * @dataProvider validatedStateProvider
     */
    public function testParseLogIparapheurDocumentIsValidated(string $state): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->LogDossier = new \stdClass();
        $pastellApiMock->journalXml->LogDossier->LogDossier = [new \stdClass(), new \stdClass()];
        $pastellApiMock->journalXml->LogDossier->LogDossier[0]->status = 'toto';
        $pastellApiMock->journalXml->LogDossier->LogDossier[1]->status = $state;
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'OK';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $parseIParapheurLog->parseLogIparapheur($resId, $idFolder);

        $this->assertSame(
            [
                'status'      => 'validated',
                'format'      => 'pdf',
                'encodedFile' => 'toto'
            ],
            $result
        );
    }

    /**
     * @return array[]
     */
    public function refusedStateProvider(): array
    {
        return [
            'visa' => ['RejetVisa'],
            'sign' => ['RejetSignataire']
        ];
    }

    /**
     * @dataProvider refusedStateProvider
     */
    public function testParseLogIparapheurDocumentIsRefused(string $state): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->LogDossier = new \stdClass();
        $pastellApiMock->journalXml->LogDossier->LogDossier = [new \stdClass(), new \stdClass()];
        $pastellApiMock->journalXml->LogDossier->LogDossier[0]->status = 'toto';
        $pastellApiMock->journalXml->LogDossier->LogDossier[1]->status = $state;
        $pastellApiMock->journalXml->LogDossier->LogDossier[1]->nom = 'Nom';
        $pastellApiMock->journalXml->LogDossier->LogDossier[1]->annotation = 'annotation';
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'OK';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $parseIParapheurLog->parseLogIparapheur($resId, $idFolder);

        $this->assertSame(
            [
                'status'  => 'refused',
                'content' => 'Nom : annotation',
            ],
            $result
        );
    }

    /**
     * @return void
     */
    public function testParseLogIparapheurDocumentIsNotRefusedAndNotValidated(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->LogDossier = new \stdClass();
        $pastellApiMock->journalXml->LogDossier->LogDossier = [new \stdClass(), new \stdClass()];
        $pastellApiMock->journalXml->LogDossier->LogDossier[0]->status = 'toto';
        $pastellApiMock->journalXml->LogDossier->LogDossier[1]->status = 'blabla';
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'OK';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $parseIParapheurLog->parseLogIparapheur($resId, $idFolder);

        $this->assertSame(
            [
                'status' => 'waiting',
            ],
            $result
        );
    }

    public function testParseLogIparapheurXmlDetailReturnAnError(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->error = 'Erreur';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $parseIParapheurLog->parseLogIparapheur($resId, $idFolder);

        $this->assertSame(
            [
                'status' => 'waiting',
            ],
            $result
        );
    }

    /**
     * @return void
     */
    public function testHandleValidateVisaWorkFlowIsCalledIfIsSigned(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $parseIParapheurLog->handleValidate($resId, $idFolder, true);

        $this->assertTrue($processVisaWorkflow->processVisaWorkflowCalled);
    }

    /**
     * @return void
     */
    public function testHandleValidateVisaWorkFlowIsCalledIfIsNotSigned(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $parseIParapheurLog->handleValidate($resId, $idFolder, false);

        $this->assertFalse($processVisaWorkflow->processVisaWorkflowCalled);
    }

    /**
     * @return void
     */
    public function testHandleValidateTheDownloadFileReturnAnError(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'error' => 'Je suis ton erreur'
        ];
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $result = $parseIParapheurLog->handleValidate($resId, $idFolder, true);

        $this->assertSame(['error' => 'Je suis ton erreur'], $result);
    }

    /**
     * @return void
     */
    public function testHandleRefusedRetrieveTheNoteContent(): void
    {
        $pastellApiMock = new PastellApiMock();
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);

        $result = $parseIParapheurLog->handleRefused('Un nom', 'une note');

        $this->assertNotEmpty($result);
        $this->assertSame(
            [
                'status'  => 'refused',
                'content' => 'Un nom : une note',
            ],
            $result
        );
    }
    /**
     * @return void
     */
    public function testHandleValidateRetrieveTheFileInBase64(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $resId = 42;
        $idFolder = 'djqfdh';


        $result = $parseIParapheurLog->handleValidate($resId, $idFolder, false);

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
}
