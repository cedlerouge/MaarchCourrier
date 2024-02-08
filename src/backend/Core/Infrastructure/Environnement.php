<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Environnement class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\Core\Infrastructure;

use MaarchCourrier\Core\Domain\Port\EnvironnementInterface;
use SrcCore\models\CoreConfigModel;

class Environnement implements EnvironnementInterface
{
    public function isDebug(): bool
    {
        $file = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);
        $config = $file['config'];
        return !empty($config['debug']);
    }
}
