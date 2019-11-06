<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief ExternalSummary Controller
 * @author dev@maarch.org
 */

namespace ExternalSummary\controllers;


use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use Email\models\EmailModel;
use MessageExchange\models\MessageExchangeModel;
use Resource\controllers\ResController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class SummaryController
{

    public static function getByResId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $queryParams = $request->getQueryParams();
        if (!empty($queryParams['limit']) && !Validator::intVal()->validate($queryParams['limit'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Query limit is not an int val']);
        }

        $emails = EmailModel::get(['select' => ['object', 'send_date', 'user_id'], 'where' => ['document->>\'id\' = ?', 'status in (?)'], 'data' => [$args['resId'], ['SENT', 'ERROR']], 'limit' => (int)$queryParams['limit']]);
        foreach ($emails as $key => $value) {
            $userInfo = UserModel::getById(['select' => ['firstname', 'lastname'], 'id' => $value['user_id']]);
            $emails[$key]['userInfo'] = $userInfo['firstname'] . ' ' . $userInfo['lastname'];
            $emails[$key]['type']     = 'email';
            unset($emails[$key]['user_id']);
        }
        $acknowledgementReceipts = AcknowledgementReceiptModel::get(['select' => ['send_date', 'user_id'], 'where' => ['res_id = ?', 'format = ?', 'send_date is not null'], 'data' => [$args['resId'], 'pdf'], 'limit' => (int)$queryParams['limit']]);
        foreach ($acknowledgementReceipts as $key => $value) {
            $userInfo = UserModel::getById(['select' => ['firstname', 'lastname'], 'id' => $value['user_id']]);
            $acknowledgementReceipts[$key]['userInfo'] = $userInfo['firstname'] . ' ' . $userInfo['lastname'];
            $acknowledgementReceipts[$key]['object']   = '';
            $acknowledgementReceipts[$key]['type']     = 'aknowledgement_receipt';
            unset($acknowledgementReceipts[$key]['user_id']);
        }
        $maarch2ged = MessageExchangeModel::get(['select' => ['type', 'date as send_date', 'account_id'], 'where' => ['res_id_master = ?', 'status = ?'], 'data' => [$args['resId'], 'S'], 'limit' => (int)$queryParams['limit']]);
        foreach ($maarch2ged as $key => $value) {
            $userInfo = UserModel::getByLogin(['select' => ['firstname', 'lastname'], 'login' => $value['account_id']]);
            $maarch2ged[$key]['userInfo'] = $userInfo['firstname'] . ' ' . $userInfo['lastname'];
            $maarch2ged[$key]['object']   = '';
            unset($maarch2ged[$key]['account_id']);
        }

        $elementsSend = array_merge($emails, $acknowledgementReceipts, $maarch2ged);
        usort($elementsSend, function ($a, $b) {
            return $b['send_date'] <=> $a['send_date'];
        });

        if (!empty($queryParams['limit'])) {
            $elementsSend = array_slice($elementsSend, 0, (int)$queryParams['limit']);
        }

        return $response->withJson(['elementsSend' => $elementsSend]);
    }
}
