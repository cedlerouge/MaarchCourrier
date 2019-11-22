<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Merge Controller
 *
 * @author dev@maarch.org
 */

namespace ContentManagement\controllers;

use CustomField\models\ResourceCustomFieldModel;
use Doctype\models\DoctypeModel;
use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use IndexingModel\models\IndexingModelModel;
use Note\models\NoteModel;
use Resource\models\ResModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

include_once('vendor/tinybutstrong/opentbs/tbs_plugin_opentbs.php');


class MergeController
{
    const OFFICE_EXTENSIONS = ['odt', 'ods', 'odp', 'xlsx', 'pptx', 'docx', 'odf'];

    public static function mergeDocument(array $args)
    {
        ValidatorModel::notEmpty($args, ['data']);
        ValidatorModel::arrayType($args, ['data']);
        ValidatorModel::stringType($args, ['path', 'content']);
        ValidatorModel::notEmpty($args['data'], ['userId']);
        ValidatorModel::intVal($args['data'], ['userId']);

        $tbs = new \clsTinyButStrong();
        $tbs->NoErr = true;
        $tbs->PlugIn(TBS_INSTALL, OPENTBS_PLUGIN);

        if (!empty($args['path'])) {
            $pathInfo = pathinfo($args['path']);
            $extension = $pathInfo['extension'];
        } else {
            $tbs->Source = $args['content'];
            $extension = 'unknow';
            $args['path'] = null;
        }

        if (!empty($args['path'])) {
            if ($extension == 'odt') {
                $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
    //            $tbs->LoadTemplate("{$args['path']}#content.xml;styles.xml", OPENTBS_ALREADY_UTF8);
            } elseif ($extension == 'docx') {
                $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
    //            $tbs->LoadTemplate("{$args['path']}#word/header1.xml;word/footer1.xml", OPENTBS_ALREADY_UTF8);
            } else {
                $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
            }
        }

        $dataToBeMerge = MergeController::getDataForMerge($args['data']);

        $pages = 1;
        if ($extension == 'xlsx') {
            $pages = $tbs->PlugIn(OPENTBS_COUNT_SHEETS);
        }

        for ($i = 0; $i < $pages; ++$i) {
            if ($extension == 'xlsx') {
                $tbs->PlugIn(OPENTBS_SELECT_SHEET, $i + 1);
            }
            foreach ($dataToBeMerge as $key => $value) {
                $tbs->MergeField($key, $value);
            }
        }

        if (in_array($extension, MergeController::OFFICE_EXTENSIONS)) {
            $tbs->Show(OPENTBS_STRING);
        } else {
            $tbs->Show(TBS_NOTHING);
        }

        return ['encodedDocument' => base64_encode($tbs->Source)];
    }

    private static function getDataForMerge(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['resId', 'userId']);

        //Resource
        if (!empty($args['resId'])) {
            $resource = ResModel::getById(['select' => ['*'], 'resId' => $args['resId']]);
        } else {
            if (!empty($args['modelId'])) {
                $indexingModel = IndexingModelModel::getById(['id' => $args['modelId'], 'select' => ['category']]);
            }
            if (!empty($args['initiator'])) {
                $entity = EntityModel::getById(['id' => $args['initiator'], 'select' => ['entity_id']]);
                $args['initiator'] = $entity['entity_id'];
            }
            if (!empty($args['destination'])) {
                $entity = EntityModel::getById(['id' => $args['destination'], 'select' => ['entity_id']]);
                $args['destination'] = $entity['entity_id'];
            }
            $resource = [
                'model_id'              => $args['modelId'] ?? null,
                'alt_identifier'        => '[res_letterbox.alt_identifier]',
                'category_id'           => $indexingModel['category'] ?? null,
                'type_id'               => $args['doctype'] ?? null,
                'subject'               => $args['subject'] ?? null,
                'destination'           => $args['destination'] ?? null,
                'initiator'             => $args['initiator'] ?? null,
                'doc_date'              => $args['documentDate'] ?? null,
                'admission_date'        => $args['arrivalDate'] ?? null,
                'departure_date'        => $args['departureDate'] ?? null,
                'process_limit_date'    => $args['processLimitDate'] ?? null,
                'barcode'               => $args['barcode'] ?? null,
                'origin'                => $args['origin'] ?? null
            ];
        }
        $allDates = ['doc_date', 'departure_date', 'admission_date', 'process_limit_date', 'opinion_limit_date', 'closing_date', 'creation_date'];
        foreach ($allDates as $date) {
            $resource[$date] = TextFormatModel::formatDate($resource[$date], 'd/m/Y');
        }
        $resource['category_id'] = ResModel::getCategoryLabel(['categoryId' => $resource['category_id']]);

