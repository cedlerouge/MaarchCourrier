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
use ExternalSignatoryBook\pastell\Domain\PastellStates;

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
                $errors[$key] = 'Error when getting folder detail : ' . $info['error'];
                unset($idsToRetrieve[$key]);
            } else {
                if (in_array('verif-iparapheur', $info['actionPossibles'] ?? [])) {
                    $verif = $this->pastellApi->verificationIParapheur($this->config, $value['external_id']);
                    if ($verif !== true) {
                        $errors[$key] = 'Action "verif-iparapheur" failed';
                        unset($idsToRetrieve[$key]);
                        continue;
                    }
                }

                $resId = $value['res_id_master'] ?? $value['res_id'];
                $result = $this->parseIParapheurLog->parseLogIparapheur($resId, $value['external_id']);

                if (!empty($result['error'])) {
                    $errors[$key] = $result['error'];
                    unset($idsToRetrieve[$key]);
                    continue;
                }

                $idsToRetrieve[$key] = array_merge($value, $result);

                // Deletion is automatic if postAction in conf is suppression
                $postAction = $this->pastellConfig->getPastellConfig()->getPostAction();
                if (
                    $postAction == 'suppression' &&
                    ($result['status'] == 'validated' || $result['status'] == 'refused')
                ) {
                    $deleteFolderResult = $this->pastellApi->deleteFolder($this->config, $resId);
                    if (!empty($deleteFolderResult['error'])) {
                        return ['error' => $deleteFolderResult['error']];
                    }
                }
            }
        }

        return ['success' => $idsToRetrieve, 'error' => $errors];
    }
}
