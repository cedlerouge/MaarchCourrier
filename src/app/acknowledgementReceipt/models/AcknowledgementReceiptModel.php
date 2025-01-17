<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Acknowledgement Receipt Model
 * @author dev@maarch.org
 */

namespace AcknowledgementReceipt\models;

use Exception;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class AcknowledgementReceiptModel
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

        return DatabaseModel::select([
            'select'   => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'    => ['acknowledgement_receipts'],
            'where'    => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'     => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by' => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'    => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);
    }

    /**
     * @param array $aArgs
     * @return array
     * @throws Exception
     */
    public static function getByIds(array $aArgs): array
    {
        ValidatorModel::notEmpty($aArgs, ['ids']);
        ValidatorModel::arrayType($aArgs, ['ids']);

        return DatabaseModel::select([
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['acknowledgement_receipts'],
            'where'  => ['id in (?)'],
            'data'   => [$aArgs['ids']]
        ]);
    }

    /**
     * @param array $aArgs
     * @return array
     * @throws Exception
     */
    public static function getByResIds(array $aArgs = []): array
    {
        ValidatorModel::notEmpty($aArgs, ['resIds']);
        ValidatorModel::arrayType($aArgs, ['select', 'orderBy', 'resIds', 'groupBy']);

        return DatabaseModel::select([
            'select'   => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'    => ['acknowledgement_receipts'],
            'where'    => ['res_id in (?)'],
            'data'     => [$aArgs['resIds']],
            'order_by' => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'groupBy'  => empty($aArgs['groupBy']) ? [] : $aArgs['groupBy']
        ]);
    }

    /**
     * @param array $aArgs
     * @return int
     * @throws Exception
     */
    public static function create(array $aArgs): int
    {
        ValidatorModel::notEmpty(
            $aArgs,
            ['resId', 'type', 'format', 'userId', 'contactId', 'docserverId', 'path', 'filename', 'fingerprint']
        );
        ValidatorModel::intVal($aArgs, ['resId', 'userId']);
        ValidatorModel::stringType(
            $aArgs,
            ['type', 'format', 'docserverId', 'path', 'filename', 'fingerprint', 'cc', 'cci']
        );

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'acknowledgement_receipts_id_seq']);

        DatabaseModel::insert([
            'table'         => 'acknowledgement_receipts',
            'columnsValues' => [
                'id'            => $nextSequenceId,
                'res_id'        => $aArgs['resId'],
                'type'          => $aArgs['type'],
                'format'        => $aArgs['format'],
                'user_id'       => $aArgs['userId'],
                'contact_id'    => $aArgs['contactId'],
                'creation_date' => 'CURRENT_TIMESTAMP',
                'docserver_id'  => $aArgs['docserverId'],
                'path'          => $aArgs['path'],
                'filename'      => $aArgs['filename'],
                'fingerprint'   => $aArgs['fingerprint'],
                'cc'            => $aArgs['cc'],
                'cci'           => $aArgs['cci'],
            ]
        ]);

        return $nextSequenceId;
    }

    /**
     * @param array $aArgs
     * @return bool
     * @throws Exception
     */
    public static function update(array $aArgs): bool
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'acknowledgement_receipts',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    /**
     * @param array $aArgs
     * @return bool
     * @throws Exception
     */
    public static function updateSendDate(array $aArgs): bool
    {
        ValidatorModel::notEmpty($aArgs, ['send_date', 'res_id']);

        DatabaseModel::update([
            'table' => 'acknowledgement_receipts',
            'set'   => ['send_date' => $aArgs['send_date']],
            'where' => ['res_id = ?', 'send_date is null', 'format = \'pdf\''],
            'data'  => [$aArgs['res_id']]
        ]);

        return true;
    }

    /**
     * @param array $aArgs
     * @return bool
     * @throws Exception
     */
    public static function delete(array $aArgs): bool
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'acknowledgement_receipts',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
