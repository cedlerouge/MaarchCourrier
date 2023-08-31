<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Docserver Controller
 * @author dev@maarch.org
 */

namespace Mercure\controllers;

use Configuration\models\ConfigurationModel;
use Contact\models\ContactModel;
use Convert\controllers\FullTextController;
use DateTime;
use SrcCore\controllers\LogsController;
use Respect\Validation\Validator;
use Slim\Psr7\Request;
use SrcCore\http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;

class LadController
{
    public function ladRequest(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (!Validator::notEmpty()->validate($body['encodedResource'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body encodedResource is empty']);
        }
        if (!Validator::notEmpty()->validate($body['extension'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body extension is empty']);
        }

        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_mercure']);
        if (empty($configuration)) {
            return $response->withStatus(400)->withJson(['errors' => 'Mercure configuration is not enabled']);
        }

        $configuration = json_decode($configuration['value'], true);
        if (empty($configuration['enabledLad']) || !$configuration['enabledLad']) {
            return $response->withStatus(200)->withJson(['message' => 'Mercure LAD is not enabled']);
        }

        $ladResult = [];
        if ($configuration['mwsLadPriority']){
            if (!Validator::notEmpty()->validate($body['filename'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body filename is empty']);
            }

            $ladConfiguration = CoreConfigModel::getJsonLoaded(['path' => 'config/ladConfiguration.json']);
            if (empty($ladConfiguration)) {
                return ['errors' => 'LAD configuration file does not exist'];
            }

            $ladResult = MwsController::launchLadMws([
                'encodedResource' => $body['encodedResource'],
                'filename' => $body['filename']
            ]);
        } else {
            $ladResult = LadController::launchLad([
                'encodedResource' => $body['encodedResource'],
                'extension' => $body['extension']
            ]);
        }

        return $response->withJson($ladResult);
    }

    public static function testAndActivateLad(Request $request, Response $response){
        $configuration = ConfigurationModel::getByPrivilege(['privilege' => 'admin_mercure']);
        if (empty($configuration)) {
            return $response->withStatus(400)->withJson(['errors' => 'Mercure configuration is not enabled']);
        }

        $ladConfiguration = CoreConfigModel::getJsonLoaded(['path' => 'config/ladConfiguration.json']);
        if (empty($ladConfiguration)) {
            return $response->withStatus(400)->withJson(['errors' => 'LAD configuration file does not exist']);
        }

        if (!is_dir($ladConfiguration['config']['mercureLadDirectory'])){
            return $response->withStatus(400)->withJson(['errors' => 'Mercure module directory does not exist']);
        }

        $testFile = $ladConfiguration['config']['mercureLadDirectory'] . DIRECTORY_SEPARATOR . 'Bernard_Pascontent.pdf';
        $encodedResource = base64_encode(file_get_contents($testFile));

        $ladResult = LadController::launchLad([
            'encodedResource' => $encodedResource,
            'extension' => 'pdf'
        ]);

        if (!empty($ladResult['subject'])){
            return $response->withJson(['success' => true]);
        }

        return $response->withStatus(400)->withJson(['errors' => 'LAD result is empty']);
    }

    public static function launchLad(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['encodedResource', 'extension']);
        ValidatorModel::stringType($aArgs, ['encodedResource', 'extension']);

        $customId = CoreConfigModel::getCustomId();

        $ladConfiguration = CoreConfigModel::getJsonLoaded(['path' => 'config/ladConfiguration.json']);
        if (empty($ladConfiguration)) {
            return ['errors' => 'LAD configuration file does not exist'];
        }

        $tmpPath = $ladConfiguration['config']['mercureLadDirectory'].'/IN/'.$customId.DIRECTORY_SEPARATOR;
        if (!is_dir($tmpPath)){
            mkdir($tmpPath, 0777);
            mkdir($ladConfiguration['config']['mercureLadDirectory'].'/OUT/'.$customId.DIRECTORY_SEPARATOR, 0777);
        }
        $tmpFilename = 'lad' . rand() . '_' . rand();

        file_put_contents($tmpPath.$tmpFilename . '.' . $aArgs['extension'], base64_decode($aArgs['encodedResource']));

        //Mercure5 fileIn fileOut fileParams
        LogsController::add([
            'isTech'    => true,
            'moduleId'  => 'mercure',
            'level'     => 'INFO',
            'tableName' => '',
            'recordId'  => '',
            'eventType' => "LAD task",
            'eventId'   => "Launch LAD on file {$tmpPath}{$tmpFilename}.{$aArgs['extension']}"
        ]);

        $command = $ladConfiguration['config']['mercureLadDirectory'].'/Mercure5 '
            .$tmpPath.$tmpFilename . '.' . $aArgs['extension'].' '
            .$ladConfiguration['config']['mercureLadDirectory'].'/OUT/'.$customId.DIRECTORY_SEPARATOR.$tmpFilename.'.xml '
            .$ladConfiguration['config']['mercureLadDirectory'].'/MERCURE5_I1_LAD_COURRIER.INI';

        exec($command.' 2>&1', $output, $return);

        $aReturn = [];
        if ($return == 0){
            $mappingMercure = $ladConfiguration['mappingLadFields'];
            $outputXml = CoreConfigModel::getXmlLoaded(['path' => $ladConfiguration['config']['mercureLadDirectory'].'/OUT/'.$customId.DIRECTORY_SEPARATOR.$tmpFilename.'.xml']);
            $mandatoryFields = [
                'subject',
                'documentDate',
                'contactIdx'
            ];
            $aReturn = [];

            foreach ($mandatoryFields as $f){
                $aReturn[$f] = "";
            }

            if ($outputXml){
                foreach ($outputXml->page as $page) {
                    foreach ($page->field as $field){
                        $nameAttributeKey = 'n';
                        $nameAttribute = (string)$field->attributes()->$nameAttributeKey;
                        $disabledField = false;
                        $normalizationRule = '';
                        $normalizationFormat = null;

                        if (isset($mappingMercure[$nameAttribute])){
                            if (isset($mappingMercure[$nameAttribute]['disabled']))
                                $disabledField = $mappingMercure[$nameAttribute]['disabled'];
                            if (isset($mappingMercure[$nameAttribute]['normalizationRule']))
                                $normalizationRule = $mappingMercure[$nameAttribute]['normalizationRule'];
                            if (isset($mappingMercure[$nameAttribute]['normalizationFormat']))
                                $normalizationFormat = $mappingMercure[$nameAttribute]['normalizationFormat'];
                            if (isset($mappingMercure[$nameAttribute]['key']))
                                $nameAttribute = $mappingMercure[$nameAttribute]['key'];
                        }

                        if (!$disabledField){
                            if (!array_key_exists($nameAttribute, $aReturn) || empty($aReturn[$nameAttribute])){
                                $aReturn[$nameAttribute] = LadController::normalizeField((string)$field[0], $normalizationRule, $normalizationFormat);
                            }
                        }
                    }

                    foreach ($page->SenderContact as $contact){
                        $aReturn["contactIdx"] = (string)$contact->Idx[0];
                    }
                }
            }
        } else {
            LogsController::add([
                'isTech'    => true,
                'moduleId'  => 'mercure',
                'level'     => 'ERROR',
                'tableName' => '',
                'recordId'  => '',
                'eventType' => "LAD task",
                'eventId'   => "LAD task error on file {$tmpPath}{$tmpFilename}.{$aArgs['extension']}, return : {$return}"
            ]);
            $aReturn = ['output' => $output, 'return' => $return, 'cmd' => $command];
        }

        LogsController::add([
            'isTech'    => true,
            'moduleId'  => 'mercure',
            'level'     => 'INFO',
            'tableName' => '',
            'recordId'  => '',
            'eventType' => "LAD task",
            'eventId'   => "LAD task success on file {$tmpPath}{$tmpFilename}.{$aArgs['extension']}"
        ]);

        return $aReturn;
    }

    private static function normalizeField($fieldContent, $normalizationRule, $normalizationFormat = null){
        switch ($normalizationRule){
            case 'DATE':
                $result = LadController::normalizeDate($fieldContent, $normalizationFormat);
                break;
            default:
                $result = $fieldContent;
                break;
        }

        return $result;
    }

    public static function getContactsIndexationState(Request $request, Response $response){
        $ladConfiguration = CoreConfigModel::getJsonLoaded(['path' => 'config/ladConfiguration.json']);
        if (empty($ladConfiguration)) {
            return $response->withJson(['errors' => 'LAD configuration file does not exist']);
        }

        $customId = CoreConfigModel::getCustomId();
        $indexedContacts = ContactModel::get([
            'select'    => ['COUNT(*)'],
            'where'     => ['lad_indexation = ? '],
            'data'      => [true]
        ]);
        $countIndexedContacts = (int)$indexedContacts[0]['count'];

        $allContacts = ContactModel::get([
            'select'    => ['COUNT(*)']
        ]);
        $countAllContacts = (int)$allContacts[0]['count'];

        $lexDirectory = $ladConfiguration['config']['contactsLexiconsDirectory'] . DIRECTORY_SEPARATOR . $customId;
        if (is_file($lexDirectory.DIRECTORY_SEPARATOR."lastindexation.flag")){
            $flagFile = fopen($lexDirectory.DIRECTORY_SEPARATOR."lastindexation.flag", "r") or die("Unable to open file!");

            $dateIndexation = fgets($flagFile);
            fclose($flagFile);
        } else {
            $dateIndexation = "Jamais";
        }


        return $response->withJson([
            'dateIndexation'            => $dateIndexation,
            'countIndexedContacts'      => $countIndexedContacts,
            'countAllContacts'          => $countAllContacts,
            'pctIndexationContacts'     => ($countIndexedContacts * 100) / $countAllContacts,
        ]);
    }

    public static function generateContactsIndexation(Request $request, Response $response){
        $customId = CoreConfigModel::getCustomId();

        $ladConfiguration = CoreConfigModel::getJsonLoaded(['path' => 'config/ladConfiguration.json']);
        if (empty($ladConfiguration)) {
            return $response->withJson(['errors' => 'LAD configuration file does not exist']);
        }

        //Création des dossiers Idx et Lexiques si non existants
        $lexDirectory = $ladConfiguration['config']['contactsLexiconsDirectory'] . DIRECTORY_SEPARATOR . $customId;
        if (!is_dir($lexDirectory)){
            mkdir($lexDirectory, 0777, true);
        }

        //Ouverture des index Lucene
        try {
            if (FullTextController::isDirEmpty($ladConfiguration['config']['contactsIndexesDirectory'])) {
                $index = \Zend_Search_Lucene::create($ladConfiguration['config']['contactsIndexesDirectory']);
            } else {
                $index = \Zend_Search_Lucene::open($ladConfiguration['config']['contactsIndexesDirectory']);
            }

            $index->setFormatVersion(\Zend_Search_Lucene::FORMAT_2_3);
            \Zend_Search_Lucene_Analysis_Analyzer::setDefault(new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());

        } catch (\Exception $e) {
            return $response->withJson(['errors' => 'Full Text index failed : ' . $e]);
        }

        //Construction de la configuration
        $tabLexicon = $tabSelect = [];
        foreach ($ladConfiguration['contactsIndexation'] as $fieldIndexation){
            $tabSelect[] = $fieldIndexation['database'];

            if (!is_null($fieldIndexation['lexicon'])){
                $tabLexicon[$fieldIndexation['lexicon']] = [];
            }
        }


        //Récupération des contacts
        $contactsToIndexes = ContactModel::get([
            'select'    => $tabSelect,
            'orderBy'   => ['id']
        ]);

        foreach ($contactsToIndexes as $c){
            //Suppression de l'ID en cours
            $term = new \Zend_Search_Lucene_Index_Term((integer)$c['id'], 'Idx');
            $terms = $index->termDocs($term);
            foreach ($terms as $value) {
                $index->delete($value);
            }

            $cIdx = new \Zend_Search_Lucene_Document();

            foreach ($ladConfiguration['contactsIndexation'] as $key => $fieldIndexation){
                try {
                    if ($key == "id"){
                        $cIdx->addField(\Zend_Search_Lucene_Field::UnIndexed($fieldIndexation['lucene'], (integer)$c['id']));
                    } else {
                        $cIdx->addField(\Zend_Search_Lucene_Field::text($fieldIndexation['lucene'], $c[$key], 'utf-8'));
                    }
                } catch (\Exception $e) {
                    return $response->withJson(['errors' => 'Contact indexation Lucene failed : ' . $e]);
                }

                //Ajout des informations aux lexiques
                if (isset($tabLexicon[$fieldIndexation['lexicon']])){
                    if (!in_array($c[$key], $tabLexicon[$fieldIndexation['lexicon']]) && !empty($c[$key])){
                        $tabLexicon[$fieldIndexation['lexicon']][] = $c[$key];
                    }
                }
            }

            $cIdx->addField(\Zend_Search_Lucene_Field::text('UserMWS', $customId, 'utf-8'));

            $index->addDocument($cIdx);
            $index->commit();
            if ((integer)$c['id'] % 50 === 0) {
                $index->optimize();
            }

            //Modification du status d'indexation
            ContactModel::update([
                'set'   => ['lad_indexation' => 1],
                'where' => ['id = ?'],
                'data'  => [$c['id']]
            ]);
        }

        foreach ($tabLexicon as $keyLexicon => $l){
            sort($l);
            $lexiconFile = fopen($lexDirectory.DIRECTORY_SEPARATOR.$keyLexicon.".txt", "w") or die("Unable to open file!");
            foreach ($l as $entry){
                fwrite($lexiconFile, $entry."\n");
            }
            fclose($lexiconFile);
        }

        $flagFile = fopen($lexDirectory.DIRECTORY_SEPARATOR."lastindexation.flag", "w") or die("Unable to open file!");
        fwrite($flagFile, date("d-m-Y H:i:s"));
        fclose($flagFile);

        return $response->withJson([
            'success'      => true
        ]);
    }

    private static function normalizeDate($content, $format){
        $result = strtolower($content);
        $result = str_replace(" ", "", $result);
        $result = LadController::stripAccents($result);
        $result = LadController::replaceMonth($result);

        $result = LadController::getElementsDate($result);
        if (!$result) return "";

        $date = new DateTime($result['year']."-".$result['month']."-".$result['day']);

        return $date->format($format);
    }

    private static function getElementsDate($dateString) {
        //$strPattern = "([0-9]|01|02|03|04|05|06|07|08|09|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31|premier|un|deux|trois|quatre|cinq|six|sept|huit|neuf|dix|onze)\s?\.?\\?\/?-?_?(12|11|10|09|08|07|06|05|04|03|02|01|décembre|decembre|novembre|octobre|septembre|aout|août|juillet|juin|mai|avril|mars|fevrier|février|janvier)\s?\.?\\?\/?-?_?(20[0-9][0-9])";
        $strPattern = "/([0-9]|01|02|03|04|05|06|07|08|09|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31|premier|un|deux|trois|quatre|cinq|six|sept|huit|neuf|dix|onze)\s?\.?\\\\?\/?-?_?(12|11|10|09|08|07|06|05|04|03|02|01|décembre|decembre|novembre|octobre|septembre|aout|août|juillet|juin|mai|avril|mars|fevrier|février|janvier)\s?\.?\\\\?\/?-?_?(20[0-9][0-9])/m";
        preg_match_all($strPattern, $dateString, $matches, PREG_SET_ORDER, 0);

        $dateElements = [];
        if (isset($matches[0][1]) && !empty($matches[0][1])){
            $dateElements['day'] = $matches[0][1];
            $dateElements['month'] = $matches[0][2];
            $dateElements['year'] = $matches[0][3];
            return $dateElements;
        }
        return false;
    }

    private static function stripAccents($content)
    {
        $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');

        return str_replace($search, $replace, $content);
    }

    private static function replaceMonth($dateString){
        $search  = array('janvier', 'janv', 'fevrier', 'fev', 'mars', 'mar', 'avril', 'avr', 'mai', 'juin', 'juillet', 'juil', 'aout', 'aou', 'septembre', 'sept', 'octobre', 'oct', 'novembre', 'nov', 'decembre', 'dec');
        $replace = array('01', '01', '02', '02', '03', '03', '04', '04', '05', '06', '07', '07', '08', '08', '09', '09', '10', '10', '11', '11', '12', '12');

        return str_replace($search, $replace, $dateString);
    }
}
