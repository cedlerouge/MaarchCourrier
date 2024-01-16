<?php

namespace ExternalSignatoryBook\Infrastructure;

use ExternalSignatoryBook\Domain\Ports\UserRepositoryInterface;
use ExternalSignatoryBook\Domain\User;
use User\models\UserModel;

class UserRepository implements UserRepositoryInterface
{
    public function getRootUser(): User
    {
        $userId = UserModel::get([
            'select' => ['id'],
            'where'  => ['mode = ? OR mode = ?'],
            'data'   => ['root_visible', 'root_invisible'],
            'limit'  => 1
        ])[0]['id'];

        return new User($userId);
    }
}
