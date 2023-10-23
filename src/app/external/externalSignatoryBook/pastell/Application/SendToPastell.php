<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Application;

use Attachment\models\AttachmentModel;
use Attachment\models\AttachmentTypeModel;
use Convert\controllers\ConvertPdfController;
use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfigInterface;
use Resource\controllers\StoreController;
use Resource\models\ResModel;

class SendToPastell
{
    private PastellConfigurationCheck $checkConfigPastell;
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;

    /**
     * @param PastellConfigurationCheck $checkConfigPastell
     * @param PastellApiInterface $pastellApi
     * @param PastellConfigInterface $pastellConfig
     */
    public function __construct(
        PastellConfigurationCheck $checkConfigPastell,
        PastellApiInterface       $pastellApi,
        PastellConfigInterface    $pastellConfig
    )
    {
        $this->checkConfigPastell = $checkConfigPastell;
        $this->pastellConfig = $pastellConfig;
        $this->pastellApi = $pastellApi;
    }

    /**
     * @param $resId
     * @param $title
     * @param $collId
     * @return bool
     */
    public function sendFolderToPastell($resId, $title, $collId): bool
    {
        $config = $this->pastellConfig->getPastellConfig();

        //Check folder creation
        $idFolder = $this->pastellApi->createFolder($config);
        if (empty($idFolder)) {
            return false;
        } elseif (!empty($idFolder['errors'])) {
            return false;
        }
        $idFolder = $idFolder['idFolder'];

        //Check iParapheur subtype
        $iParapheurSousType = $this->pastellApi->getIparapheurSousType($config, $idFolder);
        if (!empty($iParapheurSousType['errors'])) {
            return false;
        } elseif (!in_array($config->getIparapheurSousType(), $iParapheurSousType)) {
            return false;
        }

        /***
         * patch
         * send file
         *
         * annexe
         *
         * action envoi iparapheur
         *
         */
        $this->pastellApi->editFolder($config, $idFolder, $title);


        return true;
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function
    sendData(array $data): array
    {
        /**
         *       Recup data du courrier
         * foreach resIds
         *      courrier est integré ?
         *          non → continue
         *          oui → appel fonction
         */

        //$config = $this->pastellConfig->getPastellConfig();

        //Récupération des datas du courrier côté MC
        $annexes['letterbox'] = ResModel::get([
            'select' => ['res_id', 'path', 'filename', 'docserver_id', 'format', 'category_id', 'external_id', 'integrations', 'subject'],
            'where'  => ['res_id = ?'],
            'data'   => [$data['resIdMaster']]
        ]);

        //Vérification si le document principal est intégré
        if (!empty($annexes['letterbox'][0])) {
            $mainDocumentIntegration = json_decode($annexes['letterbox'][0]['integrations'], true);
            $externalId = json_decode($annexes['letterbox'][0]['external_id'], true);
            if ($mainDocumentIntegration['inSignatureBook'] && empty($externalId['signatureBookId'])) {
                $response = $this->sendResource($data, $annexes);
            }
        }
        return $response;
    }

    /**
     * @param array $data
     * @param array $annexes
     * @return string[]|void
     */
    public function sendResource(array $data, array $annexes)
    {
        /**
         * -> infos du courrier
         * -> recup la PJ
         *      for integre ? signable ?
         *  -> sendToFolderToPastell
         */

        if (!empty($annexes['letterbox'][0]['docserver_id'])) {
            $adrMainInfo = ConvertPdfController::getConvertedPdfById(['resId' => $data['resIdMaster'], 'collId' => 'letterbox_coll']);
            $letterboxPath = DocserverModel::getByDocserverId(['docserverId' => $adrMainInfo['docserver_id'], 'select' => ['path_template']]);
            $annexes['letterbox'][0]['filePath'] = $letterboxPath['path_template'] . str_replace('#', '/', $adrMainInfo['path']) . $adrMainInfo['filename'];
        }

        // Traitement du doc principal
        $resId = $annexes['letterbox'][0]['res_id'];
        $title = $annexes['letterbox'][0]['subject'];
        $collId = 'letterbox_coll';

        $this->sendFolderToPastell($resId, $title, $collId);

        //Récupération des infos du courrier côté MC
        $attachments = AttachmentModel::get([
            'select' => ['res_id', 'docserver_id', 'path', 'filename', 'format', 'attachment_type', 'fingerprint', 'title'],
            'where'  => ['res_id_master = ?', 'attachment_type not in (?)', "status NOT IN ('DEL','OBS', 'FRZ', 'TMP', 'SEND_MASS')", "in_signature_book = 'true'"],
            'data'   => [$data['resIdMaster'], ['signed_response']]
        ]);

        //Récupération du type de PJ
        $attachmentTypes = AttachmentTypeModel::get(['select' => ['type_id', 'signable']]);
        $attachmentTypes = array_column($attachmentTypes, 'signable', 'type_id');

        foreach ($attachments as $key => $value) {
            if (!$attachmentTypes[$value['attachment_type']]) {
                $adrInfo = AdrModel::getConvertedDocumentById(['resId' => $value['res_id'], 'collId' => 'attachments_coll', 'type' => 'PDF']);
                $annexeAttachmentPath = DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id'], 'select' => ['path_template']]);
                $value['filePath'] = $annexeAttachmentPath['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $adrInfo['path']) . $adrInfo['filename'];

                $docserverType = DocserverTypeModel::getById(['id' => $annexeAttachmentPath['docserver_type_id'], 'select' => ['fingerprint_mode']]);
                $fingerprint = StoreController::getFingerPrint(['filePath' => $value['filePath'], 'mode' => $docserverType['fingerprint_mode']]);
                if ($value['fingerprint'] != $fingerprint) {
                    return ['error' => 'Fingerprints do not match'];
                }

                unset($attachments[$key]);
            }
        }

        foreach ($attachments as $attachment) {
            $resId = $attachment['res_id'];
            $title = $attachment['title'];
            $collId = 'attachments_coll';

            $this->sendFolderToPastell($resId, $title, $collId);
        }
    }
}
