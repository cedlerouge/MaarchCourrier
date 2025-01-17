<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief CurlServiceInterface class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Core\Domain\Port;


use MaarchCourrier\Core\Domain\Curl\CurlRequest;

interface CurlServiceInterface
{
    public function call(CurlRequest $curlRequest): CurlRequest;
}
