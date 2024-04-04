<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 * @brief   FirstLevelModelAbstract
 * @author  dev <dev@maarch.org>
 * @ingroup core
 */

namespace Doctype\models;

use Exception;
use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class FirstLevelModelAbstract
{
    /**
     * @param array $aArgs
     * @return array
     * @throws Exception
     */
    public static function get(array $aArgs = []): array
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $firstLevel = DatabaseModel::select([
            'select'   => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'    => ['doctypes_first_level'],
            'where'    => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'     => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by' => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'    => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $firstLevel;
    }

    /**
     * @param array $aArgs
     * @return array
     * @throws Exception
     */
    public static function getById(array $aArgs): array
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select(
            [
                'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
                'table'  => ['doctypes_first_level'],
                'where'  => ['doctypes_first_level_id = ?'],
                'data'   => [$aArgs['id']]
            ]
        );

        if (empty($aReturn[0])) {
            return [];
        }

        return $aReturn[0];
    }

    /**
     * @param array $aArgs
     * @return int
     * @throws Exception
     */
    public static function create(array $aArgs): int
    {
        ValidatorModel::notEmpty($aArgs, ['doctypes_first_level_label']);

        $aArgs['doctypes_first_level_id'] = DatabaseModel::getNextSequenceValue([
            'sequenceId' => 'doctypes_first_level_id_seq'
        ]);
        DatabaseModel::insert([
            'table'         => 'doctypes_first_level',
            'columnsValues' => $aArgs
        ]);

        return $aArgs['doctypes_first_level_id'];
    }

    /**
     * @param array $aArgs
     * @return true
     * @throws Exception
     */
    public static function update(array $aArgs): bool
    {
        ValidatorModel::notEmpty($aArgs, ['doctypes_first_level_id']);
        ValidatorModel::intVal($aArgs, ['doctypes_first_level_id']);

        DatabaseModel::update([
            'table' => 'doctypes_first_level',
            'set'   => $aArgs,
            'where' => ['doctypes_first_level_id = ?'],
            'data'  => [$aArgs['doctypes_first_level_id']]
        ]);

        return true;
    }
}
