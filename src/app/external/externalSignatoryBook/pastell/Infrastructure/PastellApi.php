<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

namespace ExternalSignatoryBook\pastell\Infrastructure;

use Exception;
use ExternalSignatoryBook\pastell\Domain\PastellApiInterface;
use ExternalSignatoryBook\pastell\Domain\PastellConfig;
use SrcCore\models\CurlModel;

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
            'url'       => $config->getUrl() . '/version',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET'
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
        $response = CurlModel::exec([
            'url'       => $config->getUrl() . '/entite',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET'
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
            'url'       => $config->getUrl() . '/entite/' . $config->getEntity() . '/connecteur',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET'
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
     * Getting the type of folder(document) that can be created
     * @param PastellConfig $config
     * @return array
     */
    public function getFolderType(PastellConfig $config): array
    {
        $response = CurlModel::exec([
            'url'       => $config->getUrl() . '/flux',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET'
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
            'url'       => $config->getUrl() . '/entite/' . $config->getEntity() . '/connecteur/' . $config->getConnector() . '/externalData/iparapheur_type',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET'
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
     * Creating a folder
     * @param $config
     * @return array|string[]
     */
    public function createFolder($config): array
    {
        $response = CurlModel::exec([
            'url'         => $config->getUrl() . '/entite/' . $config->getEntity() . '/document',
            'basicAuth'   => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'headers'     => ['content-type:application/json'],
            'method'      => 'POST',
            'queryParams' => ['type' => $config->getFolderType()],
            'body'        => json_encode([])
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
     * Getting subtype of the plugged connector
     * @param PastellConfig $config
     * @param string $idFolder
     * @return array
     */
    public function getIparapheurSousType(PastellConfig $config, string $idFolder): array
    {
        $response = CurlModel::exec([
            'url'       => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idFolder . '/externalData/iparapheur_sous_type',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET'
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
     * Sending datas to the created folder
     * @throws Exception
     */
    public function editFolder(PastellConfig $config, string $idFolder, string $title, string $sousType): array
    {
        $data = [
            'libelle'              => $title,
            'iparapheur_sous_type' => $sousType,
            'iparapheur_type'      => $config->getIparapheurType(),
//            'envoi_signature'      => true // TODO obligatoire ????
        ];

        $response = CurlModel::exec([
            'url'       => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idFolder,
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'headers'   => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'method'    => 'PATCH',
            'body'      => http_build_query($data)
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

    /**
     * Uploading a file to be signed
     * @param PastellConfig $config
     * @param string $idFolder
     * @param string $filePath
     * @return array|string[]
     */
    public function uploadMainFile(PastellConfig $config, string $idFolder, string $filePath): array
    {
//        $mainFileInfo = ConvertPdfController::getConvertedPdfById(/*['resId' => , 'collId' => ]*/);
//
//        if (empty($mainFileInfo['docserver_id']) || strtolower(pathinfo($mainFileInfo['filename'], PATHINFO_EXTENSION)) != 'pdf') {
//            return ['error' => 'Document ' . ['resIdMaster'] . ' is not converted in pdf'];
//        }
//        $attachmentPath = DocserverModel::getByDocserverId(['docserverId' => $mainFileInfo['docserver_id'], 'select' => ['path_template']]);
//        $attachmentFilePath = $attachmentPath['path_template'] . str_replace('#', '/', $mainFileInfo['path']) . $mainFileInfo['filename'];

        $bodyData = [
            'file_name'    => 'Document principal.' . pathinfo($filePath)['extension'],
            'file_content' => file_get_contents($filePath)
        ];

        $response = CurlModel::exec([
            'url'       => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idFolder . '/file/document',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'headers'   => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'method'    => 'POST',
            'body'      => http_build_query($bodyData)
        ]);

        $return = [];
        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        }
        return $return;
    }

    /**
     * Getting datas and state of a folder
     * @param PastellConfig $config
     * @param string $idFolder
     * @return array|string[]
     */
    public function getFolderDetail(PastellConfig $config, string $idFolder): array
    {
        $response = CurlModel::exec([
            'url'       => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idFolder,
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET'
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

    /**
     * @param PastellConfig $config
     * @param string $idFolder
     * @return object|array|mixed|string[]
     */
    public function getXmlDetail(PastellConfig $config, string $idFolder): object
    {
        $response = CurlModel::exec([
            'url'       => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idFolder . '/file/iparapheur_historique',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'    => 'GET',
            'isXml'     => true
        ]);

        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            $return = $response['response'];
        }
        return $return;
    }

    /**
     * @param PastellConfig $config
     * @param string $idDocument
     * @return array
     */
    public function downloadFile(PastellConfig $config, string $idDocument): array
    {
        $response = CurlModel::exec([
            'url'          => $config->getUrl() . '/entite/' . $config->getEntity() . '/document/' . $idDocument . '/file/document',
            'basicAuth'    => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'method'       => 'GET',
            'fileResponse' => true
        ]);

        if ($response['code'] > 201) {
            if (!empty($response['response']['error-message'])) {
                $return = ["error" => $response['response']['error-message']];
            } else {
                $return = ["error" => 'An error occurred !'];
            }
        } else {
            $return = ['encodedFile' => base64_encode($response['response'])];
        }
        return $return;
    }

    public function verificationIParapheur(PastellConfig $config, string $idDocument): bool
    {
        $response = CurlModel::exec([
            'url' => $config->getUrl() . '/api/v2' . '/entite/' . $config->getEntity() . '/document/' . $idDocument . '/action/verif-iparapheur',
            'basicAuth' => ['user' => $config->getLogin(), 'password' => $config->getPassword()],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'method' => 'POST',
        ]);

        return $response['code'] == 200;
    }
}
