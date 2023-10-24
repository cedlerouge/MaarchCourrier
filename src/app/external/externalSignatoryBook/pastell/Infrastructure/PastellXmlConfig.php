<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;
use ExternalSignatoryBook\pastell\Domain\PastellStates;
use SrcCore\models\CoreConfigModel;

class PastellXmlConfig implements PastellConfigInterface
{
    /**
     * @param array $args
     * @return PastellConfig|null
     */
    public function getPastellConfig(array $args = []): ?PastellConfig
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
            $PastellConfig = $loadedXml->xpath('//signatoryBook[id=\'pastell\']')[0] ?? null;
            if ($PastellConfig) {
                $pastellConfig = new PastellConfig(
                    (string)$PastellConfig->url ?? null,
                    (string)$PastellConfig->login ?? null,
                    (string)$PastellConfig->password ?? null,
                    (int)$PastellConfig->entityId ?? null,
                    (int)$PastellConfig->connectorId ?? null,
                    (string)$PastellConfig->documentType ?? null,
                    (string)$PastellConfig->iParapheurType ?? null,
                    (string)$PastellConfig->iParapheurSousType ?? null
                );
            }
        }
        return $pastellConfig;
    }

    /**
     * @param array $args
     * @return PastellStates|null
     */
    public function getPastellStates(array $args = []): ?PastellStates
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
        $pastellStat = null;
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            $pastellConfig = $loadedXml->xpath('//signatoryBook[id=\'pastell\']')[0] ?? null;
            if ($pastellStat) {
                $pastellStat = new PastellStates(
                    (string)$pastellStat->errorCode ?? null,
                    (string)$PastellConfig->visaState ?? null,
                    (string)$PastellConfig->signState ?? null,
                    (int)$PastellConfig->refusedVisa ?? null,
                    (int)$PastellConfig->refusedSign ?? null,
                );
            }
        }
        return $pastellStat;
    }
}
