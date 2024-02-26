<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief SignatureServiceConfigLoaderRepository class
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure;

use Exception;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfig;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfigLoaderInterface;
use SrcCore\models\CoreConfigModel;

class SignatureServiceJsonConfigLoader implements SignatureServiceConfigLoaderInterface
{
    /**
     * @return SignatureServiceConfig|null
     * @throws Exception Returns the signatureBook config
     */
    public function getSignatureServiceConfig(): ?SignatureServiceConfig
    {
        $loadedConfig = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);
        $signatureBookConfig = null;
        if (!empty($loadedConfig)) {
            $signatureBookConfig = $loadedConfig['signatureBook'];
            if ($signatureBookConfig) {
                $signatureBookConfig = new SignatureServiceConfig(
                    $signatureBookConfig['url'] ?? null,
                );
            }
        }
        return $signatureBookConfig ?? null;
    }
}
