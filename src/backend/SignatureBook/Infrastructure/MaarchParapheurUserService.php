<?php

namespace MaarchCourrier\SignatureBook\Infrastructure;

use Exception;
use MaarchCourrier\Core\Domain\User\Port\UserInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureBookUserServiceInterface;
use MaarchCourrier\SignatureBook\Domain\Port\SignatureServiceConfig;
use SrcCore\models\CurlModel;

class MaarchParapheurUserService implements SignatureBookUserServiceInterface
{
    public int $id;
    private SignatureServiceConfig $config;

    public function setConfig(SignatureServiceConfig $config): SignatureBookUserServiceInterface
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param array $ids
     * @param string $accessToken
     * @return true
     * @throws Exception
     */
    public function doesUserExists(array $ids, string $accessToken): bool
    {
        $response = CurlModel::exec([
            'url'        => rtrim($this->config->getUrl(), '/') . '/rest/users',
            'bearerAuth' => ['token' => $accessToken],
            'method'     => 'GET',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
            'body'        => json_encode($ids),
        ]);

        if ($response['code'] != 200) {
            return true;
        } else {
            return $response['errors'];
        }
    }

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     * @throws Exception
     */
    public function createUser(UserInterface $user, string $accessToken): array|int
    {
        $userDetails = [
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getMail(),
            'login' => $user->getLogin(),
            'signatureMode' => ["rgs_2stars"]
        ];

        $response = CurlModel::exec([
            'url'        => rtrim($this->config->getUrl(), '/') . '/rest/users',
            'bearerAuth' => ['token' => $accessToken],
            'method'     => 'POST',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
            'body'        => json_encode($userDetails),
        ]);

        if ($response['code'] == 200) {
            return $response['response']['id'];
        } else {
            return $response['errors'] ??
                ['errors' => 'Error occurred during the creation of the Maarch Parapheur user.'];
        }
    }

    /**
     * @param UserInterface $user
     * @param string $accessToken
     * @return array|int
     * @throws Exception
     */
    public function updateUser(UserInterface $user, string $accessToken): array|int
    {
        $userDetails = [
            'firstName' => $user->getFirstname(),
            'lastName' => $user->getLastname(),
            'mail' => $user->getMail(),
            'login' => $user->getLogin()
        ];

        $response = CurlModel::exec([
            'url'        => rtrim($this->config->getUrl(), '/') . '/rest/users' . $user->getLogin(),
            'bearerAuth' => ['token' => $accessToken],
            'method'     => 'PUT',
            'headers'    => [
                'content-type: application/json',
                'Accept: application/json',
            ],
            'body'        => json_encode($userDetails),
        ]);

        if ($response['code'] == 200) {
            return $response['response']['id'];
        } else {
            return $response['errors'] ?? ['errors' => 'Failed to update the user in Maarch Parapheur.'];
        }
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
