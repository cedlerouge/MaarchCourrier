<?php

namespace MaarchCourrier\SignatureBook\Application\Config;

use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookConfigInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookConfig;

class RetrieveConfig
{
    public function __construct(
        private readonly SignatureBookConfigInterface $config
    ) {
    }

    /**
     * @return SignatureBookConfig|null
     */
    public function getConfig(): ?SignatureBookConfig
    {
        return $this->config->getConfig();
    }
}
