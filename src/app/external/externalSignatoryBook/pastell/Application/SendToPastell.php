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
use ExternalSignatoryBook\pastell\Domain\VisaCircuitDataInterface;

class SendToPastell
{
    private PastellConfigurationCheck $checkConfigPastell;
    private PastellApiInterface $pastellApi;
    private PastellConfigInterface $pastellConfig;
    private ProcessVisaWorkflowInterface $processVisaWorkflow;
    private ResourceDataInterface $resourceData;
    private ResourceFileInterface $resourceFile;

    private VisaCircuitDataInterface $visaCircuitData;

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
        ProcessVisaWorkflowInterface $processVisaWorkflow,
        VisaCircuitDataInterface     $visaCircuitData
    ) {
        $this->checkConfigPastell = $checkConfigPastell;
        $this->pastellConfig = $pastellConfig;
        $this->pastellApi = $pastellApi;
        $this->resourceData = $resourceData;
        $this->resourceFile = $resourceFile;
        $this->processVisaWorkflow = $processVisaWorkflow;
        $this->visaCircuitData = $visaCircuitData;
    }

    /**
     * Sending data and main file to ExternalSignatoryBookTrait
     * @param int $resId
     * @return string[]
     */
    public function sendData(int $resId): array
    {
        if (!$this->checkConfigPastell->checkPastellConfig()) {
            return ['error' => 'Cannot retrieve resources from pastell : pastell configuration is invalid'];
        }

        $config = $this->pastellConfig->getPastellConfig();
        $sousType = $config->getIparapheurSousType();

        $nextSignatory = $this->visaCircuitData->getNextSignatory($resId);
        if (!empty($nextSignatory['userId'])) {
            $sousType = $nextSignatory['userId'];
        }

        $idFolder = $this->sendResource($resId, $sousType);
        if (!empty($idFolder['error'])) {
            return ['error' => $idFolder['error']];
        }

        return [
            'sended' => [
                'letterbox_coll' => [
                    $resId => $idFolder['idFolder'] ?? null
                ]
            ]
        ];
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
        // Getting data from MC (res_letterbox)
        $mainResource = $this->resourceData->getMainResourceData($resId);

        if (empty($mainResource)) {
            return ['error' => 'Resource not found'];
        }

        $attachments = $this->resourceData->getIntegratedAttachmentsData($resId);
        $attachmentTypes = $this->resourceData->getAttachmentTypes();

        $annexes = [];
        foreach ($attachments as $key => $attachment) {
            $type = $attachmentTypes[$attachment['attachment_type']];
            if (empty($type['signable'])) {
                // Attachment is not signable, so it is an annex
                $filePath = $this->resourceFile->getAttachmentFilePath($attachment['res_id'], $attachment['fingerprint']);

                $annexes[] = $filePath;
                unset($attachments[$key]);
            }
        }

        // Checking if main document is integrated
        $mainDocumentIntegration = json_decode($mainResource['integrations'], true);

        if ($mainDocumentIntegration['inSignatureBook'] && empty(json_decode($mainResource['external_id'], true))) {
            $resId = $mainResource['res_id'];
            $title = $mainResource['subject'];
            // Getting path of the main file
            $mainResourceFilePath = $this->resourceFile->getMainResourceFilePath($resId);
            if (str_contains($mainResourceFilePath, 'Error')) {
                return ['error' => 'Document ' . $resId . ' is not converted in pdf'];
                //} elseif (!empty($attachmentsResource)) { // Getting attachments data
                //$attachmentsResourceFilePath = $this->resourceFile->getAttachmentsFilePath($attachmentsResource);
                // TODO
            } else {
                return $this->sendFolderToPastell($resId, $title, $sousType, $mainResourceFilePath, $annexes);
            }
        }
    }

    /**
     * @param int $resId
     * @param string $title
     * @param string $sousType
     * @param string $filePath
     * @param array $annexes
     * @return array|string[]
     */
    public function sendFolderToPastell(int $resId, string $title, string $sousType, string $filePath, array $annexes = []): array
    {
        $config = $this->pastellConfig->getPastellConfig();

        // Checking folder creation
        $idFolder = $this->pastellApi->createFolder($config);
        if (empty($idFolder)) {
            return ['error' => 'Folder creation has failed'];
        } elseif (!empty($idFolder['error'])) {
            return ['error' => $idFolder['error']];
        }
        $idFolder = $idFolder['idFolder'];

        // Check iParapheur subtype
        $iParapheurSousTypes = $this->pastellApi->getIparapheurSousType($config, $idFolder);
        if (!empty($iParapheurSousTypes['error'])) {
            return ['error' => $iParapheurSousTypes['error']];
        } elseif (!in_array($sousType, $iParapheurSousTypes)) {
            if (!in_array($config->getIparapheurSousType(), $iParapheurSousTypes)) {
                return ['error' => 'Subtype does not exist in iParapheur'];
            }

            $sousType = $config->getIparapheurSousType();
        }

        // Sending data to the folder
        $editResult = $this->pastellApi->editFolder($config, $idFolder, $title, $sousType);
        if (!empty($editResult['error'])) {
            return ['error' => $editResult['error']];
        }

        // uploading main file
        $uploadResult = $this->pastellApi->uploadMainFile($config, $idFolder, $filePath);
        if (!empty($uploadResult['error'])) {
            return ['error' => $uploadResult['error']];
        }

        $annexCount = 0;
        foreach ($annexes as $annex) {
            $uploadResult = $this->pastellApi->uploadAttachmentFile($config, $idFolder, $annex, $annexCount);
            if (empty($uploadResult['error'])) {
                $annexCount++;
            }
        }

        // Sending folder to iParapheur
        $orientationResult = $this->pastellApi->orientation($config, $idFolder);
        if (!empty($orientationResult['error'])) {
            return ['error' => $orientationResult['error']];
        }

        $info = $this->pastellApi->getFolderDetail($config, $idFolder);
        if (in_array('send-iparapheur', $info['actionPossibles'])) {
            $sendIparapheur = $this->pastellApi->sendIparapheur($config, $idFolder);
            if (!$sendIparapheur) {
                return ['error' => 'L\'action « send-iparapheur »  n\'est pas permise : Le dernier état du document (send-iparapheur) ne permet pas de déclencher cette action'];
            }
        }
        $this->processVisaWorkflow->processVisaWorkflow($resId, false);

        return ['idFolder' => $idFolder];
    }
}
