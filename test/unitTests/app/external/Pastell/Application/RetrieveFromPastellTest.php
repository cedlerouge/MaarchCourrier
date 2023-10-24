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
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);

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
