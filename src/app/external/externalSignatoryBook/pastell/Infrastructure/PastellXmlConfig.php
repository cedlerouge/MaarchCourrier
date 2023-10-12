<?php

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;
use SrcCore\models\CoreConfigModel;

class PastellXmlConfig implements PastellConfigInterface
{

    public function getPastellConfig(array $Args = []): array
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
        $pastellConfig = [];
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            $PastellConfig = $loadedXml->xpath('//signatoryBook[id=\'Pastell\']')[0] ?? null;
            if ($PastellConfig) {
                $pastellConfig['url']       = (string)$PastellConfig->url ?? null;
                $pastellConfig['login']     = (string)$PastellConfig->login ?? null;
                $pastellConfig['password']  = (string)$PastellConfig->password ?? null;
                $pastellConfig['entite']    = (string)$PastellConfig->entity ?? null;
                $pastellConfig['type']      = (string)$PastellConfig->defaultType ?? null;
                $pastellConfig['sousType']  = (string)$PastellConfig->defaultSousType ?? null;
            }
        }

        return $pastellConfig;
    }
}
