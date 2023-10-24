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
        $pastellState = null;
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            $pastellState = $loadedXml->xpath('//signatoryBook[id=\'pastell\']')[0] ?? null;
            if ($pastellState) {
                $pastellState = new PastellStates(
                    (string)$pastellState->errorCode ?? null,
                    (string)$pastellState->visaState ?? null,
                    (string)$pastellState->signState ?? null,
                    (string)$pastellState->refusedVisa ?? null,
                    (string)$pastellState->refusedSign ?? null,
                );
            }
        }
        return $pastellState;
    }
}
