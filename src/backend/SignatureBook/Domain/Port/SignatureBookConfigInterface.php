<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\SignatureBook\Domain\SignatureBookConfig;

interface SignatureBookConfigInterface
{
    /**
     * @return SignatureBookConfig|null
     */
    public function getConfig(): ?SignatureBookConfig;
}
