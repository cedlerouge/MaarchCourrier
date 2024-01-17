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
use ExternalSignatoryBook\pastell\Domain\ResourceDataInterface;
use History\controllers\HistoryController;
use User\models\UserModel;

class RetrieveFromPastell
{
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;
    private PastellConfigurationCheck $pastellConfigCheck;
    private ParseIParapheurLog $parseIParapheurLog;
    private PastellConfig $config;
    private ResourceDataInterface $resourceData;

    /**
     * @param PastellApiInterface $pastellApi
     * @param PastellConfigInterface $pastellConfig
     * @param PastellConfigurationCheck $pastellConfigCheck
     * @param ParseIParapheurLog $parseIParapheurLog
     * @param ResourceDataInterface $resourceData
     */
    public function __construct(
        PastellApiInterface       $pastellApi,
        PastellConfigInterface    $pastellConfig,
        PastellConfigurationCheck $pastellConfigCheck,
        ParseIParapheurLog        $parseIParapheurLog,
        ResourceDataInterface     $resourceData
    )
    {
        $this->pastellApi = $pastellApi;
        $this->pastellConfig = $pastellConfig;
        $this->pastellConfigCheck = $pastellConfigCheck;
        $this->parseIParapheurLog = $parseIParapheurLog;
        $this->config = $this->pastellConfig->getPastellConfig();
        $this->resourceData = $resourceData;
    }

    private static function addLogInHistory(array $infosLog)
    {
        $userIdRoot = UserModel::get([
            'select' => ['id'],
            'where'  => ['mode = ? OR mode = ?'],
            'data'   => ['root_visible', 'root_invisible'],
            'limit'  => 1
        ])[0]['id'];

        $message = (is_array($infosLog['message'])) ? implode("-", $infosLog['message']) : $infosLog['message'];

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $infosLog['id'],
            'eventType' => 'ACTION#1',
            'eventId'   => '1',
            'userId'    => $userIdRoot,
            'info'      => "[Pastell api] {$message}"
        ]);
    }

    /**
     * @param array $idsToRetrieve
     * @param string $documentType
     * @return array|string[]
     */
    public function retrieve(array $idsToRetrieve, string $documentType): array
    {
        if (!$this->pastellConfigCheck->checkPastellConfig()) {
            return ['success' => [], 'error' => 'Cannot retrieve resources from pastell : pastell configuration is invalid'];
        }
        $errors = [];

        foreach ($idsToRetrieve as $key => $value) {
            $info = $this->pastellApi->getFolderDetail($this->config, $value['external_id']);
            if (!empty($info['error'])) {
                $errors[$key] = 'Error when getting folder detail : ' . $info['error'];
                $this->addLogInHistory(['id' => $value['res_id_master'] ?? $value['res_id'], 'message' => 'Error when getting folder detail : ' . $info['error']]);
                unset($idsToRetrieve[$key]);
            } else {
                if (in_array('verif-iparapheur', $info['actionPossibles'] ?? [])) {
                    $verif = $this->pastellApi->verificationIParapheur($this->config, $value['external_id']);
                    if ($verif !== true) {
                        $errors[$key] = 'Action "verif-iparapheur" failed';

                        $this->addLogInHistory(['id' => $value['res_id_master'] ?? $value['res_id'], 'message' => 'Action "verif-iparapheur" failed']);
                        unset($idsToRetrieve[$key]);
                        continue;
                    }
                }
                // Need res_id_master for parseLogIparapheur and res_id for updateDocument (for attachments and main document)
                $resId = $value['res_id_master'] ?? $value['res_id'];
                $result = $this->parseIParapheurLog->parseLogIparapheur($resId, $value['external_id']);
                $this->resourceData->updateDocumentExternalStateSignatoryUser($value['res_id'], $documentType == 'resLetterbox' ? 'resource' : 'attachment', $result['signatory'] ?? '');

                if (!empty($result['error'])) {
                    $errors[$key] = $result['error'];
                    $this->addLogInHistory(['id' => $resId, 'message' => $result['error']]);
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
                    $deleteFolderResult = $this->pastellApi->deleteFolder($this->config, $value['external_id']);
                    if (!empty($deleteFolderResult['error'])) {
                        $errors[$key] = $deleteFolderResult['error'];
                        $this->addLogInHistory(['id' => $resId, 'message' => $deleteFolderResult['error']]);
                        unset($idsToRetrieve[$key]);
                    }
                }
            }
        }

        return ['success' => $idsToRetrieve, 'error' => $errors];
    }
}
