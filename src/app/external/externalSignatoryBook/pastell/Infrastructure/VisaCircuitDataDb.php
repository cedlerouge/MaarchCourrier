<?php

declare(strict_types=1);

namespace ExternalSignatoryBook\pastell\Infrastructure;

use Exception;
use ExternalSignatoryBook\pastell\Domain\VisaCircuitDataInterface;
use SrcCore\models\DatabaseModel;
use User\models\UserModel;

class VisaCircuitDataDb implements VisaCircuitDataInterface
{
    /**
     * @throws Exception
     */
    public function getNextSignatory(int $resId): array
    {
        $signatory = DatabaseModel::select([
            'select' => ['item_id'],
            'table'  => ['listinstance',],
            'where'  => ['res_id = ?', 'item_mode = ?', 'process_date is null'],
            'data'   => [$resId, 'sign']
        ])[0];

        if (!empty($signatory['item_id'])) {
            $user = UserModel::getById(['id' => $signatory['item_id'], 'select' => ['user_id']]);
        }

        return [
            'userId' => $user['user_id'] ?? null
        ];
    }
}
