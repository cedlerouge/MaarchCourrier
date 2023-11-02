<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Retrieve from Pastell
 * @author dev@maarch.org
 */

namespace ExternalSignatoryBook\pastell\Application;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;

class RetrieveFromPastell
{
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;
    private PastellConfigurationCheck $pastellConfigCheck;
    private ParseIParapheurLog $parseIParapheurLog;
    private PastellConfig $config;

    /**
     * @param PastellApiInterface $pastellApi
     * @param PastellConfigInterface $pastellConfig
     * @param PastellConfigurationCheck $pastellConfigCheck
     * @param ParseIParapheurLog $parseIParapheurLog
     */
    public function __construct(
        PastellApiInterface       $pastellApi,
        PastellConfigInterface    $pastellConfig,
        PastellConfigurationCheck $pastellConfigCheck,
        ParseIParapheurLog        $parseIParapheurLog
    )
    {
        $this->pastellApi = $pastellApi;
        $this->pastellConfig = $pastellConfig;
        $this->pastellConfigCheck = $pastellConfigCheck;
        $this->parseIParapheurLog = $parseIParapheurLog;
        $this->config = $this->pastellConfig->getPastellConfig();
    }

    /**
     * @param array $idsToRetrieve
     * @return array|string[]
     */
    public function retrieve(array $idsToRetrieve): array
    {
        if (!$this->pastellConfigCheck->checkPastellConfig()) {
            return ['success' => [], 'error' => 'Cannot retrieve resources from pastell : pastell configuration is invalid'];
        }

        $errors = [];

        foreach ($idsToRetrieve as $key => $value) {
            $info = $this->pastellApi->getFolderDetail($this->config, $value['external_id']);
            if (!empty($info['error'])) {
                $idsToRetrieve[$key]['status'] = 'waiting';
            } else {
                if (in_array('verif-iparapheur', $info['actionPossibles'])) {
                    $verif = $this->pastellApi->verificationIParapheur($this->config, $value['external_id']);
                    if ($verif !== true) {
                        $errors[$key] = 'Action "verif-iparapheur" failed';
                        unset($idsToRetrieve[$key]);
                        continue;
                    }
                }

                $result = $this->parseIParapheurLog->parseLogIparapheur($value['res_id'], $value['external_id']);

                if (!empty($result['error'])) {
                    $errors[$key] = $result['error'];
                    unset($idsToRetrieve[$key]);
                    continue;
                }

                $idsToRetrieve[$key] = array_merge($value, $result);
            }
        }

        return ['success' => $idsToRetrieve, 'error' => $errors];
    }
}
