<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace unitTests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use PHPUnit\Framework\TestCase;

class PastellServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfItIsEmpty(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = null;
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfUrlIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            '',
            'toto',
            'toto123',
            193,
            0,
            '',
            '',
            ''
        );
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfLoginIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            '',
            'toto123',
            193,
            0,
            '',
            '',
            ''
        );
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfPasswordIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            '',
            193,
            0,
            '',
            '',
            ''
        );
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfUrlIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->version = ['errors' => 'Erreur lors de la récupération de la version'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);


        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);

    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfEntityIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            0,
            0,
            '',
            '',
            ''
        );
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfEntityIsNotFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->entity = ['192', '42', '813'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsValidIfEntityIsFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfConnectorIsNotFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->entity = ['192', '193', '813'];
        $pastellApiMock->connector = ['34', '245', '813'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsValidIfConnectorIsFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->entity = ['192', '193', '813'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfDocumentTypeIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            '',
            '',
            ''
        );
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfDocumentTypeIsNotFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            'ls-not-document-pdf',
            'XELIANS COURRIER',
            ''
        );
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsValidIfDocumentTypeIsFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfIparapheurTypeIsMissing(): void
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
            '',
            ''
        );
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsNotValidIfIparapheurTypeIsNotFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            0,
            0,
            'ls-not-document-pdf',
            'PELIANS COURRIER',
            ''
        );
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testConfigurationIsValidIfIparapheurTypeIsFoundInPastell(): void
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
            ''
        );
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }
}
