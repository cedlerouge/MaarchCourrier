<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Send to Pastell
 * @author dev@maarch.org
 */

namespace ExternalSignatoryBook\pastell\Application;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;
use ExternalSignatoryBook\pastell\Domain\ProcessVisaWorkflowInterface;
use ExternalSignatoryBook\pastell\Domain\ResourceDataInterface;
use ExternalSignatoryBook\pastell\Domain\ResourceFileInterface;

class SendToPastell
{
    private PastellConfigurationCheck $checkConfigPastell;
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;
    private ProcessVisaWorkflowInterface $processVisaWorkflow;
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;

    /**
     * @param PastellConfigurationCheck $checkConfigPastell
     * @param PastellApiInterface $pastellApi
     * @param PastellConfigInterface $pastellConfig
     * @param ResourceDataInterface $resourceData
     * @param ResourceFileInterface $resourceFile
     * @param ProcessVisaWorkflowInterface $processVisaWorkflow
     */
    public function __construct(
        PastellConfigurationCheck    $checkConfigPastell,
        PastellApiInterface          $pastellApi,
        PastellConfigInterface       $pastellConfig,
        ResourceDataInterface        $resourceData,
        ResourceFileInterface        $resourceFile,
        ProcessVisaWorkflowInterface $processVisaWorkflow
    )
    {
        $this->checkConfigPastell = $checkConfigPastell;
        $this->pastellConfig = $pastellConfig;
        $this->pastellApi = $pastellApi;
        $this->resourceData = $resourceData;
        $this->resourceFile = $resourceFile;
        $this->processVisaWorkflow = $processVisaWorkflow;
    }

    /**
     * @param int $resId
     * @param string $title
     * @param string $sousType
     * @param string $filePath
     * @return array idFolder Pastell
     */
    public function sendFolderToPastell(int $resId, string $title, string $sousType, string $filePath): array
    {
        $config = $this->pastellConfig->getPastellConfig();

        // Checking folder creation
        $idFolder = $this->pastellApi->createFolder($config);
        if (empty($idFolder)) {
            return ['error' => 'Folder creation has failed'];
        } elseif (!empty($idFolder['errors'])) {
            return ['error' => $idFolder['errors']];
        }
        $idFolder = $idFolder['idFolder'];

        /***
         * patch
         * send file
         *
         * annexe -> plus tard
         *
         * action envoi iparapheur
         *
         *  processVisaWorkflow
         */
        $editResult = $this->pastellApi->editFolder($config, $idFolder, $title, $sousType);
        if (!empty($editResult['error'])) {
            return $editResult['error'];
        } else {
            // uploading main file
            $uploadResult = $this->pastellApi->uploadMainFile($config, $idFolder, $filePath);
            if (!empty($uploadResult['error'])) {
                return $uploadResult['error'];
            } else {
                // Sending folder to iParapheur
                $orientationResult = $this->pastellApi->orientation($config, $idFolder);
                if (!empty($orientationResult['error'])) {
                    return $orientationResult['error'];
                } else {
                    $this->processVisaWorkflow->processVisaWorkflow($resId, false);
                }
                $info = $this->pastellApi->getFolderDetail($config, $idFolder);
                if (in_array('send-iparapheur', $info['actionPossibles'])) {
                    $this->pastellApi->sendIparapheur($config, $idFolder);
                }
            }
        }
        return ['idFolder' => $idFolder];
    }

    /**
     * Sending data and main file to ExternalSignatoryBookTrait
     * @param int $resId
     * @return string[]
     */
    public function sendData(int $resId): array
    {
        /**
         *       Recup data du courrier
         * foreach resIds
         *      courrier est integré ?
         *          non → continue
         *          oui → appel fonction
         */

        $config = $this->pastellConfig->getPastellConfig();

        //Check iParapheur subtype
        $iParapheurSousType = $this->pastellApi->getIparapheurSousType($config, $resId);
        if (!empty($iParapheurSousType['error'])) {
            return $iParapheurSousType['error'];
        } elseif (!in_array($config->getIparapheurSousType(), $iParapheurSousType)) {
            return ['error' => 'Subtype does not exist in iParapheur'];
        } else {
            $idFolder = $this->sendResource($resId, $config->getIparapheurSousType());

            return [
                'sended' => [
                    'letterbox_coll' => [
                        $resId => $idFolder['idFolder'] ?? null
                    ]
                ]
            ];
        }
    }

