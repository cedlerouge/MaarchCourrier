<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Application\Config;

use MaarchCourrier\SignatureBook\Application\Config\RetrieveConfig;
use MaarchCourrier\SignatureBook\Domain\SignatureBookConfig;
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

        $this->assertInstanceOf(SignatureBookConfig::class, $config);
        $this->assertFalse($config->isNewInternalParaph());
        $this->assertEmpty($config->getUrl());
    }

    public function testGetConfigWhenNewInternalParaphIsActive(): void
    {
        $this->signatureBookConfigRepositoryMock->isNewInternalParaphActive = true;

        $config = $this->retrieveConfig->getConfig();

        $this->assertInstanceOf(SignatureBookConfig::class, $config);
        $this->assertTrue($config->isNewInternalParaph());
        $this->assertNotEmpty($config->getUrl());
        $this->assertSame($this->signatureBookConfigRepositoryMock->url, $config->getUrl());
    }
}
