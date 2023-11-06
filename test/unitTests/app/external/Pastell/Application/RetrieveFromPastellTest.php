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
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ParseIParapheurLogMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\ProcessVisaWorkflowSpy;
use PHPUnit\Framework\TestCase;

class RetrieveFromPastellTest extends TestCase
{
    private PastellApiMock $pastellApiMock;
    private PastellConfigMock $pastellConfigMock;
    private ParseIParapheurLogMock $parseIParapheurLogMock;
    private RetrieveFromPastell $retrieveFromPastell;

    protected function setUp(): void
    {
        $this->pastellApiMock = new PastellApiMock();
        $processVisaWorkflowSpy = new ProcessVisaWorkflowSpy();
        $this->pastellConfigMock = new PastellConfigMock();
        $pastellConfigurationCheck = new PastellConfigurationCheck($this->pastellApiMock, $this->pastellConfigMock);

        $this->parseIParapheurLogMock = new ParseIParapheurLogMock(
            $this->pastellApiMock,
            $this->pastellConfigMock,
            $pastellConfigurationCheck,
            $processVisaWorkflowSpy
        );

        $this->retrieveFromPastell = new RetrieveFromPastell(
            $this->pastellApiMock,
            $this->pastellConfigMock,
            $pastellConfigurationCheck,
            $this->parseIParapheurLogMock
        );
    }

    /**
     * @return void
     */
    public function testRetrieveOneResourceNotFoundAndOneSigned(): void
    {
        $this->pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];

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

        $result = $this->retrieveFromPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                42 => [
                    'res_id'      => 42,
                    'external_id' => 'djqfdh',
                    'status'      => 'validated',
                    'format'      => 'pdf',
                    'encodedFile' => 'toto'
                ],
            ],
            $result['success']
        );
        $this->assertSame(
            [
                12 => 'Error when getting folder detail : An error occurred !'
            ],
            $result['error']
        );
    }

    /**
     * @return void
     */
    public function testRetrieveOneResourceFoundButNotFinishOneSignedAndOneRefused(): void
    {
        $this->pastellApiMock->documentsDownload = [
            'encodedFile' => 'toto'
        ];

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

        $result = $this->retrieveFromPastell->retrieve($idsToRetrieve);

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
            $result['success']
        );
    }

    /**
     * @return void
     */
    public function testWhenVerificationFailedForAResourceWeRetrieveTheErrorAndTheOtherResources(): void
    {
        $this->pastellApiMock->verificationIparapheurFailedId = 'testKO';

        $idsToRetrieve = [
            420 => [
                'res_id'      => 420,
                'external_id' => 'testKO'
            ],
            42  => [
                'res_id'      => 42,
                'external_id' => 'djqfdh'
            ]
        ];

        $result = $this->retrieveFromPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                'success' => [
                    42 => [
                        'res_id'      => 42,
                        'external_id' => 'djqfdh',
                        'status'      => 'validated',
                        'format'      => 'pdf',
                        'encodedFile' => 'toto'
                    ]
                ],
                'error'   => [
                    420 => 'Action "verif-iparapheur" failed'
                ],
            ],
            $result
        );
    }

    /**
     * @return void
     */
    public function testWhenParsingTheHistoryFailedForAResourceWeRetrieveTheErrorAndTheOtherResources(): void
    {
        $this->parseIParapheurLogMock->errorResId = 420;

        $idsToRetrieve = [
            420 => [
                'res_id'      => 420,
                'external_id' => 'testKO'
            ],
            42  => [
                'res_id'      => 42,
                'external_id' => 'djqfdh'
            ]
        ];

        $result = $this->retrieveFromPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                'success' => [
                    42 => [
                        'res_id'      => 42,
                        'external_id' => 'djqfdh',
                        'status'      => 'validated',
                        'format'      => 'pdf',
                        'encodedFile' => 'toto'
                    ]
                ],
                'error'   => [
                    420 => 'Could not parse log'
                ],
            ],
            $result
        );
    }

    /**
     * @return void
     */
    public function testCannotRetrieveResourcesFromPastellIfConfigurationIsNotValid(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            '',
            '',
            '',
            0,
            0,
            '',
            '',
            ''
        );

        $idsToRetrieve = [
            420 => [
                'res_id'      => 420,
                'external_id' => 'testKO'
            ],
            42  => [
                'res_id'      => 42,
                'external_id' => 'djqfdh'
            ]
        ];

        $result = $this->retrieveFromPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                'success' => [],
                'error'   => 'Cannot retrieve resources from pastell : pastell configuration is invalid',
            ],
            $result
        );
    }

    /**
     * @return void
     */
    public function testRetrievingAttachmentUseResIdMaster(): void
    {
        $this->parseIParapheurLogMock->errorResId = 420;

        $idsToRetrieve = [
            43 => [
                'res_id'        => 43,
                'external_id'   => 'testKO',
                'res_id_master' => 420
            ],
            40 => [
                'res_id'        => 40,
                'external_id'   => 'djqfdh',
                'res_id_master' => 42
            ]
        ];

        $result = $this->retrieveFromPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                'success' => [
                    40 => [
                        'res_id'        => 40,
                        'external_id'   => 'djqfdh',
                        'res_id_master' => 42,
                        'status'        => 'validated',
                        'format'        => 'pdf',
                        'encodedFile'   => 'toto'
                    ]
                ],
                'error'   => [
                    43 => 'Could not parse log'
                ],
            ],
            $result
        );
    }

    /**
     * @return void
     */
    public function testRetrieveIsNotValidWhenDeleteFolderReturnsAnError(): void
    {
        $this->pastellApiMock->deletedFolder = ['error' => 'An error occurred !'];

        $idsToRetrieve = [
            42  => [
                'res_id'      => 42,
                'external_id' => 'djqfdh',
                'status' => 'validated'
            ]
        ];

        $result = $this->retrieveFromPastell->retrieve($idsToRetrieve);

        $this->assertSame(
            [
                'error'   => 'An error occurred !',
            ], $result);
    }
}
