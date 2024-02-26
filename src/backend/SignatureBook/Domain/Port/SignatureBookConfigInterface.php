<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\SignatureBook\Domain\SignatureBookConfig;

interface SignatureBookConfigInterface
{
    /**
     * @return SignatureBookConfig
     */
    public function getConfig(): SignatureBookConfig;
}
