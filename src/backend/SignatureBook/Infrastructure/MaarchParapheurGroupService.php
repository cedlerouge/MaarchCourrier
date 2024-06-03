<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Signature Book User Service Interface
 * @author dev@maarch.org
 */

namespace MaarchCourrier\SignatureBook\Infrastructure;

use Exception;
use Group\models\PrivilegeModel;
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
            'label' => $group->getLibelle()
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
     * @inheritDoc
     */
    public function updateGroup(GroupInterface $group): array|bool
    {
        $userInfos = [
            'label' => $group->getLibelle()
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
                ['errors' => 'Error occurred during the creation of the Maarch Parapheur group.'];
        }
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getGroupPrivileges(GroupInterface $group): array
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

        if ($response['code'] === 200) {
            return $response['response']['group']['privileges'];
        } else {
            return $response['errors'] ?? ['errors' => 'Error occurred while retrieving group information.'];
        }
    }

    /**
     * @param GroupInterface $group
     * @param $privilege
     * @inheritDoc
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
                ['errors' => 'Error occurred during the creation of the Maarch Parapheur group.'];
        }
    }

    /**
     * @param GroupInterface $group
     * @return bool
     * @throws Exception
     */
    public function isPrivilegeIsChecked(GroupInterface $group): bool
    {
        if ($group->getPrivilege() == 'sign_document') {
            $privilegeToCheck = 'visa_documents';
        } else {
            $privilegeToCheck = 'sign_document';
        }

        $hasPrivilege =  PrivilegeModel::groupHasPrivilege(
            ['privilegeId' => $privilegeToCheck, 'groupId' => $group->getGroupId()]
        );

        if ($hasPrivilege) {
            return false;
        }
        return true;
    }
}
