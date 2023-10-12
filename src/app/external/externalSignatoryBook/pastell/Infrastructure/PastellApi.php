<?php

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use SrcCore\models\CurlModel;

class PastellApi implements PastellApiInterface
{
// TODO appel CURL

    /**
     * @return bool
     */
    public function checkPastellConfig(): bool
    {
        // TODO: Implement checkPastellConfig() method.
    }

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return array
     */
    public function getVersion(string $url, string $login, string $password): array
    {
        $response = CurlModel::exec([
            'url' => $url . '/version',
            'basicAuth' => ['user' => $login, 'password' => $password],
            'method' => 'GET'
        ]);

        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            $return = ['version' => $response['response']['version'] ?? ''];
        }

        return $return;
    }

    public function getEntity(string $entity): array
    {
        //Récupération de l'entité accessible via l'utilisateur connecté
    }
}
