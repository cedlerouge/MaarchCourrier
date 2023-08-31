<?php

/**
 * Copyright Maarch since 2008 under license.
 * See LICENSE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Index Contacts Script
 * @author dev@maarch.org
 */

namespace Mercure\scripts;

require 'vendor/autoload.php';

use Configuration\models\ConfigurationModel;
use Contact\models\ContactModel;
use Convert\controllers\FullTextController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabasePDO;

// SAMPLE COMMANDS :
// (in root app)
// Launch indexation contacts : php src/app/mercure/scripts/IndexContactsScript.php --customId yourcustom --fileConfig 'config/ladConfiguration.json'

// ARGS
// --customId    : instance id;
// --fileConfig      : path of LAD file configuration;

IndexContactsScript::initalize($argv);

class IndexContactsScript
{
    public static function initalize(array $args)
    {
        $customId = '';
        $fileConfiguration    = '';

        if (array_search('--customId', $args) > 0) {
            $cmd = array_search('--customId', $args);
            $customId = $args[$cmd+1];
        }

        if (array_search('--fileConfig', $args) > 0) {
            $cmd = array_search('--fileConfig', $args);
            $fileConfiguration = $args[$cmd+1];
        }

        IndexContactsScript::generateIndex(['customId' => $customId, 'fileConfig' => $fileConfiguration]);
    }

    public static function generateIndex(array $args)
    {
        DatabasePDO::reset();
        new DatabasePDO(['customId' => $args['customId']]);

        $fileConfig = (!empty($args['fileConfig'])) ? $args['fileConfig'] : 'config/ladConfiguration.json';

        $ladConfiguration = CoreConfigModel::getJsonLoaded(['path' => $fileConfig]);
        if (empty($ladConfiguration)) {
            echo "/!\\ LAD configuration file does not exist \n";
            return false;
        }

        if (empty($ladConfiguration['config']['contactsLexiconsDirectory'])){
            echo "/!\\ contactsLexiconsDirectory parameter is empty in configuration file \n";
            return false;
        }

        if (empty($ladConfiguration['config']['contactsIndexesDirectory'])){
            echo "/!\\ contactsIndexesDirectory parameter is empty in configuration file \n";
            return false;
        }

        //Création des dossiers Lexiques et Index si non existants
        $lexDirectory = $ladConfiguration['config']['contactsLexiconsDirectory'] . DIRECTORY_SEPARATOR . $args['customId'];
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
            echo "/!\\ Open index directory failed \n";
            return false;
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


        $cptIndex = 0;
        foreach ($contactsToIndexes as $c){
            if ($cptIndex%50 == 0){
                echo "Indexation contact ".$cptIndex."/".count($contactsToIndexes)."\n";
            }

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
                    echo '/!\\ Contact indexation Lucene failed : ' . $e;
                    return false;
                }

                //Ajout des informations aux lexiques
                if (isset($tabLexicon[$fieldIndexation['lexicon']])){
                    if (!in_array($c[$key], $tabLexicon[$fieldIndexation['lexicon']]) && !empty($c[$key])){
                        $tabLexicon[$fieldIndexation['lexicon']][] = $c[$key];
                    }
                }
            }

            $cIdx->addField(\Zend_Search_Lucene_Field::text('UserMWS', $args['customId'], 'utf-8'));

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

            $cptIndex++;
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

        echo "Contacts indexation done !\n";
    }
}
