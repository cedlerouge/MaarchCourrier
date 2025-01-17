<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Contact Group List Model
 * @author dev@maarch.org
 */

namespace Contact\models;

use Exception;
use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class ContactGroupListModel
{
    /**
     * @param  array  $args
     * @return array
     * @throws Exception
     */
    public static function get(array $args = []): array
    {
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($args, ['limit']);

        return DatabaseModel::select([
            'select'   => empty($args['select']) ? ['*'] : $args['select'],
            'table'    => ['contacts_groups_lists'],
            'where'    => empty($args['where']) ? [] : $args['where'],
            'data'     => empty($args['data']) ? [] : $args['data'],
            'order_by' => empty($args['orderBy']) ? [] : $args['orderBy'],
            'limit'    => empty($args['limit']) ? 0 : $args['limit'],
            'offset'   => empty($args['offset']) ? 0 : $args['offset']
        ]);
    }

    /**
     * @param  array  $args
     * @return true
     * @throws Exception
     */
    public static function create(array $args): bool
    {
        ValidatorModel::notEmpty($args, ['contacts_groups_id', 'correspondent_id', 'correspondent_type']);
        ValidatorModel::stringType($args, ['correspondent_type']);
        ValidatorModel::intVal($args, ['contacts_groups_id', 'correspondent_id']);

        DatabaseModel::insert([
            'table'         => 'contacts_groups_lists',
            'columnsValues' => [
                'contacts_groups_id' => $args['contacts_groups_id'],
                'correspondent_id'   => $args['correspondent_id'],
                'correspondent_type' => $args['correspondent_type']
            ]
        ]);

        return true;
    }

    /**
     * @param  array  $args
     * @return true
     * @throws Exception
     */
    public static function delete(array $args): bool
    {
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'contacts_groups_lists',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    /**
     * @param  array  $args
     * @return array
     * @throws Exception
     */
    public static function getWithCorrespondents(array $args = []): array
    {
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($args, ['limit']);

        return DatabaseModel::select([
            'select'    => empty($args['select']) ? ['*'] : $args['select'],
            'table'     => ['contacts_groups_lists', 'contacts', 'entities', 'users'],
            'left_join' => [
                "contacts_groups_lists.correspondent_id = contacts.id AND" .
                " contacts_groups_lists.correspondent_type = 'contact'",
                "contacts_groups_lists.correspondent_id = entities.id AND" .
                " contacts_groups_lists.correspondent_type = 'entity'",
                "contacts_groups_lists.correspondent_id = users.id AND" .
                " contacts_groups_lists.correspondent_type = 'user'"
            ],
            'where'     => empty($args['where']) ? [] : $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data'],
            'order_by'  => empty($args['orderBy']) ? [] : $args['orderBy'],
            'limit'     => empty($args['limit']) ? 0 : $args['limit'],
            'offset'    => empty($args['offset']) ? 0 : $args['offset']
        ]);
    }
}
