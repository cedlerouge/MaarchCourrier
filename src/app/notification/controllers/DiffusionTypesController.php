<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief DiffusionTypes Controller
 *
 * @author dev@maarch.org
 * @ingroup notifications
 */

namespace Notification\controllers;

use Exception;
use Notification\models\NotificationModel;
use SrcCore\models\DatabaseModel;
use User\models\UserModel;

class DiffusionTypesController
{
    /**
     * @param array $args
     * @return array|int|null
     */
    public static function getItemsToNotify(array $args = []): array|int|null
    {
        $diffusionTypes = NotificationModel::getDiffusionType();
        foreach ($diffusionTypes as $diffusionType) {
            if ($diffusionType['id'] == $args['notification']['diffusion_type']) {
                $function = $diffusionType['function'];
                break;
            }
        }

        $items = [];
        if (!empty($function)) {
            $items = DiffusionTypesController::$function(
                ['request' => $args['request'], 'notification' => $args['notification'], 'event' => $args['event']]
            );
        }

        return $items;
    }

    /**
     * @param array $args
     * @return array
     * @throws Exception
     */
    public static function getRecipientsByContact(array $args = []): array
    {
        if ($args['request'] == 'recipients') {
            return DatabaseModel::select([
                'select'    => ['contacts.id as user_id', 'contacts.email as mail'],
                'table'     => ['resource_contacts', 'contacts'],
                'left_join' => ['resource_contacts.item_id = contacts.id'],
                'where'     => ['res_id = ?', 'type = ?', 'mode = ?'],
                'data'      => [$args['event']['record_id'], 'contact', 'sender']
            ]);
        } else {
            return [];
        }
    }

