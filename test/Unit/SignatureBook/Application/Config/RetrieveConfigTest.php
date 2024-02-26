<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Application\Config;

use MaarchCourrier\SignatureBook\Application\Config\RetrieveConfig;
use MaarchCourrier\Tests\Unit\SignatureBook\Mock\Config\SignatureBookConfigRepositoryMock;
use PHPUnit\Framework\TestCase;

class RetrieveConfigTest extends TestCase
{
    private RetrieveConfig $retrieveConfig;
    private SignatureBookConfigRepositoryMock $signatureBookConfigRepositoryMock;

    protected function setUp(): void
    {
        $this->signatureBookConfigRepositoryMock = new SignatureBookConfigRepositoryMock();
        $this->retrieveConfig = new RetrieveConfig($this->signatureBookConfigRepositoryMock);
    }

    public function testGetDefaultConfigAndExpectParameterNewInternalParaphToBeFalse(): void
    {
        $config = $this->retrieveConfig->getConfig();

        var_dump($config);
    }
}
