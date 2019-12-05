<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource Contact Model
* @author dev@maarch.org
*/

namespace Resource\models;

use Contact\models\ContactModel;
use Entity\models\EntityModel;
use SrcCore\controllers\AutoCompleteController;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ResourceContactModel
{
    public static function getByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aContacts = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['resource_contacts'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']],
        ]);

        return $aContacts;
    }

    public static function get(array $args)
    {
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($args, ['limit']);

        $contacts = DatabaseModel::select([
            'select'    => empty($args['select']) ? ['*'] : $args['select'],
            'table'     => ['resource_contacts'],
            'where'     => empty($args['where']) ? [] : $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data'],
            'order_by'  => empty($args['orderBy']) ? [] : $args['orderBy'],
            'limit'     => empty($args['limit']) ? 0 : $args['limit']
        ]);

        return $contacts;
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['res_id', 'item_id', 'type', 'mode']);
        ValidatorModel::intVal($args, ['res_id', 'item_id']);
        ValidatorModel::stringType($args, ['type', 'mode']);

        DatabaseModel::insert([
            'table'         => 'resource_contacts',
            'columnsValues' => [
                'res_id'    => $args['res_id'],
                'item_id'   => $args['item_id'],
                'type'      => $args['type'],
                'mode'      => $args['mode']
            ]
        ]);

        return true;
    }

    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'resource_contacts',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
