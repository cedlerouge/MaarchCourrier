<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Infrastructure;

use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
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
    public function getVersion(PastellConfig $config): array
    {
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/version',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
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
    public function getEntity(PastellConfig $config): array
    {
        $return = [];
        //Récupération de l'entité accessible via l'utilisateur connecté
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
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
    public function getConnector(PastellConfig $config): array
    {
        $return = [];
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite/', $config->getEntity() . '/connecteur',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
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

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @param int $entite
     * @param int $connector
     * @return array
     */
    public function getType(PastellConfig $config): array
    {
        $return = [];
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite' . $config->getEntity() . '/connecteur' . $config->getConnector() . '/externalData/iparapheur_type',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
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
                $return = ['idEntity' => $entite['id_e']];
            }
        }
        return $return;
    }
}
