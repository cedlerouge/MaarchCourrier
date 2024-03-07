<?php

namespace MaarchCourrier\SignatureBook\Infrastructure;

use Exception;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookConfigInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookConfig;
use SrcCore\models\CoreConfigModel;

class SignatureBookConfigLoader implements SignatureBookConfigInterface
{
    /**
     * @throws Exception
     */
    public function getConfig(): SignatureBookConfig
    {
        $signatureBookConfig = new SignatureBookConfig();
        $config = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);

        if (isset($config['config']['newInternalParaph'])) {
            $signatureBookConfig->setIsNewInternalParaph($config['config']['newInternalParaph'] ?? false);

            if (isset($config['signatureBook']['url']) && $config['config']['newInternalParaph'] === true) {
                $signatureBookConfig->setUrl($config['signatureBook']['url'] ?? '');
            }
        }

        return $signatureBookConfig;
    }
}
