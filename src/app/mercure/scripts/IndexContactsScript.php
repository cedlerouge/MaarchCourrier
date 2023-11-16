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

use Contact\models\ContactModel;
use Convert\controllers\FullTextController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabasePDO;

// SAMPLE COMMANDS :
// (in root app)
// Launch indexation contacts : php src/app/mercure/scripts/IndexContactsScript.php --customId yourcustom --fileConfig 'config/ladConfiguration.json'

// ARGS
// --customId   : instance id;
// --fileConfig : path of LAD file configuration (optionnal);
// --force      : Forcer la réindexation de toute la base (optionnal);

IndexContactsScript::initalize($argv);

class IndexContactsScript
{
    public static function initalize(array $args)
    {
        $customId = '';
        $fileConfiguration = '';
        $isForce = false;

        if (array_search('--customId', $args) > 0) {
            $cmd = array_search('--customId', $args);
            $customId = $args[$cmd + 1];

            $fileConfiguration = 'custom/' . $customId . '/config/ladConfiguration.json';
        }

        if (array_search('--fileConfig', $args) > 0) {
            $cmd = array_search('--fileConfig', $args);
            $fileConfiguration = $args[$cmd + 1];
        }

        if (array_search('--force', $args) > 0) {
            $isForce = true;
        }

        IndexContactsScript::generateIndex(['customId' => $customId, 'fileConfig' => $fileConfiguration, 'indexAll' => $isForce]);
    }

