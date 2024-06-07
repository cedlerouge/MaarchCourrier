<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Maarch Parapheur Group Service
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure;

use Exception;
use MaarchCourrier\Core\Domain\Group\Port\GroupInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookGroupServiceInterface;
use MaarchCourrier\SignatureBook\Domain\SignatureBookServiceConfig;
use SrcCore\models\CurlModel;

class MaarchParapheurGroupService implements SignatureBookGroupServiceInterface
{
    private SignatureBookServiceConfig $config;

    /**
     * @param SignatureBookServiceConfig $config
     *
     * @return SignatureBookGroupServiceInterface
     */
    public function setConfig(SignatureBookServiceConfig $config): SignatureBookGroupServiceInterface
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param GroupInterface $group
     * @return array|int
     * @throws Exception
     */
    public function createGroup(GroupInterface $group): array|int
    {
        $userInfos = [
            'label' => $group->getLabel()
        ];

        $response = CurlModel::exec([
            'url' => rtrim($this->config->getUrl(), '/') . '/rest/groups',
            'basicAuth' => [
                'user'     => $this->config->getUserWebService()->getLogin(),
                'password' => $this->config->getUserWebService()->getPassword(),
            ],
            'method' => 'POST',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
            'body' => json_encode($userInfos),
        ]);

        if ($response['code'] === 200) {
            return $response['response']['id'];
        } else {
            return $response['errors'] ??
                ['errors' => 'Error occurred during the creation of the Maarch Parapheur group.'];
        }
    }


    /**
     * @throws Exception
     */
    public function updateGroup(GroupInterface $group): array|bool
    {
        $userInfos = [
            'label' => $group->getLabel()
        ];
        $externalId = $group->getExternalId();
        $response = CurlModel::exec([
            'url' => rtrim($this->config->getUrl(), '/') . '/rest/groups/' . $externalId['internalParapheur'] ,
            'basicAuth' => [
                'user'     => $this->config->getUserWebService()->getLogin(),
                'password' => $this->config->getUserWebService()->getPassword(),
            ],
            'method' => 'PUT',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
            'body' => json_encode($userInfos),
        ]);

        if ($response['code'] === 204) {
            return true;
        } else {
            return $response['errors'] ??
                ['errors' => 'Error occurred during the update of the Maarch Parapheur group.'];
        }
    }

    /**
     * @param GroupInterface $group
     * @return array|bool
     * @throws Exception
     */
    public function deleteGroup(GroupInterface $group): array|bool
    {
        $externalId = $group->getExternalId();
        $response = CurlModel::exec([
            'url' => rtrim($this->config->getUrl(), '/') . '/rest/groups/' . $externalId['internalParapheur'] ,
            'basicAuth' => [
                'user'     => $this->config->getUserWebService()->getLogin(),
                'password' => $this->config->getUserWebService()->getPassword(),
            ],
            'method' => 'DELETE',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
        ]);

        if ($response['code'] <= 204) {
            return true;
        } else {
            return $response['errors'] ??
                ['errors' => 'Error occurred during the deletion of the Maarch Parapheur group.'];
        }
    }

    /**
     * @param GroupInterface $group
     * @return bool|array
     * @throws Exception
     */
    public function getGroupPrivileges(GroupInterface $group): bool|array
    {
        $externalId = $group->getExternalId();
        $response = CurlModel::exec([
            'url' => rtrim($this->config->getUrl(), '/') . '/rest/groups/' .
                $externalId['internalParapheur'],
            'basicAuth' => [
                'user'     => $this->config->getUserWebService()->getLogin(),
                'password' => $this->config->getUserWebService()->getPassword(),
            ],
            'method' => 'GET',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
        ]);

        if ($response['code'] !== 200) {
            return $response['errors'] ?? ['errors' => 'Error occurred while retrieving group information.'];
        } else {
            $result = $response['response']['group']['privileges'];
            $result = array_filter($result, function ($result) {
                return in_array($result['id'], ['indexation', 'manage_documents']);
            });
            $result = array_combine(array_column($result, 'id'), $result);
            if (
                ($result['indexation']['checked'] === false) ||
                ($result['manage_documents']['checked'] === false)
            ) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param GroupInterface $group
     * @param string $privilege
     * @param bool $checked
     * @return array|bool
     * @throws Exception
     */
    public function updatePrivilege(GroupInterface $group, string $privilege, bool $checked): array|bool
    {
        $externalId = $group->getExternalId();
        $data = [
            'checked' => $checked,
        ];
        $response = CurlModel::exec([
            'url' => rtrim($this->config->getUrl(), '/') . '/rest/groups/' .
                $externalId['internalParapheur'] . '/privilege/' . $privilege,
            'basicAuth' => [
                'user'     => $this->config->getUserWebService()->getLogin(),
                'password' => $this->config->getUserWebService()->getPassword(),
            ],
            'method' => 'PUT',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
            'body' => json_encode($data),
        ]);

        if ($response['code'] <= 204) {
            return true;
        } else {
            return $response['errors'] ??
                ['errors' => 'Error occurred during the update of the group privilege in Maarch Parapheur.'];
        }
    }
}
