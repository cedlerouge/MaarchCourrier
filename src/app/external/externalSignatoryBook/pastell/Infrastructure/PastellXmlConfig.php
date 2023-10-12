<?php

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;
use SrcCore\models\CoreConfigModel;

class PastellXmlConfig implements PastellConfigInterface
{

    public function getPastellConfig(array $Args = []): ?PastellConfig
    {
        $customId = CoreConfigModel::getCustomId();
        if (!empty($aArgs['customId'])) {
            $customId = $aArgs['customId'];
        }

        if (file_exists("custom/{$customId}/modules/visa/xml/remoteSignatoryBooks.xml")) {
            $path = "custom/{$customId}/modules/visa/xml/remoteSignatoryBooks.xml";
        } else {
            $path = 'modules/visa/xml//remoteSignatoryBooks.xml';
        }
        $pastellConfig = null;
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            $PastellConfig = $loadedXml->xpath('//signatoryBook[id=\'Pastell\']')[0] ?? null;
            if ($PastellConfig) {
                $pastellConfig = new PastellConfig(
                    (string)$PastellConfig->url ?? null,
                    (string)$PastellConfig->login ?? null,
                    (string)$PastellConfig->password ?? null,
                    (int)$PastellConfig->entityId ?? null,
                    (int)$PastellConfig->connectorId ?? null,
                    (string)$PastellConfig->defaultType ?? null,
                    (string)$PastellConfig->defaultSousType ?? null
                );
            }
        }

        return $pastellConfig;
    }
}
