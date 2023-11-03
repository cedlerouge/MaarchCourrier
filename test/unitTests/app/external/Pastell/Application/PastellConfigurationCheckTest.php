<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace MaarchCourrier\Tests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Application\SendToPastell;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellApiMock;
use MaarchCourrier\Tests\app\external\Pastell\Mock\PastellConfigMock;
use PHPUnit\Framework\TestCase;

class PastellConfigurationCheckTest extends TestCase
{
    private PastellApiMock $pastellApiMock;
    private PastellConfigMock $pastellConfigMock;
    private PastellConfigurationCheck $pastellConfigCheck;

    protected function setUp(): void
    {
        $this->pastellApiMock = new PastellApiMock();
        $this->pastellConfigMock = new PastellConfigMock();
        $this->pastellConfigCheck = new PastellConfigurationCheck($this->pastellApiMock, $this->pastellConfigMock);
    }

    /**
     * Testing when configuration is empty
     * @return void
     */
    public function testConfigurationTestIsNotValidIfItIsEmpty(): void
    {
        $this->pastellConfigMock->pastellConfig = null;

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing when Pastell API URL is missing in config
     * @return void
     */
    public function testConfigurationTestIsNotValidIfUrlIsMissing(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            '',
            'toto',
            'toto123',
            193,
            0,
            '',
            '',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing when API login is missing in config
     * @return void
     */
    public function testConfigurationTestIsNotValidIfLoginIsMissing(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            '',
            'toto123',
            193,
            0,
            '',
            '',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing when API password is missing in config
     * @return void
     */
    public function testConfigurationTestIsNotValidIfPasswordIsMissing(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            '',
            193,
            0,
            '',
            '',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing a wrong Pastell API URL
     * @return void
     */
    public function testConfigurationTestIsNotValidIfUrlIsNotValid(): void
    {
        $this->pastellApiMock->version = ['errors' => 'Erreur lors de la récupération de la version'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing when entity is missing in config
     * @return void
     */
    public function testConfigurationTestIsNotValidIfEntityIsMissing(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            0,
            0,
            '',
            '',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing wrong entities
     * @return void
     */
    public function testConfigurationTestIsNotValidIfEntityIsNotFoundInPastell(): void
    {
        $this->pastellApiMock->entity = ['192', '42', '813'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
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
     * Testing non-valid entities
     * @return void
     */
    public function testConfigurationTestIsNotValidIfEntityIsNotValid(): void
    {
        $this->pastellApiMock->entity = ['errors' => 'Erreur lors de la récupération des entités'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing with the right entity
     * @return void
     */
    public function testConfigurationTestIsValidIfEntityIsFoundInPastell(): void
    {
        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }

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

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing when connector is missing in config
     * @return void
     */
    public function testConfigurationTestIsNotValidIfConnectorIsMissing(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            0,
            '',
            '',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing with a bad connector
     * @return void
     */
    public function testConfigurationTestIsNotValidIfConnectorIsNotFoundInPastell(): void
    {
        $this->pastellApiMock->entity = ['192', '193', '813'];
        $this->pastellApiMock->connector = ['34', '245', '813'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

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
     * Testing with a non-valid connector
     * @return void
     */
    public function testConfigurationTestIsNotValidIfConnectorIsNotValid(): void
    {
        $this->pastellApiMock->connector = ['errors' => 'Erreur lors de la récupération des connecteurs'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing with the right connector
     * @return void
     */
    public function testConfigurationTestIsValidIfConnectorIsFoundInPastell(): void
    {
        $this->pastellApiMock->entity = ['192', '193', '813'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }

    /**
     * Testing when document type is missing in config
     * @return void
     */
    public function testConfigurationTestIsNotValidIfDocumentTypeIsMissing(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            '',
            '',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing when a bad document type
     * @return void
     */
    public function testConfigurationTestIsNotValidIfDocumentTypeIsNotFoundInPastell(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            'ls-not-document-pdf',
            'XELIANS COURRIER',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

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
     * Testing with a non-valid document type
     * @return void
     */
    public function testConfigurationTestIsNotValidIfDocumentTypeIsNotValid(): void
    {
        $this->pastellApiMock->flux = ['errors' => 'Erreur lors de la récupération des types de documents'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing with the right connector
     * @return void
     */
    public function testConfigurationTestIsValidIfDocumentTypeIsFoundInPastell(): void
    {
        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }

    /**
     * Testing conf without iParapheur type
     * @return void
     */
    public function testConfigurationTestIsNotValidIfIparapheurTypeIsMissing(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
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
    public function testConfigurationTestIsNotValidIfIparapheurTypeIsNotFoundInPastell(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            'ls-document-pdf',
            'PELIANS COURRIER',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

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
     * @return void
     */
    public function testConfigurationTestIsNotValidIfIparapheurTypeIsNotValid(): void
    {
        $this->pastellApiMock->iParapheurType = ['errors' => 'Erreur lors de la récupération des types de iParapheur'];

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertFalse($result);
    }

    /**
     * Testing conf with the right iParapheur type
     * @return void
     */
    public function testConfigurationTestIsValidIfIparapheurTypeIsFoundInPastell(): void
    {
        $this->pastellConfigMock->pastellConfig = new PastellConfig(
            'testurl',
            'toto',
            'toto123',
            193,
            776,
            'ls-document-pdf',
            'XELIANS COURRIER',
            ''
        );

        $result = $this->pastellConfigCheck->checkPastellConfig();

        $this->assertTrue($result);
    }

    public function testSendToPastellIsNotValidIfIdFolderIsMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->folder = [];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);

        $result = $sendToPastell->sendFolderToPastell();

        $this->assertFalse($result);
    }

    public function testConfigurationIsNotValidIfIdFolderIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->folder = ['errors' => 'Erreur lors de la récupération de l\'id du dossier'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);

        $result = $sendToPastell->sendFolderToPastell();

        $this->assertFalse($result);
    }

    public function testSendToPastellIsValidIfIdFolderIsNotMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->folder = [];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);

        $result = $sendToPastell->sendFolderToPastell();

        $this->assertFalse($result);
    }

    public function testSendToPastellIsNotValidIfIparapheurSousTypeMissing(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->folder = [];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);

        $result = $sendToPastell->sendFolderToPastell();

        $this->assertFalse($result);
    }

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
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);

        $result = $sendToPastell->sendFolderToPastell();

        $this->assertFalse($result);
    }

    public function testConfigurationIsNotValidIfIparapheurSousTypeIsNotValid(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellApiMock->iParapheurSousType = ['errors' => 'Erreur lors de la récupération des sous types de iParapheur'];
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);

        $result = $sendToPastell->sendFolderToPastell();

        $this->assertFalse($result);
    }

    public function testSendToPastellIsValidIfIparapheurSousTypeIsFoundInPastell(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellConfigCheck = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);
        $sendToPastell = new SendToPastell($pastellConfigCheck, $pastellApiMock, $pastellConfigMock);

        $result = $sendToPastell->sendFolderToPastell();

        $this->assertTrue($result);
    }
}
