<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

use MaarchCourrier\SignatureBook\Domain\SignatureBookConfigReturnApi;

interface SignatureBookConfigInterface
{
    /**
     * @return SignatureBookConfigReturnApi
     */
    public function getConfig(): SignatureBookConfigReturnApi;
}
