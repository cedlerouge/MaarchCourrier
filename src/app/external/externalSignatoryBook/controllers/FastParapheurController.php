<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief fastParapheur Controller
 * @author nathan.cheval@edissyum.com
 * @author dev@maarch.org
 */

namespace ExternalSignatoryBook\controllers;

use Attachment\controllers\AttachmentTypeController;
use Attachment\models\AttachmentModel;
use Attachment\models\AttachmentTypeModel;
use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use Entity\models\ListInstanceModel;
use Resource\controllers\StoreController;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use SrcCore\models\DatabaseModel;
use User\models\UserModel;
use SrcCore\models\ValidatorModel;
use Respect\Validation\Validator;
use User\controllers\UserController;
use History\controllers\HistoryController;
use SimpleXMLElement;
use Slim\Http\Request;
use Slim\Http\Response;

/**
    * @codeCoverageIgnore
*/
class FastParapheurController
{
    public function linkUserToFastParapheur(Request $request, Response $response, array $args)
    {
        $body = $request->getParsedBody();
        if (!Validator::notEmpty()->email()->validate($body['fastParapheurUserEmail'] ?? null)) {
            return $response->withStatus(400)->withJson(['errors' => 'body fastParapheurUserEmail is not a valid email address']);
        }
        if (!Validator::notEmpty()->intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'args id is not an integer']);
        }

        $userController = new UserController();
        $error = $userController->hasUsersRights(['id' => $args['id']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $alreadyLinked = UserModel::get([
            'select' => [1],
            'where'  => ['external_id->>\'fastParapheur\' = ?'],
            'data'   => [$body['fastParapheurUserEmail']]
        ]);
        if (!empty($alreadyLinked)) {
            return $response->withStatus(403)->withJson(['errors' => 'FastParapheur user email can only be linked to a single MaarchCourrier user', 'lang' => 'fastParapheurUserAlreadyLinked']);
        }

        $userInfo = UserModel::getById(['select' => ['external_id', 'firstname', 'lastname'], 'id' => $args['id']]);

        $externalId = json_decode($userInfo['external_id'], true);
        $externalId['fastParapheur'] = $body['fastParapheurUserEmail'];

        UserModel::updateExternalId(['id' => $args['id'], 'externalId' => json_encode($externalId)]);

        HistoryController::add([
            'tableName'    => 'users',
            'recordId'     => $GLOBALS['id'],
            'eventType'    => 'ADD',
            'eventId'      => 'userCreation',
            'info'         => _USER_LINKED_TO_FASTPARAPHEUR . " : {$userInfo['firstname']} {$userInfo['lastname']}"
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function unlinkUserToFastParapheur(Request $request, Response $response, array $args)
    {
        if (!Validator::notEmpty()->intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'args id is not an integer']);
        }

        $userController = new UserController();
        $error = $userController->hasUsersRights(['id' => $args['id']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $user = UserModel::getById(['id' => $args['id'], 'select' => ['firstname', 'lastname', 'external_id']]);
        $externalId = json_decode($user['external_id']);
        unset($externalId->fastParapheur);

        UserModel::updateExternalId(['id' => $args['id'], 'externalId' => json_encode($externalId)]);

        HistoryController::add([
            'tableName'    => 'users',
            'recordId'     => $GLOBALS['id'],
            'eventType'    => 'ADD',
            'eventId'      => 'userCreation',
            'info'         => _USER_UNLINKED_TO_FASTPARAPHEUR . " : {$user['firstname']} {$user['lastname']}"
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public static function retrieveSignedMails(array $args)
    {
        $version = $args['version'];
        foreach ($args['idsToRetrieve'][$version] as $resId => $value) {
            if (empty(trim($value['external_id']))) {
                $args['idsToRetrieve'][$version][$resId]['status'] = 'waiting';
                continue;
            }

            $curlReturn = CurlModel::exec([
                'url'           => $args['config']['data']['url'] . '/documents/v2/' . $value['external_id'] . '/history',
                'method'        => 'GET',
                'options'       => [
                    CURLOPT_SSLCERT       => $args['config']['data']['certPath'],
                    CURLOPT_SSLCERTPASSWD => $args['config']['data']['certPass'],
                    CURLOPT_SSLCERTTYPE   => $args['config']['data']['certType']
                ]
            ]);           

            if ($curlReturn['code'] == 404) {
                return ['error' => 'Erreur 404 : ' . $curlReturn['raw']];
            }

            if (!empty($curlReturn['response']['developerMessage']) && !empty($value['res_id_master'])) {
                echo "PJ n° $resId et document original n° {$value['res_id_master']} : {$curlReturn['response']['developerMessage']} " . PHP_EOL;
                continue;
            } elseif (!empty($curlReturn['response']['developerMessage'])) {
                echo "Document principal n° $resId : {$curlReturn['response']['developerMessage']} " . PHP_EOL;
                continue;
            }

            foreach ($curlReturn['response'] as $valueResponse) {    // Loop on all steps of the documents (prepared, send to signature, signed etc...)
                if ($valueResponse['stateName'] == $args['config']['data']['validatedState']) {
                    $response = FastParapheurController::download(['config' => $args['config'], 'documentId' => $value['external_id']]);
                    $args['idsToRetrieve'][$version][$resId]['status'] = 'validated';
                    $args['idsToRetrieve'][$version][$resId]['format'] = 'pdf';
                    $args['idsToRetrieve'][$version][$resId]['encodedFile'] = $response['b64FileContent'];
                    $signatoryInfo = FastParapheurController::getSignatoryUserInfo(['resId' => $args['idsToRetrieve'][$version][$resId]['res_id_master']]);
                    $args['idsToRetrieve'][$version][$resId]['signatory_user_serial_id'] = $signatoryInfo['id'];
                    break;
                } elseif ($valueResponse['stateName'] == $args['config']['data']['refusedState']) {
                    $signatoryInfo = FastParapheurController::getSignatoryUserInfo(['resId' => $args['idsToRetrieve'][$version][$resId]['res_id_master']]);
                    $response = FastParapheurController::getRefusalMessage([
                        'config'        => $args['config'],
                        'documentId'    => $value['external_id'],
                        'res_id'         => $resId,
                        'version'       => $version
                    ]);
                    $args['idsToRetrieve'][$version][$resId]['status'] = 'refused';
                    $args['idsToRetrieve'][$version][$resId]['notes'][] = ['content' => $signatoryInfo['lastname'] . ' ' . $signatoryInfo['firstname'] . ' : ' . $response];
                    break;
                } else {
                    $args['idsToRetrieve'][$version][$resId]['status'] = 'waiting';
                }
            }
        }
        
        return $args['idsToRetrieve'];
    }

    public static function getSignatoryUserInfo(array $args = [])
    {
        $res = DatabaseModel::select([
            'select'    => ['firstname', 'lastname', 'users.id'],
            'table'     => ['listinstance', 'users'],
            'left_join' => ['listinstance.item_id = users.id'],
            'where'     => ['res_id = ?', 'process_date is null', 'difflist_type = ?'],
            'data'      => [$args['resId'], 'VISA_CIRCUIT']
        ])[0];

        return $res;
    }

    public static function processVisaWorkflow(array $args = [])
    {
        $resIdMaster = $args['res_id_master'] ?? $args['res_id'];

        $attachments = AttachmentModel::get(['select' => ['count(1)'], 'where' => ['res_id_master = ?', 'status = ?'], 'data' => [$resIdMaster, 'FRZ']]);
        if ((count($attachments) < 2 && $args['processSignatory']) || !$args['processSignatory']) {
            $visaWorkflow = ListInstanceModel::get([
                'select'  => ['listinstance_id', 'requested_signature'],
                'where'   => ['res_id = ?', 'difflist_type = ?', 'process_date IS NULL'],
                'data'    => [$resIdMaster, 'VISA_CIRCUIT'],
                'orderBY' => ['ORDER BY listinstance_id ASC']
            ]);
    
            if (!empty($visaWorkflow)) {
                foreach ($visaWorkflow as $listInstance) {
                    if ($listInstance['requested_signature']) {
                        // Stop to the first signatory user
                        if ($args['processSignatory']) {
                            ListInstanceModel::update(['set' => ['signatory' => 'true', 'process_date' => 'CURRENT_TIMESTAMP'], 'where' => ['listinstance_id = ?'], 'data' => [$listInstance['listinstance_id']]]);
                        }
                        break;
                    }
                    ListInstanceModel::update(['set' => ['process_date' => 'CURRENT_TIMESTAMP'], 'where' => ['listinstance_id = ?'], 'data' => [$listInstance['listinstance_id']]]);
                }
            }
        }
    }

    public static function upload(array $args)
    {
        ValidatorModel::notEmpty($args, ['circuitId', 'label', 'businessId']);
        ValidatorModel::stringType($args, ['circuitId', 'label', 'businessId']);

        $circuitId    = $args['circuitId'];
        $label        = $args['label'];
        $subscriberId = $args['businessId'];

        // Retrieve the annexes of the attachemnt to sign (other attachment and the original document)
        $annexes = [];
        $annexes['letterbox'] = ResModel::get([
            'select' => ['res_id', 'path', 'filename', 'docserver_id', 'format', 'category_id', 'external_id', 'integrations'],
            'where'  => ['res_id = ?'],
            'data'   => [$args['resIdMaster']]
        ]);

        if (!empty($annexes['letterbox'][0]['docserver_id'])) {
            $adrMainInfo = ConvertPdfController::getConvertedPdfById(['resId' => $args['resIdMaster'], 'collId' => 'letterbox_coll']);
            $letterboxPath = DocserverModel::getByDocserverId(['docserverId' => $adrMainInfo['docserver_id'], 'select' => ['path_template']]);
            $annexes['letterbox'][0]['filePath'] = $letterboxPath['path_template'] . str_replace('#', '/', $adrMainInfo['path']) . $adrMainInfo['filename'];
        }

        $attachments = AttachmentModel::get([
            'select'    => [
                'res_id', 'docserver_id', 'path', 'filename', 'format', 'attachment_type', 'fingerprint'
            ],
            'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP', 'SEND_MASS')", "in_signature_book = 'true'"],
            'data'      => [$args['resIdMaster'], ['signed_response']]
        ]);

        $attachmentTypes = AttachmentTypeModel::get(['select' => ['type_id', 'signable']]);
        $attachmentTypes = array_column($attachmentTypes, 'signable', 'type_id');
        foreach ($attachments as $key => $value) {
            if (!$attachmentTypes[$value['attachment_type']]) {
                $annexeAttachmentPath = DocserverModel::getByDocserverId(['docserverId' => $value['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
                $value['filePath']    = $annexeAttachmentPath['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $value['path']) . $value['filename'];

                $docserverType = DocserverTypeModel::getById(['id' => $annexeAttachmentPath['docserver_type_id'], 'select' => ['fingerprint_mode']]);
                $fingerprint = StoreController::getFingerPrint(['filePath' => $value['filePath'], 'mode' => $docserverType['fingerprint_mode']]);
                if ($value['fingerprint'] != $fingerprint) {
                    return ['error' => 'Fingerprints do not match'];
                }

                unset($attachments[$key]);
                $annexes['attachments'][] = $value;
            }
        }
        // END annexes

        $attachmentToFreeze = [];
        foreach ($attachments as $attachment) {
            $resId  = $attachment['res_id'];
            $collId = 'attachments_coll';
            
            $response = FastParapheurController::uploadFile([
                'resId'        => $resId,
                'collId'       => $collId,
                'resIdMaster'  => $args['resIdMaster'],
                'annexes'      => $annexes,
                'circuitId'    => $circuitId,
                'label'        => $label,
                'subscriberId' => $subscriberId,
                'config'       => $args['config']
            ]);

            if (!empty($response['error'])) {
                return $response;
            } else {
                $attachmentToFreeze[$collId][$resId] = $response['success'];
            }
        }

        // Send main document if in signature book
        if (!empty($annexes['letterbox'][0])) {
            $mainDocumentIntegration = json_decode($annexes['letterbox'][0]['integrations'], true);
            $externalId              = json_decode($annexes['letterbox'][0]['external_id'], true);
            if ($mainDocumentIntegration['inSignatureBook'] && empty($externalId['signatureBookId'])) {
                $resId  = $annexes['letterbox'][0]['res_id'];
                $collId = 'letterbox_coll';
                unset($annexes['letterbox']);
    
                $response = FastParapheurController::uploadFile([
                    'resId'        => $resId,
                    'collId'       => $collId,
                    'resIdMaster'  => $args['resIdMaster'],
                    'annexes'      => $annexes,
                    'circuitId'    => $circuitId,
                    'label'        => $label,
                    'subscriberId' => $subscriberId,
                    'config'       => $args['config']
                ]);

                if (!empty($response['error'])) {
                    return $response;
                } else {
                    $attachmentToFreeze[$collId][$resId] = $response['success'];
                }
            }
        }

        return ['sended' => $attachmentToFreeze];
    }

    public static function uploadFile(array $args)
    {
        $adrInfo = ConvertPdfController::getConvertedPdfById(['resId' => $args['resId'], 'collId' => $args['collId']]);
        if (empty($adrInfo['docserver_id']) || strtolower(pathinfo($adrInfo['filename'], PATHINFO_EXTENSION)) != 'pdf') {
            return ['error' => 'Document ' . $args['resIdMaster'] . ' is not converted in pdf'];
        }
        $attachmentPath     =  DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id'], 'select' => ['path_template']]);
        $attachmentFilePath = $attachmentPath['path_template'] . str_replace('#', '/', $adrInfo['path']) . $adrInfo['filename'];
        $attachmentFileName = 'projet_courrier_' . $args['resIdMaster'] . '_' . rand(0001, 9999) . '.pdf';

        $zip         = new \ZipArchive();
        $tmpPath     = CoreConfigModel::getTmpPath();
        $zipFilePath = $tmpPath . DIRECTORY_SEPARATOR
            . $attachmentFileName . '.zip';  // The zip file need to have the same name as the attachment we want to sign

        if ($zip->open($zipFilePath, \ZipArchive::CREATE)!==true) {
            return ['error' => "Can not open file : <$zipFilePath>\n"];
        }
        $zip->addFile($attachmentFilePath, $attachmentFileName);

        if (!empty($args['annexes']['letterbox'])) {
            $zip->addFile($args['annexes']['letterbox'][0]['filePath'], 'document_principal.' . $args['annexes']['letterbox'][0]['format']);
        }

        if (isset($args['annexes']['attachments'])) {
            for ($j = 0; $j < count($args['annexes']['attachments']); $j++) {
                $zip->addFile(
                    $args['annexes']['attachments'][$j]['filePath'],
                    'PJ_' . ($j + 1) . '.' . $args['annexes']['attachments'][$j]['format']
                );
            }
        }

        $zip->close();

        $b64Attachment = file_get_contents($zipFilePath);
        $fileName      = $attachmentFileName . '.zip';
        $circuitId     = str_replace('.', '-', $args['circuitId']);
        
        $curlReturn = CurlModel::exec([
            'url'           => $args['config']['data']['url'] . '/documents/v2/' . $args['subscriberId'] . '/' . $circuitId . '/upload',
            'method'        => 'POST',
            'options'       => [
                CURLOPT_SSLCERT       => $args['config']['data']['certPath'],
                CURLOPT_SSLCERTPASSWD => $args['config']['data']['certPass'],
                CURLOPT_SSLCERTTYPE   => $args['config']['data']['certType']
            ],
            'multipartBody' => [
                'content'   => ['isFile' => true, 'filename' => $fileName, 'content' => $b64Attachment],
                'label'     => $args['label'],
                'comment'   => ""
            ]
        ]);

        if ($curlReturn['code'] == 404) {
            return ['error' => 'Erreur 404 : ' . $curlReturn['raw']];
        } elseif (!empty($curlReturn['errors'])) {
            return ['error' => $curlReturn['errors']];
        } elseif (!empty($curlReturn['response']['developerMessage'])) {
            return ['error' => $curlReturn['response']['developerMessage']];
        }

        FastParapheurController::processVisaWorkflow(['res_id_master' => $args['resIdMaster'], 'processSignatory' => false]);
        $documentId = $curlReturn['response'];
        return ['success' => (string)$documentId];
    }

    public static function uploadWithSteps(array $args)
    {
        ValidatorModel::notEmpty($args, ['resIdMaster', 'steps', 'config']);
        ValidatorModel::intType($args, ['resIdMaster']);
        ValidatorModel::arrayType($args, ['steps', 'config']);

        $resource = ResModel::getById([
            'resId'  => $args['resIdMaster'],
            'select' => ['res_id', 'subject', 'integrations', 'docserver_id', 'path', 'filename', 'category_id', 'format', 'external_id']
        ]);

        if (empty($resource)) {
            return ['error' => 'resource does not exist', 'code' => 400];
        }

        if (!empty($resource['external_id']['fastParapheur'])) {
            return ['error' => 'resource is already in fastParapheur', 'code' => 400];
        }

        $sentAttachments = [];
        $sentMainDocument = [];
        $docservers = DocserverModel::get(['select' => ['docserver_id', 'path_template']]);
        $docservers = array_column($docservers, 'path_template', 'docserver_id');
        $attachmentTypeSignable = AttachmentTypeModel::get(['select' => ['type_id', 'signable']]);
        $attachmentTypeSignable = array_column($attachmentTypeSignable, 'signable', 'type_id');

        $attachments = AttachmentModel::get([
            'select'    => [
                'res_id', 'title', 'docserver_id', 'path', 'filename', 'format', 'attachment_type', 'fingerprint'
            ],
            'where'     => ['res_id_master = ?', 'attachment_type not in (?)', 'status not in (\'DEL\', \'OBS\', \'FRZ\', \'TMP\', \'SEND_MASS\')', 'in_signature_book is true'],
            'data'      => [$args['resIdMaster'], AttachmentTypeController::UNLISTED_ATTACHMENT_TYPES]
        ]);

        if (empty($docservers[$resource['docserver_id']])) {
            return ['error' => 'resource docserver does not exist', 'code' => 500];
        }
        $resource['integrations'] = json_decode($resource['integrations'], true);
        if ($resource['integrations']['inSignatureBook']) {
            $sentMainDocument = [
                'comment'  => $resource['subject'],
                'signable' => $resource['category_id'] == 'outgoing',
                'path'     => $docservers[$resource['docserver_id']] . $resource['path'] . $resource['filename']
            ];
        }
        foreach ($attachments as $attachment) {
            $sentAttachments[] = [
                'comment'  => $attachment['title'],
                'signable' => $attachmentTypeSignable[$attachment['attachment_type']],
                'path'     => $docservers[$attachment['docserver_id']] . $attachment['path'] . $attachment['filename']
            ];
        }

        $upload = [
            'doc'        => null,
            'comment'    => '',
            'appendices' => []
        ];
        if (!empty($sentMainDocument) && is_file($sentMainDocument['path'])) {
            if ($sentMainDocument['signable']) {
                $upload['doc'] = $sentMainDocument['path'];
                $upload['comment'] = $sentMainDocument['comment'];
            } else {
                $upload['appendices'][] = $sentMainDocument['path'];
            }
        }
        foreach ($sentAttachments as $sentAttachment) {
            if (!is_file($sentAttachment['path'])) {
                continue;
            }
            if ($sentAttachment['signable']) {
                $upload['doc'] = $sentAttachment['path'];
                $upload['comment'] = $sentAttachment['comment'];
            } else {
                $upload['appendices'][] = $sentAttachment['path'];
            }
        }
        if (empty($upload['doc'])) {
            return ['error' => 'resource has nothing to sign', 'code' => 400];
        }

        $circuit = [
            'type'  => 'BUREAUTIQUE_PDF',
            'steps' => []
        ];
        $otpInfo = [];
        foreach ($args['steps'] as $index => $step) {
            if ($step['type'] == 'user' && !empty($step['id'])) {
                $user = UserModel::getById(['id' => $step['id'], 'select' => ['external_id->>\'fastParapheur\' as "fastParapheurEmail"']]);
                if (empty($user['fastParapheurEmail'])) {
                    return ['errors' => 'no FastParapheurEmail for user ' . $step['id'], 'code' => 400];
                }
                $circuit['steps'][] = [
                    'step'    => 'signature',
                    'members' => [$user['fastParapheurEmail']]
                ];
            } elseif ($step['type'] == 'externalOTP'
                      && Validator::notEmpty()->phone()->validate($step['phone'])
                      && Validator::notEmpty()->email()->validate($step['email'])
                      && Validator::notEmpty()->stringType()->validate($step['firstname'])
                      && Validator::notEmpty()->stringType()->validate($step['lastname'])) {
                $circuit['steps'][] = [
                    'step'    => 'OTPSignature',
                    'members' => [$step['email']]
                ];
                $otpInfo['OTP_firstname_' . $index]   = $step['firstname'];
                $otpInfo['OTP_lastname_' . $index]    = $step['lastname'];
                $otpInfo['OTP_phonenumber_' . $index] = $step['phone'];
                $otpInfo['OTP_email_' . $index]       = $step['email'];
            } else {
                return ['error' => 'step number ' . ($index + 1) . ' is invalid', 'code' => 400];
            }
        }
        if (empty($step)) {
            return ['error' => 'steps are empty or invalid', 'code' => 400];
        }

        $otpInfoXML = null;
        if (!empty($otpInfo)) {
            $otpInfoXML = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?> <meta-data-list></meta-data-list>');
            foreach ($otpInfo as $name => $value) {
                $metadataElement = $otpInfoXML->addChild('meta-data');
                $metadataElement->addAttribute($name, $value);
            }
        }

        $curlReturn = CurlModel::exec([
            'method'  => 'POST',
            'url'     => $args['config']['url'] . '/documents/ondemand/' . $args['businessId'] . '/upload',
            'options' => [
                CURLOPT_SSLCERT       => $args['config']['certPath'],
                CURLOPT_SSLCERTPASSWD => $args['config']['certPass'],
                CURLOPT_SSLCERTTYPE   => $args['config']['certType']
            ],
            'multipartBody' => [
                'comment' => $upload['comment'],
                'doc'     => ['isFile' => true, 'filename' => basename($upload['doc']), 'content' => file_get_contents($upload['doc'])],
                // TODO: annexes -> zip from $upload['appendices']
                'circuit' => json_encode($circuit)
            ]
        ]);
        if ($curlReturn['code'] != 200) {
            return ['error' => $curlReturn['errors'], 'code' => $curlReturn['code']];
        }
        $fastParapheurDocId = (string)$curlReturn['response'];

        if (!empty($otpInfoXML)) {
            $curlReturn = CurlModel::exec([
                'method'  => 'PUT',
                'url'     => $args['config']['url'] . '/documents/v2/otp/' . $fastParapheurDocId . '/metadata/define',
                'options' => [
                    CURLOPT_SSLCERT       => $args['config']['certPath'],
                    CURLOPT_SSLCERTPASSWD => $args['config']['certPass'],
                    CURLOPT_SSLCERTTYPE   => $args['config']['certType']
                ],
                'multipartBody' => [
                    'otpinformation' => ['isFile' => true, 'filename' => 'otpinfo.xml', 'content' => $otpInfoXML->asXML()]
                ]
            ]);
            if ($curlReturn['code'] != 200) {
                return ['error' => $curlReturn['errors'], 'code' => $curlReturn['code']];
            }
        }

        return [
            'sended' => [
                'letterbox_coll' => [
                    'resIdMaster' => [
                        $args['resIdMaster'] => $fastParapheurDocId
                    ]
                ]
            ]
        ];
    }

    public static function download(array $args)
    {
        $curlReturn = CurlModel::exec([
            'url'           => $args['config']['data']['url'] . '/documents/v2/' . $args['documentId'] . '/download',
            'method'        => 'GET',
            'options'       => [
                CURLOPT_SSLCERT       => $args['config']['data']['certPath'],
                CURLOPT_SSLCERTPASSWD => $args['config']['data']['certPass'],
                CURLOPT_SSLCERTTYPE   => $args['config']['data']['certType'],
            ],
            'fileResponse'  => true
        ]);

        if ($curlReturn['code'] == 404) {
            echo "Erreur 404 : {$curlReturn['raw']}";
            return false;
        } elseif (!empty($curlReturn['response']['developerMessage'])) {
            echo $curlReturn['response']['developerMessage'];
            return false;
        } else {
            return ['b64FileContent' => base64_encode($curlReturn['response'])];
        }
    }

    public static function sendDatas(array $args)
    {
        $config = $args['config'];
        // We need the SIRET field and the user_id of the signatory user's primary entity
        $signatory = DatabaseModel::select([
            'select'    => ['user_id', 'external_id', 'entities.entity_label'],
            'table'     => ['listinstance', 'users_entities', 'entities'],
            'left_join' => ['item_id = user_id', 'users_entities.entity_id = entities.entity_id'],
            'where'     => ['res_id = ?', 'item_mode = ?', 'process_date is null'],
            'data'      => [$args['resIdMaster'], 'sign']
        ])[0];
        $redactor = DatabaseModel::select([
            'select'    => ['short_label'],
            'table'     => ['res_view_letterbox', 'users_entities', 'entities'],
            'left_join' => ['dest_user = user_id', 'users_entities.entity_id = entities.entity_id'],
            'where'     => ['res_id = ?'],
            'data'      => [$args['resIdMaster']]
        ])[0];

        $signatory['business_id'] = json_decode($signatory['external_id'], true)['fastParapheurSubscriberId'];
        if (empty($signatory['business_id']) || substr($signatory['business_id'], 0, 3) == 'org') {
            $signatory['business_id'] = $config['data']['subscriberId'];
        }

        $user = [];
        if (!empty($signatory['user_id'])) {
            $user = UserModel::getById(['id' => $signatory['user_id'], 'select' => ['user_id']]);
        }

        if (empty($user['user_id'])) {
            return ['error' => _VISA_WORKFLOW_NOT_FOUND];
        }

        // check if circuidId is an email
        if (Validator::email()->notEmpty()->validate($user['user_id'])) {
            $user['user_id'] = explode("@", $user['user_id'])[0];
        }

        if (empty($signatory['business_id'])) {
            return ['error' => _NO_BUSINESS_ID];
        }

        if (empty($redactor['short_label'])) {
            return ['error' => _VISA_WORKFLOW_ENTITY_NOT_FOUND];
        }

        return FastParapheurController::upload([
            'config'        => $config, 
            'resIdMaster'   => $args['resIdMaster'], 
            'businessId'    => $signatory['business_id'], 
            'circuitId'     => $user['user_id'], 
            'label'         => $redactor['short_label']
        ]);
    }

    public static function getRefusalMessage(array $args)
    {
        $curlReturn = CurlModel::exec([
            'url'           => $args['config']['data']['url'] . '/documents/v2/' . $args['documentId'] . '/comments/refusal',
            'method'        => 'GET',
            'options'       => [
                CURLOPT_SSLCERT       => $args['config']['data']['certPath'],
                CURLOPT_SSLCERTPASSWD => $args['config']['data']['certPass'],
                CURLOPT_SSLCERTTYPE   => $args['config']['data']['certType']
            ]
        ]);

        $response = "";
        if (!empty($curlReturn['response']['developerMessage']) && $args['version'] == 'noVersion') {
            $attachmentName = AttachmentModel::getById(['select' => ['title'], 'id' => $args['res_id']]);
            $str = explode(':', $curlReturn['response']['developerMessage']);
            unset($str[0]);
            $response = _FOR_ATTACHMENT . " \"{$attachmentName['title']}\". " . implode('.', $str);

        } elseif (!empty($curlReturn['response']['developerMessage'])) {
            $str = explode(':', $curlReturn['response']['developerMessage']);
            unset($str[0]);
            $response = _FOR_MAIN_DOC . ". " . implode('.', $str);

        } elseif (!empty($curlReturn['response']['comment']) && $args['version'] == 'noVersion') {
            $attachmentName = AttachmentModel::getById(['select' => ['title'], 'id' => $args['res_id']]);
            $response = _FOR_ATTACHMENT . " \"{$attachmentName['title']}\". " . $curlReturn['response']['comment'];

        } elseif (!empty($curlReturn['response']['comment'])) {
            $response = _FOR_MAIN_DOC . ". " . $curlReturn['response']['comment'];
        }
        return $response;
    }

    public static function getUsers(array $args)
    {
        $businessId = $args['businessId'] ?? $args['config']['subscriberId'];
        $curlReturn = CurlModel::exec([
            'url'           => $args['config']['url'] . '/exportUsersData?siren=' . urlencode($businessId),
            'method'        => 'GET',
            'options'       => [
                CURLOPT_SSLCERT       => $args['config']['certPath'],
                CURLOPT_SSLCERTPASSWD => $args['config']['certPass'],
                CURLOPT_SSLCERTTYPE   => $args['config']['certType']
            ]
        ]);

        if (empty($curlReturn['response']['users'])) {
            return [];
        }

        $users = [];
        foreach ($curlReturn['response']['users'] as $user) {
            $users[] = [
                'idToDisplay' => trim($user['prenom'] . ' ' . $user['nom']),
                'email'       => trim($user['email'])
            ];
        }

        return $users;
    }
}