        if (!empty($resource['type_id'])) {
            $doctype = DoctypeModel::getById(['id' => $resource['type_id'], 'select' => ['process_delay', 'process_mode', 'description']]);
            $resource['type_label'] = $doctype['description'];
            $resource['process_delay'] = $doctype['process_delay'];
            $resource['process_mode'] = $doctype['process_mode'];
        }

        if (!empty($resource['initiator'])) {
            $initiator = EntityModel::getByEntityId(['entityId' => $resource['initiator'], 'select' => ['*']]);
            if (!empty($initiator)) {
                foreach ($initiator as $key => $value) {
                    $resource["initiator_{$key}"] = $value;
                }
            }
            $initiator['path'] = EntityModel::getEntityPathByEntityId(['entityId' => $resource['initiator'], 'path' => '']);
            if (!empty($initiator['parent_entity_id'])) {
                $parentInitiator = EntityModel::getByEntityId(['entityId' => $initiator['parent_entity_id'], 'select' => ['*']]);
            }
        }
        if (!empty($resource['destination'])) {
            $destination = EntityModel::getByEntityId(['entityId' => $resource['destination'], 'select' => ['*']]);
            $destination['path'] = EntityModel::getEntityPathByEntityId(['entityId' => $resource['destination'], 'path' => '']);
            if (!empty($destination['parent_entity_id'])) {
                $parentDestination = EntityModel::getByEntityId(['entityId' => $destination['parent_entity_id'], 'select' => ['*']]);
            }
        }

        //Attachment
        $attachment = [
            'chrono'    => '[attachment.chrono]',
            'title'     => $args['attachment_title'] ?? null
        ];

        //User
        $currentUser = UserModel::getById(['id' => $args['userId'], 'select' => ['firstname', 'lastname', 'phone', 'mail', 'initials']]);
        $currentUserPrimaryEntity = UserModel::getPrimaryEntityById(['id' => $args['userId'], 'select' => ['entities.*', 'users_entities.user_role as role']]);
        if (!empty($currentUserPrimaryEntity)) {
            $currentUserPrimaryEntity['path'] = EntityModel::getEntityPathByEntityId(['entityId' => $currentUserPrimaryEntity['entity_id'], 'path' => '']);
        }

        //Visas
        $visas = '';
        if (!empty($args['resId'])) {
            $visaWorkflow = ListInstanceModel::get([
                'select'    => ['item_id'],
                'where'     => ['difflist_type = ?', 'res_id = ?'],
                'data'      => ['VISA_CIRCUIT', $args['resId']],
                'orderBy'   => ['listinstance_id']
            ]);
            foreach ($visaWorkflow as $value) {
                $labelledUser = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                $primaryentity = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']]);
                $visas .= "{$labelledUser} ({$primaryentity})\n";
            }
        }

        //Opinions
        $opinions = '';
        if (!empty($args['resId'])) {
            $opinionWorkflow = ListInstanceModel::get([
                'select'    => ['item_id'],
                'where'     => ['difflist_type = ?', 'res_id = ?'],
                'data'      => ['AVIS_CIRCUIT', $args['resId']],
                'orderBy'   => ['listinstance_id']
            ]);
            foreach ($opinionWorkflow as $value) {
                $labelledUser = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                $primaryentity = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']]);
                $opinions .= "{$labelledUser} ({$primaryentity})\n";
            }
        }

        //Copies
        $copies = '';
        if (!empty($args['resId'])) {
            $copyWorkflow = ListInstanceModel::get([
                'select'    => ['item_id', 'item_type'],
                'where'     => ['difflist_type = ?', 'res_id = ?', 'item_mode = ?'],
                'data'      => ['entity_id', $args['resId'], 'cc'],
                'orderBy'   => ['listinstance_id']
            ]);
            foreach ($copyWorkflow as $value) {
                if ($value['item_type'] == 'user_id') {
                    $labelledUser  = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                    $primaryentity = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']]);
                    $label         = "{$labelledUser} ({$primaryentity})";
                } else {
                    $entity = EntityModel::getByEntityId(['entityId' => $value['item_id'], 'select' => ['entity_label']]);
                    $label = $entity['entity_label'];
                }
                $copies .= "{$label}\n";
            }
        }