    public static function generateIndex(array $args)
    {
        DatabasePDO::reset();
        new DatabasePDO(['customId' => $args['customId']]);


        $fileConfig = (!empty($args['fileConfig']) && is_file($args['fileConfig'])) ? $args['fileConfig'] : 'custom/' . $args['customId'] . '/config/ladConfiguration.json';

        $ladConfiguration = CoreConfigModel::getJsonLoaded(['path' => $fileConfig]);
        if (empty($ladConfiguration)) {
            echo "/!\\ LAD configuration file does not exist \n";
            return false;
        }

        $contactsIndexesDirectory = $ladConfiguration['config']['mercureLadDirectory'] . "/Lexiques/ContactsIdx";
        $contactsLexiconsDirectory = $ladConfiguration['config']['mercureLadDirectory'] . "/Lexiques/ContactsLexiques";

        if (empty($contactsIndexesDirectory)) {
            echo "/!\\ contactsIndexesDirectory parameter is empty in configuration file \n";
            return false;
        }

        if (empty($contactsLexiconsDirectory)) {
            echo "/!\\ contactsLexiconsDirectory parameter is empty in configuration file \n";
            return false;
        }

        //Création des dossiers Lexiques et Index si non existants
        $lexDirectory = $contactsLexiconsDirectory . DIRECTORY_SEPARATOR . $args['customId'];
        if (!is_dir($lexDirectory)) {
            mkdir($lexDirectory, 0775, true);
        }

        if (!is_dir($contactsIndexesDirectory)) {
            mkdir($contactsIndexesDirectory, 0775, true);
        }

        //Ouverture des index Lucene
        try {
            if (FullTextController::isDirEmpty($contactsIndexesDirectory)) {
                $index = \Zend_Search_Lucene::create($contactsIndexesDirectory);
            } else {
                $index = \Zend_Search_Lucene::open($contactsIndexesDirectory);
            }

            $index->setFormatVersion(\Zend_Search_Lucene::FORMAT_2_3);
            \Zend_Search_Lucene_Analysis_Analyzer::setDefault(new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }

        //Construction de la configuration
        $tabLexicon = $tabSelect = [];
        foreach ($ladConfiguration['contactsIndexation'] as $fieldIndexation) {
            $tabSelect[] = $fieldIndexation['database'];

            if (!is_null($fieldIndexation['lexicon'])) {
                $tabLexicon[$fieldIndexation['lexicon']] = [];

                //Initialiser le lexique si le fichier Lexique existe déjà
                if (!$args['indexAll'] && is_file($lexDirectory . DIRECTORY_SEPARATOR . $fieldIndexation['lexicon'] . ".txt")) {
                    $lexique = fopen($lexDirectory . DIRECTORY_SEPARATOR . $fieldIndexation['lexicon'] . ".txt", "r");

                    while (($entreeLexique = fgets($lexique)) !== false) {
                        if (!empty($entreeLexique)) {
                            $tabLexicon[$fieldIndexation['lexicon']][] = trim($entreeLexique);
                        }
                    }

                    fclose($lexique);
                }
            }
        }

        //Récupération des contacts
        $contactsToIndexes = ContactModel::get([
            'select' => $tabSelect,
            'orderBy' => ['id'],
            'where' => (!$args['indexAll']) ? ['lad_indexation is false'] : []
        ]);

        $cptIndex = 0;

        $listIdToUpdate = [];
        echo "[" . date("Y-m-d H:i:s") . "] Début de l'indexation \n";

        echo "0/0";

        foreach ($contactsToIndexes as $key => $c) {
            echo "\e[2K"; # clear whole line
            echo "\e[1G"; # move cursor to column 1
            echo "Indexation contact " . ($key + 1) . "/" . count($contactsToIndexes);

            //Suppression de l'ID en cours
            $term = new \Zend_Search_Lucene_Index_Term((int)$c['id'], 'Idx');
            $terms = $index->termDocs($term);
            foreach ($terms as $value) {
                $index->delete($value);
            }

            $cIdx = new \Zend_Search_Lucene_Document();

            foreach ($ladConfiguration['contactsIndexation'] as $key => $fieldIndexation) {
                try {
                    if ($key == "id") {
                        $cIdx->addField(\Zend_Search_Lucene_Field::UnIndexed($fieldIndexation['lucene'], (int)$c['id']));
                    } else {
                        $cIdx->addField(\Zend_Search_Lucene_Field::text($fieldIndexation['lucene'], $c[$key], 'utf-8'));
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                    return false;
                }

                //Ajout des informations aux lexiques
                if (isset($tabLexicon[$fieldIndexation['lexicon']])) {
                    if (!in_array($c[$key], $tabLexicon[$fieldIndexation['lexicon']]) && !empty($c[$key])) {
                        $tabLexicon[$fieldIndexation['lexicon']][] = $c[$key];
                    }
                }
            }

            $cIdx->addField(\Zend_Search_Lucene_Field::text('UserMWS', $args['customId'], 'utf-8'));

            $index->addDocument($cIdx);
            $index->commit();

            $listIdToUpdate[] = $c['id'];

            if ((int)$c['id'] % 1000 === 0) {
                echo " (optimisation ...)";
                $index->optimize();

                ContactModel::update([
                    'set' => ['lad_indexation' => 'true'],
                    'where' => ['id in (?)'],
                    'data' => [$listIdToUpdate]
                ]);

                $listIdToUpdate = [];
            }

            $cptIndex++;
        }

        //Optimisation finale
        echo " (optimisation ...)";
        $index->optimize();
        echo "[" . date("Y-m-d H:i:s") . "] Fin de l'indexation \n";

        if (count($listIdToUpdate) > 0) {
            ContactModel::update([
                'set' => ['lad_indexation' => 1],
                'where' => ['id in (?)'],
                'data' => [$listIdToUpdate]
            ]);
        }


        echo "[" . date("Y-m-d H:i:s") . "] Ecriture des lexiques \n";
        foreach ($tabLexicon as $keyLexicon => $l) {
            //sort($l);
            $lexiconFile = fopen($lexDirectory . DIRECTORY_SEPARATOR . $keyLexicon . ".txt", "w");
            if ($lexiconFile === false) {
                echo "Erreur dans la génération du fichier de lexique : " . $lexDirectory . DIRECTORY_SEPARATOR . $keyLexicon . ".txt";
                return false;
            }

            foreach ($l as $entry) {
                fwrite($lexiconFile, $entry . "\n");
            }
            fclose($lexiconFile);
        }


        $flagFile = fopen($lexDirectory . DIRECTORY_SEPARATOR . "lastindexation.flag", "w");
        if ($flagFile == false) {
            echo "Erreur d'écriture du fichier " . $lexDirectory . DIRECTORY_SEPARATOR . "lastindexation.flag" . " !\n";
        } else {
            fwrite($flagFile, date("d-m-Y H:i:s"));
            fclose($flagFile);
        }
        echo "[" . date("Y-m-d H:i:s") . "] Script d'indexation terminé !\n";
        return true;
    }
}
