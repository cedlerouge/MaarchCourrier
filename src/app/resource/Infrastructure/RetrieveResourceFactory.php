<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve from Pastell factory
 * @author dev@maarch.org
 */

namespace Resource\Infrastructure;

use Resource\Application\RetrieveResource;

class RetrieveResourceFactory
{
    /**
     * @return RetrieveResource
     */
    public static function create(): RetrieveResource
    {
        $resourceData = new ResourceDataDatabase();
        $resourceFile = new ResourceFile();

        return new RetrieveResource($resourceData, $resourceFile);
    }
}
