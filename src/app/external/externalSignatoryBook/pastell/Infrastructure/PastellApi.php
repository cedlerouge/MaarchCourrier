<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Infrastructure;

use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use Exception;
use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use Resource\models\ResModel;
use SrcCore\models\CurlModel;
use SrcCore\models\DatabaseModel;
use User\models\UserModel;

class PastellApi implements PastellApiInterface
{
    /**
     * Getting Pastell version (Checking if URL, login and password are correct)
     * @param PastellConfig $config
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
     * Getting the connected entity
     * @param PastellConfig $config
     * @return array|string[]
     */
    public function getEntity(PastellConfig $config): array
    {
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
            $return = [];
            foreach ($response['response'] as $entite) {
                $return = ['entityId' => $entite['id_e']];
            }
        }
        return $return;
    }

    /**
     * Getting the plugged connector
     * @param PastellConfig $config
     * @return array|string[]
     */
    public function getConnector(PastellConfig $config): array
    {
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite/' . $config->getEntity() . '/connecteur',
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
            $return = [];
            foreach ($response['response'] as $connector) {
                $return[] = $connector['id_ce'];
            }
        }
        return $return;
    }

    /**
     * Getting the type of document
     * @param PastellConfig $config
     * @return array
     */
    public function getDocumentType(PastellConfig $config): array
    {
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/flux',
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
            $return = [];
            foreach ($response['response'] as $flux => $key) {
                $return[] = $flux;
            }
        }
        return $return;
    }

    /**
     * Getting the type of the plugged connector
     * @param PastellConfig $config
     * @return array
     */
    public function getIparapheurType(PastellConfig $config): array
    {
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite/' . $config->getEntity() . '/connecteur/' . $config->getConnector() . '/externalData/iparapheur_type',
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
            $return = [];
            foreach ($response['response'] as $iParapheurType) {
                $return[] = $iParapheurType;
            }
        }
        return $return;
    }

    /**
     * Creating a folder of the document type
     * @param $config
     * @return array|string[]
     */
    public function createFolder($config): array
    {
        $return = [];
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite/' . $config->getEntity() . '/document',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'headers' => ['content-type:application/json'],
            'method' => 'POST',
            'queryParams' => ['type' => $config->getDocumentType()],
            'body' => json_encode([])
        ]);

        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            $return = ['idFolder' => $response['response']['info']['id_d'] ?? ''];
        }
        return $return;
    }

    /**
     * Getting subtype of the connector
     * @param PastellConfig $config
     * @param string $idDocument
     * @return array
     */
    public function getIparapheurSousType(PastellConfig $config, string $idDocument): array
    {
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idDocument . '/externalData/iparapheur_sous_type',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method' => 'GET'
        ]);

        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            $return = $response['response'] ?? '';
        }
        return $return;
    }

    /**
     * @throws Exception
     */
    public function editFolder(PastellConfig $config, string $idDocument): array
    {
        $signatory = DatabaseModel::select([
            'select' => ['item_id'],
            'table' => ['listinstance',],
            'where' => ['res_id = ?', 'item_mode = ?', 'process_date is null'],
            'data' => [['resIdMaster'], 'sign']
        ])[0];


        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idDocument,
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'method' => 'PATCH',
            'body' => http_build_query()
        ]);

        if ($response['code'] > 200) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            $return = $response['response'] ?? '';
        }
        return $return;
    }

    public function uploadMainFile(PastellConfig $config, string $idDocument): array
    {
        $mainFileInfo = ConvertPdfController::getConvertedPdfById(/*['resId' => , 'collId' => ]*/);

        if (empty($mainFileInfo['docserver_id']) || strtolower(pathinfo($mainFileInfo['filename'], PATHINFO_EXTENSION)) != 'pdf') {
            return ['error' => 'Document ' . ['resIdMaster'] . ' is not converted in pdf'];
        }
        $attachmentPath = DocserverModel::getByDocserverId(['docserverId' => $mainFileInfo['docserver_id'], 'select' => ['path_template']]);
        $attachmentFilePath = $attachmentPath['path_template'] . str_replace('#', '/', $mainFileInfo['path']) . $mainFileInfo['filename'];

        $bodyData = array(
            'file_name' => 'Document principal.' . pathinfo($attachmentFilePath)['extension'],
            'file_content' => file_get_contents($attachmentFilePath)
        );

        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/api/v2' . '/entite/' . $config->getEntity() . '/document/' . $idDocument . '/file/document',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'method' => 'POST',
            'body' => http_build_query($bodyData)
        ]);

        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        }
        return $return;
    }

    public function getDocumentDetail(PastellConfig $config, string $idDocument): array
    {
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idDocument,
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method' => 'GET'
        ]);

        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            $return =
                [
                    'info' => $response['response']['info'] ?? '',
                    'data' => $response['response']['data'] ?? '',
                ];
        }
        return $return;
    }
}
