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
     * Testing when configuration is empty
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
     * Testing when Pastell API URL is missing in config
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
     * Testing when API login is missing in config
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
     * Testing when API password is missing in config
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
     * Testing a wrong Pastell API URL
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
     * Testing when entity is missing in config
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
     * Testing wrong entities
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
     * Testing non-valid entities
     * @return void
     */
    public function testConfigurationIsNotValidIfEntityIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->entity = ['errors' => 'Erreur lors de la récupération des entités'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing with the right entity
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
     * Testing when connector is missing in config
     * @return void
     */
    public function testConfigurationIsNotValidIfConnectorIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
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
     * Testing with a bad connector
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
     * Testing with a non-valid connector
     * @return void
     */
    public function testConfigurationIsNotValidIfConnectorIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->connector = ['errors' => 'Erreur lors de la récupération des connecteurs'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing with the right connector
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
     * Testing when document type is missing in config
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
     * Testing when a bad document type
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
     * Testing with a non-valid document type
     * @return void
     */
    public function testConfigurationIsNotValidIfDocumentTypeIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->flux = ['errors' => 'Erreur lors de la récupération des types de documents'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing with the right connector
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
     * Testing conf without iParapheur type
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
     * Testing conf with iParapheur type not in Pastell conf
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
            193,
            776,
            'ls-document-pdf',
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
    public function testConfigurationIsNotValidIfIparapheurTypeIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->iParapheurType = ['errors' => 'Erreur lors de la récupération des types de iParapheur'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing conf with the right iParapheur type
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
