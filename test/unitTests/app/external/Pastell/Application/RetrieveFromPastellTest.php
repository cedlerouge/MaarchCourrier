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
use ExternalSignatoryBook\pastell\Application\RetrieveFromPastell;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ParseIParapheurLogMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use PHPUnit\Framework\TestCase;

class RetrieveFromPastellTest extends TestCase
{

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

    public function testRetrieveOneResourceFoundButNotFinishOneSignedAndOneRefused(): void
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
            12  => [
                'res_id'      => 12,
                'external_id' => 'bloblo'
            ],
            42  => [
                'res_id'      => 42,
                'external_id' => 'djqfdh'
            ],
            152 => [
                'res_id'      => 152,
                'external_id' => 'chuchu'
            ]
        ];

        $result = $retrieveToPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                12  => [
                    'res_id'      => 12,
                    'external_id' => 'bloblo',
                    'status'      => 'waiting',
                ],
                42  => [
                    'res_id'      => 42,
                    'external_id' => 'djqfdh',
                    'status'      => 'validated',
                    'format'      => 'pdf',
                    'encodedFile' => 'toto'
                ],
                152 => [
                    'res_id'      => 152,
                    'external_id' => 'chuchu',
                    'status'      => 'refused',
                    'content'     => 'Un nom : une note'
                ]
            ],
            $result
        );
    }

    // ajout de test pour tester quand vérif est false
    public function testRetrieveReturnAnErrorWhenVerifIParapheurIsNotTrue(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->verificationIparapheur = false;
        $processVisaWorkflow = new ProcessVisaWorkflowSpy();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $parseIParapheurLog = new ParseIParapheurLogMock($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow);
        $retrieveToPastell = new RetrieveFromPastell($pastellApiMock, $pastellConfigMock, $pastellConfigCheck, $processVisaWorkflow, $parseIParapheurLog);
        $idsToRetrieve = [
            12 => [
                'res_id'      => 12,
                'external_id' => 'test'
            ]
        ];

        $result = $retrieveToPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            ['error' => 'L\'action « verif-iparapheur »  n\'est pas permise : Le dernier état du document (termine) ne permet pas de déclencher cette action'],
            $result
        );
    }
}
