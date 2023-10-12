<?php


/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace unitTests\app\external\Pastell\Application;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
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
        $pastellConfigMock->pastellConfig = [];
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
        $pastellConfigMock->pastellConfig = ['toto'];
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
        $pastellConfigMock->pastellConfig = ['url' => 'toto'];
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
        $pastellConfigMock->pastellConfig =
            [
                'url' => 'toto',
                'login' => 'toto2'
            ];
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
        $pastellConfigMock = new PastellConfigMock();
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);


        $result = $pastellService->checkPastellConfig();

        $this->assertFalse($result);

    }

    public function testIfEntityIsMatching(): void
    {
        $pastellApiMock = new PastellApiMock();
        $pastellConfigMock = new PastellConfigMock();
        $pastellService = new PastellConfigurationCheck($pastellApiMock, $pastellConfigMock);

        $result = $pastellService->checkEntity();

        $this->assertFalse($result);
    }
}