    /**
     * Getting data, file content and infos fom MC to be sent
     * @param int $resId
     * @param string $sousType
     * @param array $annexes
     * @return string[]|void
     */
    public function sendResource(int $resId, string $sousType, array $annexes = []): array
    {
        /**
         * -> infos du courrier
         * -> recup la PJ
         *      for integre ? signable ?
         *  -> sendToFolderToPastell
         */

        // Getting data from MC (res_letterbox)
        $mainResource = $this->resourceData->getMainResourceData($resId);

        // Checking if main document is integrated
        if (!empty($mainResource)) {
            $mainDocumentIntegration = json_decode($mainResource['integrations'], true);
            $externalId = json_decode($mainResource['external_id'], true);

            if ($mainDocumentIntegration['inSignatureBook'] && empty($externalId['signatureBookId'])) {
                $resId = $mainResource['res_id'];
                $title = $mainResource['subject'];
                // Getting path of the main file
                $mainResourceFilePath = $this->resourceFile->getMainResourceFilePath($resId);
                if (str_contains($mainResourceFilePath, 'Error')) {
                    return ['Error' => 'Document ' . $resId . ' is not converted in pdf'];
                } else {
                    return $this->sendFolderToPastell($resId, $title, $sousType, $mainResourceFilePath);
                }
            }
        }

        // Recup file path
        //$mainResourceFilePath = $this->resourceFile->getMainResourceFilePath($resId);

        //return $this->sendFolderToPastell($resId, $mainResource['subject'], $sousType, $mainResourceFilePath);

        //Récupération des infos du courrier côté MC
//        $attachments = AttachmentModel::get([
//            'select' => ['res_id', 'docserver_id', 'path', 'filename', 'format', 'attachment_type', 'fingerprint', 'title'],
//            'where'  => ['res_id_master = ?', 'attachment_type not in (?)', "status NOT IN ('DEL','OBS', 'FRZ', 'TMP', 'SEND_MASS')", "in_signature_book = 'true'"],
//            'data'   => [$data['resIdMaster'], ['signed_response']]
//        ]);
//
//        //Récupération du type de PJ
//        $attachmentTypes = AttachmentTypeModel::get(['select' => ['type_id', 'signable']]);
//        $attachmentTypes = array_column($attachmentTypes, 'signable', 'type_id');
//
//        foreach ($attachments as $key => $value) {
//            if (!$attachmentTypes[$value['attachment_type']]) {
//                $adrInfo = AdrModel::getConvertedDocumentById(['resId' => $value['res_id'], 'collId' => 'attachments_coll', 'type' => 'PDF']);
//                $annexeAttachmentPath = DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id'], 'select' => ['path_template']]);
//                $value['filePath'] = $annexeAttachmentPath['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $adrInfo['path']) . $adrInfo['filename'];
//
//                $docserverType = DocserverTypeModel::getById(['id' => $annexeAttachmentPath['docserver_type_id'], 'select' => ['fingerprint_mode']]);
//                $fingerprint = StoreController::getFingerPrint(['filePath' => $value['filePath'], 'mode' => $docserverType['fingerprint_mode']]);
//                if ($value['fingerprint'] != $fingerprint) {
//                    return ['error' => 'Fingerprints do not match'];
//                }
//
//                unset($attachments[$key]);
//            }
//        }
//
//        foreach ($attachments as $attachment) {
//            $resId = $attachment['res_id'];
//            $title = $attachment['title'];
//            $collId = 'attachments_coll';
//
//            $this->sendFolderToPastell($resId, $title, $collId);
//        }
    }
}
