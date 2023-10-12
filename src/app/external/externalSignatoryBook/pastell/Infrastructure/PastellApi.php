<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use SrcCore\models\CurlModel;

class PastellApi implements PastellApiInterface
{
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

        if ($response['code'] > 200) {
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

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return array|string[]
     */
    public function getEntity(string $url, string $login, string $password): array
    {
        $return = [];
        //Récupération de l'entité accessible via l'utilisateur connecté
        $response = CurlModel::exec([
            'url' => $url . '/entite',
            'basicAuth' => ['user' => $login, 'password' => $password],
            'method' => 'GET'
        ]);

        if ($response['code'] > 200) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            foreach ($response['response'] as $entite) {
                $return = ['entityId' => $entite['id_e']];
            }
        }
        return $return;
    }

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @param int $entite
     * @return array|string[]
     */
    public function getConnector(string $url, string $login, string $password, int $entite): array
    {
        $return = [];
        $response = CurlModel::exec([
            'url' => $url . '/entite/', $entite . '/connecteur',
            'basicAuth' => ['user' => $login, 'password' => $password],
            'method' => 'GET'
        ]);

        if ($response['code'] > 200) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            foreach ($response['response'] as $connector){
                $return = ['connectorId' => $connector['id_ce']];
            }
        }
        return $return;
    }
}
