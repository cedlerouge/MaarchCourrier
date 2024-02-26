<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\Config;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookConfigInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookConfig;

class SignatureBookConfigRepositoryMock implements SignatureBookConfigInterface
{
    public bool $isNewInternalParaphActive = false;
    public string $url = "https://example.com";

    public function getConfig(): SignatureBookConfig
    {
        $config = new SignatureBookConfig();

        $config->setIsNewInternalParaph($this->isNewInternalParaphActive);

        if ($this->isNewInternalParaphActive) {
            $config->setUrl($this->url);
        }

        return $config;
    }
}
