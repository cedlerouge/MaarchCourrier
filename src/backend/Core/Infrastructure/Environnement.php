<?php

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
