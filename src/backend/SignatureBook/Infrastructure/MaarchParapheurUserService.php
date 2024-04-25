<?php

namespace MaarchCourrier\SignatureBook\Infrastructure;

use Exception;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfig;
use MaarchCourrier\User\Domain\User;
use SrcCore\models\CurlModel;

class MaarchParapheurUserService implements SignatureBookUserServiceInterface
{
    private SignatureServiceConfig $config;
    public int $userId;


    public function setConfig(SignatureServiceConfig $config): MaarchParapheurUserService
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function doesUserExists(int $id): bool
    {
        // TODO: Implement doesUserExists() method.
        return true;
    }

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     * @throws Exception
     */
    public function createUser(UserInterface $user, string $accessToken): array|int
    {
        $response = CurlModel::exec([
            'url'        => rtrim($this->config->getUrl(), '/') . '/rest/users/',
            'bearerAuth' => ['token' => $accessToken],
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
            'body'        => json_encode($body),
        ]);

        if ($response['code'] == 200) {
            $this->userId = $response['id'];
        } else {
            return $response['errors'] ??
                ['errors' => 'Error occurred during the creation of the Maarch Parapheur user.'];
        }

        return $this->userId;
    }

    /**
     * @return array|int
     */
    public function updateUser(): array|int
    {
        // TODO: Implement updateUser() method.
        return [];
    }

    /**
     * @return array|int
     */
    public function deleteUser(): array|int
    {
        // TODO: Implement deleteUser() method.
        return [];
    }
}
