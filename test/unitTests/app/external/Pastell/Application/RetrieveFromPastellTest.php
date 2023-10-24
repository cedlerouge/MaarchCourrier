<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\ParseIParapheurLog;
use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\RetrieveFromPastell;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ParseIParapheurLogMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use PHPUnit\Framework\TestCase;

class RetrieveFromPastellTest extends TestCase
{
//    public function testRetrieveToPastellIsNotValidIfResIdsIsNotFoundInPastell(): void
//    {
//        $pastellApiMock = new PastellApiMock();
//        $pastellConfigMock = new PastellConfigMock();
//        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
//        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
//        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
//        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
//        $resIds = [1, 2, 42, 60];
//        $idFolder = 'dsfq';
//
//
//        $result = $retrieveToPastell->retrieve($resIds, $idFolder);
//
//        $this->assertEmpty($result);
//
//    }

    /*public function testIfOneResIdIsFoundInTheMiddleOfTheList(): void
    {
        $pastellApiMock = new PastellApiMock();
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resIds = [1, 2, 42, 60];


        $result = $retrieveToPastell->retrieve($resIds);

        $this->assertNotEmpty($result);
        $this->assertSame([42], $result);

    }*/

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
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
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
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resId = 42;
        $idFolder = 'djqfdh';

        $retrieveToPastell->handleValidate($resId, $idFolder, true);

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
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resId = 42;
        $idFolder = 'djqfdh';

        $retrieveToPastell->handleValidate($resId, $idFolder, false);

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
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $retrieveToPastell->handleValidate($resId, $idFolder, true);

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
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);

        $result = $retrieveToPastell->handleRefused('Un nom', 'une note');

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
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $retrieveToPastell->parseLogIparapheur($resId, $idFolder);

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
        $pastellApiMock->journalXml->LogDossier = [new \stdClass(), new \stdClass()];
        $pastellApiMock->journalXml->LogDossier[0]->status = 'toto';
        $pastellApiMock->journalXml->LogDossier[1]->status = $state;
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'OK';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $retrieveToPastell = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $retrieveToPastell->parseLogIparapheur($resId, $idFolder);

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
     * @return void
     */
    // TODO visa / sign -> dataProvider
    public function testParseLogIparapheurDocumentIsRefused(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->LogDossier = [new \stdClass(), new \stdClass()];
        $pastellApiMock->journalXml->LogDossier[0]->status = 'toto';
        $pastellApiMock->journalXml->LogDossier[1]->status = 'RejetSignataire';
        $pastellApiMock->journalXml->LogDossier[1]->nom = 'Nom';
        $pastellApiMock->journalXml->LogDossier[1]->annotation = 'annotation';
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'OK';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $retrieveToPastell->parseLogIparapheur($resId, $idFolder);

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
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $pastellApiMock->journalXml = new \stdClass();
        $pastellApiMock->journalXml->LogDossier = [new \stdClass(), new \stdClass()];
        $pastellApiMock->journalXml->LogDossier[0]->status = 'toto';
        $pastellApiMock->journalXml->LogDossier[1]->status = 'blabla';
        $pastellApiMock->journalXml->MessageRetour = new \stdClass();
        $pastellApiMock->journalXml->MessageRetour->codeRetour = 'OK';
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLog($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $resId = 42;
        $idFolder = 'djqfdh';

        $result = $retrieveToPastell->parseLogIparapheur($resId, $idFolder);

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
    public function testRetrieveOneResourceNotFoundAndOneSigned(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLogMock($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $idsToRetrieve = [
            12 => [
                'res_id'      => 12,
                'external_id' => 'blabla'
            ],
            42 => [
                'res_id'      => 42,
                'external_id' => 'djqfdh'
            ]
        ];

        $result = $retrieveToPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                12 => [
                    'res_id'      => 12,
                    'external_id' => 'blabla',
                    'status'      => 'waiting',
                ],
                42 => [
                    'res_id'      => 42,
                    'external_id' => 'djqfdh',
                    'status'      => 'validated',
                    'format'      => 'pdf',
                    'encodedFile' => 'toto'
                ],
            ],
            $result
        );
    }
}