    /**
     * @param array $args
     * @return array|mixed|void|null
     * @throws Exception
     */
    public static function getRecipientsByCopie(array $args = [])
    {
        if ($args['request'] == 'recipients') {
            $table = ['listinstance li', 'users us'];
            $leftJoin = ['li.item_id = us.id'];
            $where = ["li.item_mode = 'cc' AND item_type='user_id'"];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    $where[] = '( notes.id not in (SELECT DISTINCT note_id FROM note_entities) OR us.id IN (
                    SELECT ue.user_id FROM note_entities ne
                    JOIN users_entities ue ON ne.item_id = ue.entity_id
                    WHERE ne.note_id = :recordid))';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid AND lb.status not in ('INIT', 'AVAL')
                    AND li.item_id <> :userid";
                    $arrayPDO[':userid'] = $args['event']['user_id'];
            }

            // Main document resource from view
            $recipientsUser = DatabaseModel::select([
                'select'    => ['distinct us.*'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);

            // Copy to entities
            $table = ['listinstance li', 'entities e', 'users_entities ue', 'users us'];
            $leftJoin = ['li.item_id = e.id', 'e.entity_id = ue.entity_id', 'ue.user_id = us.id'];
            $where = ["li.item_mode = 'cc' AND item_type='entity_id'"];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    $where[] = '( notes.id not in (SELECT DISTINCT note_id FROM note_entities) OR us.id IN (
                    SELECT ue.user_id FROM note_entities ne
                    JOIN users_entities ue ON ne.item_id = ue.entity_id WHERE ne.note_id = :recordid))';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $where[] = "listinstance_id = :recordid";
            }

            // Main document resource from view
            $recipientsEntities = DatabaseModel::select([
                'select'    => ['distinct us.*'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => [':recordid' => $args['event']['record_id']],
            ]);

            return array_merge($recipientsUser, $recipientsEntities);
        } elseif ($args['request'] == 'res_id') {
            $table = ['listinstance li'];
            $leftJoin = [];
            $where = [];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $where[] = "listinstance_id = :recordid";
            }

            // Main document resource from view
            $resId = DatabaseModel::select([
                'select'    => ['li.res_id'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => [':recordid' => $args['event']['record_id']],
            ]);

            return $resId[0]['res_id'] ?? null;
        }
    }

    /**
     * @param array $args
     * @return array|mixed|void
     * @throws Exception
     */
    public static function getRecipientsByDestEntity(array $args = [])
    {
        if ($args['request'] == 'recipients') {
            return DatabaseModel::select([
                'select'    => ['distinct en.entity_id', 'en.enabled', 'en.email AS mail'],
                'table'     => ['res_view_letterbox rvl', 'entities en'],
                'left_join' => ['rvl.destination = en.entity_id'],
                'where'     => ['rvl.res_id = ?'],
                'data'      => [$args['event']['record_id']]
            ]);
        } elseif ($args['request'] == 'res_id') {
            $table = ['listinstance li'];
            $leftJoin = [];
            $where = [];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = notes.identifier';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid";
            }

            if (!empty($args['notification']['diffusion_properties'])) {
                $aStatus = explode(',', $args['notification']['diffusion_properties']);
                foreach ($aStatus as $key => $status) {
                    $inQuestion[] = ':statustab' . $key;
                    $arrayPDO[':statustab' . $key] = $status;
                }
                $inQuestion = implode(', ', $inQuestion);
                $where[] = 'lb.status in (' . $inQuestion . ')';
            }

            // Main document resource from view
            $resId = DatabaseModel::select([
                'select'    => ['li.res_id'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);

            return $resId[0]['res_id'];
        }
    }

    /**
     * @param array $args
     * @return array|mixed|void
     * @throws Exception
     */
    public static function getRecipientsByDestUserSign(array $args = [])
    {
        if ($args['request'] == 'recipients') {
            $table = ['listinstance li', 'users us'];
            $leftJoin = ['li.item_id = us.id'];
            $where = ["li.item_mode = 'sign' and process_date IS NULL"];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = notes.identifier';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    $where[] = '(notes.id not in (SELECT DISTINCT note_id FROM note_entities) OR us.id IN (
                    SELECT ue.user_id FROM note_entities ne
                    JOIN users_entities ue ON ne.item_id = ue.entity_id WHERE ne.note_id = :recordid))';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid";
            }

            if (!empty($args['notification']['diffusion_properties'])) {
                $aStatus = explode(',', $args['notification']['diffusion_properties']);
                foreach ($aStatus as $key => $status) {
                    $inQuestion[] = ':statustab' . $key;
                    $arrayPDO[':statustab' . $key] = $status;
                }
                $inQuestion = implode(', ', $inQuestion);
                $where[] = 'lb.status in (' . $inQuestion . ')';
            }

            // Main document resource from view
            return DatabaseModel::select([
                'select'    => ['distinct us.*'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);
        } elseif ($args['request'] == 'res_id') {
            $table = ['listinstance li'];
            $leftJoin = [];
            $where = [];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = notes.identifier';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid";
            }

            if (!empty($args['notification']['diffusion_properties'])) {
                $aStatus = explode(',', $args['notification']['diffusion_properties']);
                foreach ($aStatus as $key => $status) {
                    $inQuestion[] = ':statustab' . $key;
                    $arrayPDO[':statustab' . $key] = $status;
                }
                $inQuestion = implode(', ', $inQuestion);
                $where[] = 'lb.status in (' . $inQuestion . ')';
            }

            // Main document resource from view
            $resId = DatabaseModel::select([
                'select'    => ['li.res_id'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);

            return $resId[0]['res_id'];
        }
    }

    /**
     * @param array $args
     * @return array|mixed|void
     * @throws Exception
     */
    public static function getRecipientsByDestUserVisa(array $args = [])
    {
        if ($args['request'] == 'recipients') {
            $table = ['listinstance li', 'users us'];
            $leftJoin = ['li.item_id = us.id'];
            $where = ["li.item_mode = 'visa' and process_date IS NULL"];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = notes.identifier';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    $where[] = '(notes.id not in (SELECT DISTINCT note_id FROM note_entities) OR us.id IN (
                    SELECT ue.user_id FROM note_entities ne
                    JOIN users_entities ue ON ne.item_id = ue.entity_id WHERE ne.note_id = :recordid))';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid";
            }

            if (!empty($args['notification']['diffusion_properties'])) {
                $aStatus = explode(',', $args['notification']['diffusion_properties']);
                foreach ($aStatus as $key => $status) {
                    $inQuestion[] = ':statustab' . $key;
                    $arrayPDO[':statustab' . $key] = $status;
                }
                $inQuestion = implode(', ', $inQuestion);
                $where[] = 'lb.status in (' . $inQuestion . ')';
            }

            // Main document resource from view
            return DatabaseModel::select([
                'select'    => ['distinct us.*'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);
        } elseif ($args['request'] == 'res_id') {
            $table = ['listinstance li'];
            $leftJoin = [];
            $where = [];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = notes.identifier';
                    $where[] = 'notes.id = :recordid AND li.item_id != notes.user_id';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid";
            }

            if (!empty($args['notification']['diffusion_properties'])) {
                $aStatus = explode(',', $args['notification']['diffusion_properties']);
                foreach ($aStatus as $key => $status) {
                    $inQuestion[] = ':statustab' . $key;
                    $arrayPDO[':statustab' . $key] = $status;
                }
                $inQuestion = implode(', ', $inQuestion);
                $where[] = 'lb.status in (' . $inQuestion . ')';
            }

            // Main document resource from view
            $resId = DatabaseModel::select([
                'select'    => ['li.res_id'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);

            return $resId[0]['res_id'];
        }
    }

    /**
     * @param array $args
     * @return array|mixed|void
     * @throws Exception
     */
    public static function getRecipientsByDestUser(array $args = [])
    {
        if ($args['request'] == 'recipients') {
            $table = ['listinstance li', 'users us'];
            $leftJoin = ['li.item_id = us.id'];
            $where = ["li.item_mode = 'dest'"];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = notes.identifier';
                    $where[] = 'notes.id = :recordid AND us.id != notes.user_id';
                    $where[] = '(notes.id not in (SELECT DISTINCT note_id FROM note_entities) OR us.id IN (
                    SELECT ue.user_id FROM note_entities ne
                    JOIN users_entities ue ON ne.item_id = ue.entity_id WHERE ne.note_id = :recordid))';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid";
            }

            if (!empty($args['notification']['diffusion_properties'])) {
                $aStatus = explode(',', $args['notification']['diffusion_properties']);
                foreach ($aStatus as $key => $status) {
                    $inQuestion[] = ':statustab' . $key;
                    $arrayPDO[':statustab' . $key] = $status;
                }
                $inQuestion = implode(', ', $inQuestion);
                $where[] = 'lb.status in (' . $inQuestion . ')';
            }

            // Main document resource from view
            return DatabaseModel::select([
                'select'    => ['distinct us.*'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);
        } elseif ($args['request'] == 'res_id') {
            $table = ['listinstance li', 'users us'];
            $leftJoin = ['li.item_id = us.id'];
            $where = [];
            $arrayPDO = [':recordid' => $args['event']['record_id']];

            switch ($args['event']['table_name']) {
                case 'notes':
                    $table[] = 'notes';
                    $leftJoin[] = 'notes.identifier = li.res_id';
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = notes.identifier';
                    $where[] = 'notes.id = :recordid AND us.id != notes.user_id';
                    break;

                case 'res_letterbox':
                case 'res_view_letterbox':
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = 'lb.res_id = :recordid';
                    break;

                case 'listinstance':
                default:
                    $table[] = 'res_letterbox lb';
                    $leftJoin[] = 'lb.res_id = li.res_id';
                    $where[] = "listinstance_id = :recordid";
            }

            if (!empty($args['notification']['diffusion_properties'])) {
                $aStatus = explode(',', $args['notification']['diffusion_properties']);
                foreach ($aStatus as $key => $status) {
                    $inQuestion[] = ':statustab' . $key;
                    $arrayPDO[':statustab' . $key] = $status;
                }
                $inQuestion = implode(', ', $inQuestion);
                $where[] = 'lb.status in (' . $inQuestion . ')';
            }

            // Main document resource from view
            $resId = DatabaseModel::select([
                'select'    => ['li.res_id'],
                'table'     => $table,
                'left_join' => $leftJoin,
                'where'     => $where,
                'data'      => $arrayPDO,
            ]);

            return $resId[0]['res_id'];
        }
    }

    /**
     * @param array $args
     * @return array
     * @throws Exception
     */
    public static function getRecipientsByEntity(array $args = []): array
    {
        if ($args['request'] == 'recipients') {
            $aEntities = explode(",", $args['notification']['diffusion_properties']);
            return DatabaseModel::select([
                'select' => ['distinct users.*'],
                'table'  => ['users_entities, users'],
                'where'  => [
                    'users_entities.entity_id in (?)',
                    'users_entities.user_id = users.id',
                    'users.status != ?'
                ],
                'data'   => [$aEntities, 'DEL']
            ]);
        } else {
            return [];
        }
    }

    /**
     * @param array $args
     * @return array
     * @throws Exception
     */
    public static function getRecipientsByGroup(array $args = []): array
    {
        if ($args['request'] == 'recipients') {
            $aGroups = explode(",", $args['notification']['diffusion_properties']);
            return DatabaseModel::select([
                'select' => ['distinct us.*'],
                'table'  => ['usergroup_content ug, users us, usergroups'],
                'where'  => [
                    'us.id = ug.user_id',
                    'ug.group_id = usergroups.id',
                    'usergroups.group_id in (?)',
                    'us.status != ?'
                ],
                'data'   => [$aGroups, 'DEL']
            ]);
        } else {
            return [];
        }
    }

    /**
     * @param array $args
     * @return array
     */
    public static function getRecipientsByUser(array $args = []): array
    {
        if ($args['request'] == 'recipients') {
            $aUsers = explode(",", $args['notification']['diffusion_properties']);
            return UserModel::get(['select' => ['distinct *'], 'where' => ['id in (?)'], 'data' => [$aUsers]]);
        } else {
            return [];
        }
    }
}
