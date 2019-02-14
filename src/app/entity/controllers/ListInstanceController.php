<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Instance Controller
 * @author dev@maarch.org
 */

namespace Entity\controllers;

use Entity\models\ListInstanceModel;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator;
use Resource\controllers\ResController;
use Entity\models\EntityModel;
use SrcCore\models\DatabaseModel;
use User\models\UserModel;
use Resource\models\ResModel;
use Group\models\ServiceModel;

class ListInstanceController
{
    public function getById(Request $request, Response $response, array $aArgs)
    {
        $listinstance = ListInstanceModel::getById(['id' => $aArgs['id']]);

        return $response->withJson($listinstance);
    }

    public function getListByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        $listinstances = ListInstanceModel::getListByResId(['select' => ['listinstance_id', 'sequence', 'CASE WHEN item_mode=\'cc\' THEN \'copy\' ELSE item_mode END', 'item_id', 'item_type', 'firstname as item_firstname', 'lastname as item_lastname', 'entity_label as item_entity', 'viewed', 'process_date', 'process_comment', 'signatory', 'requested_signature'], 'id' => $aArgs['resId']]);
        
        $roles = EntityModel::getRoles();

        $listinstancesFormat = [];
        foreach ($listinstances as $key2 => $listinstance) {
            foreach ($roles as $key => $role) {
                if ($role['id'] == $listinstance['item_mode']) {
                    $listinstancesFormat[$role['label']][] = $listinstance;
                }
            }
        }

        return $response->withJson($listinstancesFormat);
    }

    public function getVisaCircuitByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        $listinstances = ListInstanceModel::getVisaCircuitByResId(['select' => ['listinstance_id', 'sequence', 'item_id', 'item_type', 'firstname as item_firstname', 'lastname as item_lastname', 'entity_label as item_entity', 'viewed', 'process_date', 'process_comment', 'signatory', 'requested_signature'], 'id' => $aArgs['resId']]);
        
        return $response->withJson($listinstances);
    }

    public function getAvisCircuitByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        $listinstances = ListInstanceModel::getAvisCircuitByResId(['select' => ['listinstance_id', 'sequence', 'item_id', 'item_type', 'firstname as item_firstname', 'lastname as item_lastname', 'entity_label as item_entity', 'viewed', 'process_date', 'process_comment'], 'id' => $aArgs['resId']]);
        
        return $response->withJson($listinstances);
    }

    public function update(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (empty($data)) {
            return $response->withStatus(400)->withJson(['errors' => 'listinstances is missing or is empty']);
        }

        DatabaseModel::beginTransaction();

        foreach ($data as $ListInstanceByRes) {
            if (empty($ListInstanceByRes['resId'])) {
                DatabaseModel::rollbackTransaction();
                return $response->withStatus(400)->withJson(['errors' => 'resId is empty']);
            }

            ListInstanceModel::delete([
                'where' => ['res_id = ?', 'difflist_type = ?'],
                'data'  => [$ListInstanceByRes['resId'], 'entity_id']
            ]);

            foreach ($ListInstanceByRes['listinstances'] as $instance) {
                if (empty($instance['res_id']) || empty($instance['item_id']) || empty($instance['item_type']) || empty($instance['item_mode']) || empty($instance['difflist_type'])) {
                    DatabaseModel::rollbackTransaction();
                    return $response->withStatus(400)->withJson(['errors' => 'Some data are empty']);
                }
                
                unset($instance['listinstance_id']);
                unset($instance['requested_signature']);
                unset($instance['signatory']);
                
                if ($instance['item_type'] == 'user_id') {
                    $user = UserModel::getByLogin(['login' => $instance['item_id']]);
                    if (empty($user) || $user['status'] != "OK") {
                        DatabaseModel::rollbackTransaction();
                        return $response->withStatus(400)->withJson(['errors' => 'User not found or not active']);
                    }
                } elseif ($instance['item_type'] == 'entity_id') {
                    $entity = EntityModel::getByEntityId(['entityId' => $instance['item_id']]);
                    if (empty($entity) || $entity['enabled'] != "Y") {
                        DatabaseModel::rollbackTransaction();
                        return $response->withStatus(400)->withJson(['errors' => 'Entity not found or not active']);
                    }
                }

                ListInstanceModel::create($instance);

                if ($instance['item_mode'] == 'dest') {
                    $entity = UserModel::getPrimaryEntityByUserId(['userId' => $instance['item_id']]);
                    ResModel::update([
                        'set'   => ['dest_user' => $instance['item_id'], 'destination' => $entity['entity_id']],
                        'where' => ['res_id = ?'],
                        'data'  => [$instance['res_id']]
                    ]);
                }
            }
        }

        DatabaseModel::commitTransaction();

        return $response->withJson(['success' => 'success']);
    }
}
