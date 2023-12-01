<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve from Docserver
 * @author dev@maarch.org
 */

namespace Resource\Domain\Models;

class ResourceDataType
{
    Public const DEFAULT = 'DEFAULT';
    Public const SIGNED = 'SIGNED';
    Public const CONVERTED = 'CONVERTED';
    Public const VERSION = 'VERSION';
    public const VERSION_BY_PAGE = 'VERSION_BY_PAGE';

    public const TYPES = [
        ResourceDataType::DEFAULT,
        ResourceDataType::SIGNED,
        ResourceDataType::CONVERTED,
        ResourceDataType::VERSION,
        ResourceDataType::VERSION_BY_PAGE
    ];
}