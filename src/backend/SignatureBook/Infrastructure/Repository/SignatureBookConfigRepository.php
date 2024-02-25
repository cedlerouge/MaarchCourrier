<?php

namespace MaarchCourrier\SignatureBook\Infrastructure\Repository;

use Exception;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookConfigInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookConfig;
use SrcCore\models\CoreConfigModel;

class SignatureBookConfigRepository implements SignatureBookConfigInterface
{
    /**
     * @throws Exception
     */
    public function getConfig(): ?SignatureBookConfig
    {
        $signatureBookConfig = null;
        $config = CoreConfigModel::getJsonLoaded(['path' => 'config/config.json']);

        if (!empty($config['signatureBook'])) {
            $config = $config['signatureBook'];
            $signatureBookConfig = new SignatureBookConfig();

            $config['newInternalParaph'] = $config['newInternalParaph'] ?? false;
            $signatureBookConfig->setIsNewInternalParaph($config['newInternalParaph']);

            if ($config['newInternalParaph'] === true) {
                $signatureBookConfig->setUrl($config['url'] ?? '');
            }
        }

        return $signatureBookConfig;
    }
}
