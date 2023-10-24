<?php

namespace MaarchCourrier\Tests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\ParseIParapheurLog;
use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\RetrieveFromPastell;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use PHPUnit\Framework\TestCase;

class ParseIParapheurLogTest extends TestCase
{
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

    public function testParseLogIparapheurDocumentIsNotRefusedAndNotValidated(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];
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

}
