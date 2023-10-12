<?php

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Application\PastellConfigurationCheck;
use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use SrcCore\models\CurlModel;

class PastellApi implements PastellApiInterface
{
// TODO appel CURL


    /**
     * Récupération de la version de Pastell (permet de vérifier la connexion avec l'url, login et password)
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

    public function getEntity(string $url, string $login, string $password): array
    {
        $return = [];
        //Récupération de l'entité accessible via l'utilisateur connecté
        $response = CurlModel::exec([
            'url' => $url . '/entite',
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
            foreach ($response['response'] as $entite) {
                $return = ['idEntity' => $entite['id_e']];
            }
        }
        return $return;
    }

    public function getConnector(string $url, string $login, string $password, int $entite)
    {
        // TODO: Implement getConnector() method.
    }
}