        //Contact
//        $contact = ContactModel::getOnView(['select' => ['*'], 'where' => ['ca_id = ?'], 'data' => [$args['contactAddressId']]])[0];
//        $contact['postal_address'] = ContactController::formatContactAddressAfnor($contact);
//        $contact['title'] = ContactModel::getCivilityLabel(['civilityId' => $contact['title']]);
//        if (empty($contact['title'])) {
//            $contact['title'] = ContactModel::getCivilityLabel(['civilityId' => $contact['contact_title']]);
//        }
//        if (empty($contact['firstname'])) {
//            $contact['firstname'] = $contact['contact_firstname'];
//        }
//        if (empty($contact['lastname'])) {
//            $contact['lastname'] = $contact['contact_lastname'];
//        }
//        if (empty($contact['function'])) {
//            $contact['function'] = $contact['contact_function'];
//        }
//        if (empty($contact['other_data'])) {
//            $contact['other_data'] = $contact['contact_other_data'];
//        }

        //Notes
        $mergedNote = '';
        if (!empty($args['resId'])) {
            $notes = NoteModel::getByUserIdForResource(['select' => ['note_text', 'creation_date', 'user_id'], 'resId' => $args['resId'], 'userId' => $args['userId']]);
            foreach ($notes as $note) {
                $labelledUser = UserModel::getLabelledUserById(['id' => $note['user_id']]);
                $creationDate = TextFormatModel::formatDate($note['creation_date'], 'd/m/Y');
                $mergedNote .= "{$labelledUser} : {$creationDate} : {$note['note_text']}\n";
            }
        }

        //CustomFields
        if (!empty($args['resId'])) {
            $customs = ResourceCustomFieldModel::get([
                'select'    => ['custom_field_id, value'],
                'where'     => ['res_id = ?'],
                'data'      => [$args['resId']],
                'orderBy'   => ['value']
            ]);
            foreach ($customs as $custom) {
                $decoded = json_decode($custom['value']);

                if (is_array($decoded)) {
                    $resource['customField_' . $custom['custom_field_id']] = implode("\n", $decoded);
                } else {
                    $resource['customField_' . $custom['custom_field_id']] = $decoded;
                }
            }
        } else {
            if (!empty($args['customFields'])) {
                foreach ($args['customFields'] as $key => $customField) {
                    if (is_array($customField)) {
                        $resource['customField_' . $key] = implode("\n", $customField);
                    } else {
                        $resource['customField_' . $key] = $customField;
                    }
                }
            }
        }

        //Datetime
        $datetime = [
            'date'  => date('d-m-Y'),
            'time'  => date('H:i')
        ];

        $dataToBeMerge['res_letterbox']     = $resource;
        $dataToBeMerge['initiator']         = empty($initiator) ? [] : $initiator;
        $dataToBeMerge['parentInitiator']   = empty($parentInitiator) ? [] : $parentInitiator;
        $dataToBeMerge['destination']       = empty($destination) ? [] : $destination;
        $dataToBeMerge['parentDestination'] = empty($parentDestination) ? [] : $parentDestination;
        $dataToBeMerge['attachment']        = $attachment;
        $dataToBeMerge['user']              = $currentUser;
        $dataToBeMerge['userPrimaryEntity'] = $currentUserPrimaryEntity;
        $dataToBeMerge['visas']             = $visas;
        $dataToBeMerge['opinions']          = $opinions;
        $dataToBeMerge['copies']            = $copies;
        $dataToBeMerge['contact']           = [];
        $dataToBeMerge['notes']             = $mergedNote;
        $dataToBeMerge['datetime']          = $datetime;

        return $dataToBeMerge;
    }

    public static function mergeChronoDocument(array $args)
    {
        ValidatorModel::stringType($args, ['path', 'content', 'chrono']);

        $tbs = new \clsTinyButStrong();
        $tbs->NoErr = true;
        $tbs->PlugIn(TBS_INSTALL, OPENTBS_PLUGIN);

        if (!empty($args['path'])) {
            $pathInfo = pathinfo($args['path']);
            $extension = $pathInfo['extension'];
        } else {
            $tbs->Source = $args['content'];
            $extension = 'unknow';
            $args['path'] = null;
        }

        if (!empty($args['path'])) {
            if ($extension == 'odt') {
                $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
                //            $tbs->LoadTemplate("{$args['path']}#content.xml;styles.xml", OPENTBS_ALREADY_UTF8);
            } elseif ($extension == 'docx') {
                $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
                //            $tbs->LoadTemplate("{$args['path']}#word/header1.xml;word/footer1.xml", OPENTBS_ALREADY_UTF8);
            } else {
                $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
            }
        }

        $tbs->MergeField('res_letterbox', ['alt_identifier' => $args['chrono']]);
        $tbs->MergeField('attachment', ['chrono' => $args['chrono']]);

        if (in_array($extension, MergeController::OFFICE_EXTENSIONS)) {
            $tbs->Show(OPENTBS_STRING);
        } else {
            $tbs->Show(TBS_NOTHING);
        }

        return ['encodedDocument' => base64_encode($tbs->Source)];
    }
}
