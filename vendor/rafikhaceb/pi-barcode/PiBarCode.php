<?php

// ******************************************************** 2013 Pitoo.com *****
// *****                   CODES A BARRES - Php script                     *****
// *****************************************************************************
// *****              (c) 2002 - pitoo.com - mail@pitoo.com                *****
// *****************************************************************************
// *****************************************************************************
// ***** Ce script est "FREEWARE", il peut être librement copié et réutilisé
// ***** dans vos propres pages et applications. Il peut également être modifié
// ***** ou amélioré.
// ***** CEPENDANT :  par  respect pour l'auteur, avant d'utiliser,  recopier,
// ***** modifier ce code vous vous engagez à :
// ***** - conserver intact l'entête de ce fichier (les commentaires comportant
// *****   Le nom du script, le copyright le nom de l'auteur et son e-mail,  ce
// *****   texte et l'historique des mises a jour ).
// ***** - conserver intact la mention 'pitoo.com'  imprimée aléatoirement sur
// *****   l'image du code généré dans environ 2% des cas.
// ***** - envoyer un  e-mail à l'auteur mail(a)pitoo.com lui indiquant votre
// *****   intention d'utiliser le résultat de son travail.
// *****************************************************************************
// ***** Toute remarque, tout commentaire, tout rapport de bug, toute recompense
// ***** sont la bienvenue : mail(a)pitoo.com
// ***** faire un don sur PayPal : paypal(a)pitoo.com
// *****************************************************************************
// *****************************************************************************
// *****                       Historique des versions                     *****
// *****************************************************************************
// $last_version = "V2.13" ;
// ***** V2.13 - 14/01/2016 - Aspic
// *****       - Mise a jour : Ligne 335 : Compatibilité avec les nouvelles versions de PHP
// ***** V2.12 - 03/05/2013 - pitoo.com
// *****       - Correction : Ligne 931 : Erreur de variable signalée par Patrick D.
// ***** V2.11 - 11/08/2010 - pitoo.com
// *****       - Correction : Ligne 1003 : Déclaration des variables pour éviter le "Warning" PHP
// ***** V2.10 - 08/12/2009 - pitoo.com
// *****       - Correction : Ligne 998 : Sur un serveur IIS 6, problème rencontré avec la variable REQUEST_URI retournée vide.
// ***** 	     Remplacée par PHP_SELF, ca fonctionne. merci à Jean-Christophe BARON - www.cc-web.fr
// ***** V2.9  - 25/09/2008 - pitoo.com
// *****       - Corrections pour éviter l'affichage de messages "Notice" de PHP
// ***** V2.8  - 10/07/2008 - pitoo.com
// *****       - Correction de bogue
// ***** V2.7  - 10/07/2008 - pitoo.com
// *****       - Ajout du format JPG
// ***** V2.6  - 10/07/2008 - pitoo.com
// *****       - Petites corrections de bugs d'affichage et de positionnement
// ***** V2.5  - 08/07/2008 - pitoo.com
// *****       - Réécriture/Encapsulation de toutes les fonctions dans la Classe
// *****       - Ajout d'une fonction permettant d'utiliser le script pour
// *****         enregistrer l'image sur le disque au lieu de l'afficher
// *****       - Ajout de la possibilité de colorer le code
// *****       - Ajout de la possibilité de générer deux formats PNG ou GIF
// *****       - correction d'un bug dans le checksum (10='-') du C11
// *****	   - corrections majeures de structures de code
// ***** V2.05 - 13/06/2006 - pitoo.com
// *****       - Suppression des fonctions inutiles (V1)
// *****       - Ajout de commentaires
// ***** V2.04 - 23/01/2006 - pitoo.com
// *****       - Correction erreur codage Lettre A du code 39
// ***** V2.03 - 20/11/2004 - pitoo.com
// *****       - Suppression de messages warning php
// ***** V2.02 - 07/04/2004 - pitoo.com
// *****       - Suppression du checksum et des Start/Stop sur le code KIX
// ***** V2.01 - 18/12/2003 - pitoo.com
// *****       - Correction de bug pour checksum C128 = 100 / 101 / 102
// ***** V2.00 - 19/06/2003 - pitoo.com
// *****       - Réécriture de toutes les fonctions pour génération directe de
// *****         l'image du code barre en PNG plutôt que d'utiliser une
// *****         multitude de petits fichiers GIFs
// ***** V1.32 - 21/12/2002 - pitoo.com
// *****       - Écriture du code 39
// *****       - Amelioration des codes UPC et 25 ()
// ***** V1.31 - 17/12/2002 - pitoo.com
// *****       - Amelioration du code 128 (ajout du Set de characters C)
// *****       - Amelioration du code 128 (ajout du code lisible en dessous )
// ***** V1.3  - 12/12/2002 - pitoo.com
// *****       - Écriture du code 128 B
// ***** V1.2  - 01/08/2002 - pitoo.com
// *****       - Écriture du code UPC / EAN
// ***** V1.0  - 01/01/2002 - pitoo.com
// *****       - Écriture du code 25


// *****************************************************************************
// *****                        CLASSE pi_barcode                          *****
// *****************************************************************************
// ***** pi_barcode()               : Constructeur et ré-initialisation
// *****
// *****************************************************************************
// ***** Méthodes Publiques :
// *****************************************************************************
// ***** setSize($h, $w=0, $cz=0)   : Hauteur mini=15px
// *****                            : Largeur
// *****                            : Zones Calmes mini=10px
// ***** setText($text='AUTO')      : Texte sous les barres (ou AUTO ou '')
// ***** hideCodeType()             : Désactive l'impression du Type de code
// ***** setColors($fg, $bg=0)      : Couleur des Barres et du Fond
// *****
// ***** setCode($code)*            : Enregistre le code a générer
// ***** setType($type)*            : EAN, UPC, C39...
// *****
// ***** utiliser l'une ou l'autre de ces deux méthodes :
// ***** showBarcodeImage()**       : Envoie l'image PNG du code à l'affichage
// ***** writeBarcodeFile($file)**  : crée un fichier image du Code à Barres
// *****
// ***** * = appel requis
// ***** ** = appel requis pour l'un ou l'autre ou les 2
// *****
// *****************************************************************************
// ***** Méthodes Privées :
// *****************************************************************************
// ***** checkCode()                : Vérifie le CODE et positionne FULLCODE
// ***** encode()                   : Converti FULLCODE en barres
// *****

namespace PiBarCode;

class PiBarCode
{
    private const BLACK = '000000';
    private const WHITE = 'FFFFFF';

    /**
     * @var string
     */
    private $code = '';

    /**
     * @var string
     */
    private $fullCode = 'NO CODE SET';

    /**
     * @var string
     */
    private $type = 'ERR';

    /**
     * @var int
     */
    private $height = 15;

    /**
     * @var int
     */
    private $width = 0;

    /**
     * @var int
     */
    private $codeWidth;

    /**
     * @var int
     */
    private $calmZone;
    /**
     * @var string
     */
    private $hr = 'AUTO';

    /**
     * @var bool
     */
    private $showType = true;

    /**
     * @var float|int
     */
    private $background;

    /**
     * @var float|int
     */
    private $foreground;

    /**
     * @var string
     */
    private $fileType = 'PNG';

    private $ih = null;

    /**
     * Constructeur
     */
    function __construct()
    {
        $this->foreground = hexdec(self::BLACK);
        $this->background = hexdec(self::WHITE);
    }

    /**
     * Set Barcode Type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * Set Barcode String
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * Set Image Height and Extra-Width
     */
    public function setSize($height, $width = 0, $calmZone = 0): void
    {
        $this->height = (max($height, 15));
        $this->width = (max($width, 0));
        $this->calmZone = (max($calmZone, 10));
    }

    /**
     * Set the Printed Text under Bars
     */
    public function setText($text = 'AUTO'): void
    {
        $this->hr = $text;
    }

    /**
     * Disable CodeType printing
     */
    public function hideCodeType(): void
    {
        $this->showType = false;
    }

    /**
     * Set Colors
     */
    public function setColors($foreground, $background = self::WHITE): void
    {
        $background = preg_replace("/[^\dA-Fa-f]/", '', $background);
        $foreground = preg_replace("/[^\dA-Fa-f]/", '', $foreground);

        $this->foreground = hexdec($foreground);
        $this->background = hexdec($background);
    }

    /**
     * Set File Type (PNG, GIF or JPG)
     */
    public function setFileType($fileType = 'PNG'): void
    {
        $fileType = strtoupper($fileType);
        $this->fileType = 'PNG';
        if ($fileType === 'GIF' || $fileType === 'JPG') {
            $this->fileType = $fileType;
        }
    }

    /**
     * Show Image
     */
    public function showBarcodeImage()
    {
        $this->checkCode();
        $this->encode();

        if ($this->fileType == 'GIF') {
            header("Content-type: image/gif");
            imagegif($this->ih);
        } elseif ($this->fileType == 'JPG') {
            header("Content-type: image/jpeg");
            imagejpeg($this->ih);
        } else {
            header("Content-type: image/png");
            imagepng($this->ih);
        }
    }

    /**
     * Save Image
     */
    public function writeBarcodeFile($file): void
    {
        $this->checkCode();
        $this->encode();

        if ($this->fileType == 'GIF') {
            imagegif($this->ih, $file);
        } elseif ($this->fileType == 'JPG') {
            imagejpeg($this->ih, $file);
        } else {
            imagepng($this->ih, $file);
        }
    }


    /**
     * Vérification du Code
     *
     * calcul ou vérification du Checksum
     */
    private function checkCode(): void
    {
        switch ($this->type) {
            case "C128C" :

                if (preg_match("/^\d{2,48}$/", $this->code)) {
                    $tmp = strlen($this->code);
                    if (($tmp % 2) != 0) {
                        $this->fullCode = '0' . $this->code;
                    } else {
                        $this->fullCode = $this->code;
                    }
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "CODE 128C REQUIRES DIGITS ONLY";
                    break;
                }

            case "C128" :

                $carok = true;
                $long = strlen($this->code);
                $i = 0;
                while (($carok) && ($i < $long)) {
                    $tmp = ord($this->code[$i]);
                    if (($tmp < 32) || ($tmp > 126)) {
                        $carok = false;
                    }
                    $i++;
                }
                if ($carok) {
                    $this->fullCode = $this->code;
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "UNAUTHORIZED CHARS IN 128 CODE";
                }

                break;
            case "UPC" :

                $this->code = '0' . $this->code;
                $this->type = 'EAN';

            case "EAN" :

                $long = strlen($this->code);
                $factor = 3;
                $checksum = 0;

                if (preg_match("/^\d{8}$/", $this->code) || preg_match("/^\d{13}$/", $this->code)) {
                    for ($index = ($long - 1); $index > 0; $index--) {
                        $checksum += intval($this->code[$index - 1]) * $factor;
                        $factor = 4 - $factor;
                    }
                    $cc = ((1000 - $checksum) % 10);

                    if (substr($this->code, -1, 1) != $cc) {
                        $this->type = "ERR";
                        $this->fullCode = "CHECKSUM ERROR IN EAN/UPC CODE";
                    } else {
                        $this->fullCode = $this->code;
                    }
                } elseif (preg_match("/^\d{7}$/", $this->code) || preg_match("/^\d{12}$/", $this->code)) {
                    for ($index = $long; $index > 0; $index--) {
                        $checksum += intval($this->code[$index - 1]) * $factor;
                        $factor = 4 - $factor;
                    }
                    $cc = ((1000 - $checksum) % 10);

                    $this->fullCode = $this->code . $cc;
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "THIS CODE IS NOT EAN/UPC TYPE";
                }

                break;
            case "C25I" :

                $long = strlen($this->code);
                if (($long % 2) == 0) {
                    $this->code = '0' . $this->code;
                }

            case "C25" :

                if (preg_match("/^\d{1,48}$/", $this->code)) {
                    $checksum = 0;
                    $factor = 3;
                    $long = strlen($this->code);
                    for ($i = $long; $i > 0; $i--) {
                        $checksum += intval($this->code[$i - 1]) * $factor;
                        $factor = 4 - $factor;
                    }
                    $checksum = 10 - ($checksum % 10);
                    if ($checksum == 10) {
                        $checksum = 0;
                    }
                    $this->fullCode = $this->code . $checksum;
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "CODE C25 REQUIRES DIGITS ONLY";
                }

                break;
            case "C39" :

                if (preg_match("/^[\dA-Z\-.\$\/+% ]{1,48}$/i", $this->code)) {
                    $this->fullCode = '*' . $this->code . '*';
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "UNAUTHORIZED CHARS IN CODE 39";
                }

                break;
            case "CODABAR" :

                if (!preg_match("/^([ABCD])[\d\-\$:\/.+]{1,48}([ABCD])$/i", $this->code)) {
                    $this->type = "ERR";
                    $this->fullCode = "CODABAR START/STOP : ABCD";
                } else {
                    $this->fullCode = $this->code;
                }

                break;
            case "MSI" :

                if (preg_match("/^\d{1,48}$/", $this->code)) {
                    $checksum = 0;
                    $factor = 1;
                    $tmp = strlen($this->code);
                    for ($i = 0; $i < $tmp; $i++) {
                        $checksum += intval($this->code[$i]) * $factor;
                        $factor++;
                        if ($factor > 10) {
                            $factor = 1;
                        }
                    }
                    $checksum = (1000 - $checksum) % 10;
                    $this->fullCode = $this->code . $checksum;
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "CODE MSI REQUIRES DIGITS ONLY";
                }

                break;
            case "C11" :

                if (preg_match("/^[\d\-]{1,48}$/", $this->code)) {
                    $checksum = 0;
                    $factor = 1;
                    $tmp = strlen($this->code);
                    for ($i = $tmp; $i > 0; $i--) {
                        $tmp = $this->code[$i - 1];
                        if ($tmp === "-") {
                            $tmp = 10;
                        } else {
                            $tmp = intval($tmp);
                        }
                        $checksum += ($tmp * $factor);
                        $factor++;
                        if ($factor > 10) {
                            $factor = 1;
                        }
                    }
                    $checksum = $checksum % 11;
                    if ($checksum == 10) {
                        $this->fullCode = $this->code . "-";
                    } else {
                        $this->fullCode = $this->code . $checksum;
                    }
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "UNAUTHORIZED CHARS IN CODE 11";
                }

                break;
            case "POSTNET" :

                if (preg_match("/^\d{5}$/", $this->code) || preg_match("/^\d{9}$/", $this->code) || preg_match(
                        "/^\d{11}$/",
                        $this->code
                    )) {
                    $checksum = 0;
                    $tmp = strlen($this->code);
                    for ($i = $tmp; $i > 0; $i--) {
                        $checksum += intval($this->code[$i - 1]);
                    }
                    $checksum = 10 - ($checksum % 10);
                    if ($checksum === 10) {
                        $checksum = 0;
                    }
                    $this->fullCode = $this->code . $checksum;
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "POSTNET MUST BE 5/9/11 DIGITS";
                }

                break;
            case "KIX" :

                if (preg_match("/^[A-Z\d]{1,50}$/", $this->code)) {
                    $this->fullCode = $this->code;
                } else {
                    $this->type = "ERR";
                    $this->fullCode = "UNAUTHORIZED CHARS IN KIX CODE";
                }

                break;
            case "CMC7" :

                if (!preg_match("/^[\dA-E]{1,48}$/", $this->code)) {
                    $this->type = "ERR";
                    $this->fullCode = "CMC7 MUST BE NUMERIC or ABCDE";
                } else {
                    $this->fullCode = $this->code;
                }

                break;
            default :

                $this->type = "ERR";
                $this->fullCode = "UNKWOWN BARCODE TYPE";

                break;
        }
    }

    /**
     * Encodage
     *
     * Encode des symboles (a-Z, 0-9, ...) vers des barres
     */
    private function encode(): void
    {
        settype($this->fullCode, 'string');
        $lencode = strlen($this->fullCode);

        $encodedString = '';

        // Copie de la chaine dans un tableau
        $a_tmp = array();
        for ($i = 0; $i < $lencode; $i++) {
            $a_tmp[$i] = $this->fullCode[$i];
        }

        switch ($this->type) {
            case "EAN" :
            case "UPC" :
                if ($lencode == 8) {
                    $encodedString = '101'; //Premier séparateur (101)
                    for ($i = 0; $i < 4; $i++) {
                        $encodedString .= PiBarCodeType::EAN_BARS['A'][$a_tmp[$i]];
                    } //Codage partie gauche (tous de classe A)
                    $encodedString .= '01010'; //Séparateur central (01010) //Codage partie droite (tous de classe C)
                    for ($i = 4; $i < 8; $i++) {
                        $encodedString .= PiBarCodeType::EAN_BARS['C'][$a_tmp[$i]];
                    }
                    $encodedString .= '101'; //Dernier séparateur (101)
                } else {
                    $parity = PiBarCodeType::EAN_PARITY[$a_tmp[0]]; //On récupère la classe de codage de la partie gauche
                    $encodedString = '101'; //Premier séparateur (101)
                    for ($i = 1; $i < 7; $i++) {
                        $encodedString .= PiBarCodeType::EAN_BARS[$parity[$i - 1]][$a_tmp[$i]];
                    } //Codage partie gauche
                    $encodedString .= '01010'; //Séparateur central (01010) //Codage partie droite (tous de classe C)
                    for ($i = 7; $i < 13; $i++) {
                        $encodedString .= PiBarCodeType::EAN_BARS['C'][$a_tmp[$i]];
                    }
                    $encodedString .= '101'; //Dernier séparateur (101)
                }

                break;
            case "C128C" :
                $encodedString = PiBarCodeType::C128['C']; //Start
                $checksum = 105;
                $j = 1;
                for ($i = 0; $i < $lencode; $i += 2) {
                    $tmp = intval(substr($this->fullCode, $i, 2));
                    $checksum += ($j++ * $tmp);
                    $encodedString .= PiBarCodeType::C128[$tmp];
                }
                $checksum %= 103;
                $encodedString .= PiBarCodeType::C128[$checksum];
                $encodedString .= PiBarCodeType::C128['S']; //Stop
                break;
            case "C128" :
                $encodedString = PiBarCodeType::C128['B']; //Start
                $checksum = 104;
                $j = 1;
                for ($i = 0; $i < $lencode; $i++) {
                    $tmp = ord($a_tmp[$i]) - 32;
                    $checksum += ($j++ * $tmp);
                    $encodedString .= PiBarCodeType::C128[$tmp];
                }
                $checksum %= 103;
                $encodedString .= PiBarCodeType::C128[$checksum];
                $encodedString .= PiBarCodeType::C128['S']; //Stop
                break;
            case "C25" :
                $encodedString = PiBarCodeType::C25['D'] . "0"; //Start
                for ($i = 0; $i < $lencode; $i++) {
                    $num = intval($a_tmp[$i]);
                    $tmp = PiBarCodeType::C25[$num];
                    for ($j = 0; $j < 5; $j++) {
                        $tmp2 = intval(substr($tmp, $j, 1));
                        for ($k = 1; $k <= $tmp2; $k++) {
                            $encodedString .= "1";
                        }
                        $encodedString .= "0";
                    }
                }
                $encodedString .= PiBarCodeType::C25['F']; //Stop
                break;
            case "C25I" :
                $encodedString = PiBarCodeType::C25['d']; //Start
                $checksum = 0;
                for ($i = 0; $i < $lencode; $i += 2) {
                    $num1 = intval($a_tmp[$i]);
                    $num2 = intval($a_tmp[$i + 1]);
                    $checksum += ($num1 + $num2);
                    $tmp1 = PiBarCodeType::C25[$num1];
                    $tmp2 = PiBarCodeType::C25[$num2];
                    for ($j = 0; $j < 5; $j++) {
                        $t1 = intval(substr($tmp1, $j, 1));
                        $t2 = intval(substr($tmp2, $j, 1));
                        for ($k = 1; $k <= $t1; $k++) {
                            $encodedString .= "1";
                        }
                        for ($k = 1; $k <= $t2; $k++) {
                            $encodedString .= "0";
                        }
                    }
                }
                $encodedString .= PiBarCodeType::C25['f']; //Stop
                break;
            case "C39" :
                for ($i = 0; $i < $lencode; $i++) {
                    $encodedString .= PiBarCodeType::C39[$a_tmp[$i]] . "0";
                }
                $encodedString = substr($encodedString, 0, -1);
                break;
            case "CODABAR" :
                for ($i = 0; $i < $lencode; $i++) {
                    $encodedString .= PiBarCodeType::CODE_A_BAR[$a_tmp[$i]] . "0";
                }
                $encodedString = substr($encodedString, 0, -1);
                break;
            case "MSI" :
                $encodedString = PiBarCodeType::MSI['D']; //Start
                for ($i = 0; $i < $lencode; $i++) {
                    $encodedString .= PiBarCodeType::MSI[intval($a_tmp[$i])];
                }
                $encodedString .= PiBarCodeType::MSI['F']; //Stop
                break;
            case "C11" :
                $encodedString = PiBarCodeType::C11['S'] . "0"; //Start
                for ($i = 0; $i < $lencode; $i++) {
                    $encodedString .= PiBarCodeType::C11[$a_tmp[$i]] . "0";
                }
                $encodedString .= PiBarCodeType::C11['S']; //Stop
                break;
            case "POSTNET" :
                $encodedString = '1'; //Start
                for ($i = 0; $i < $lencode; $i++) {
                    $encodedString .= PiBarCodeType::POSTNET[$a_tmp[$i]];
                }
                $encodedString .= '1'; //Stop

                $this->codeWidth = (strlen($encodedString) * 4) - 4;
                if ($this->hr != '') {
                    $this->height = 32;
                } else {
                    $this->height = 22;
                }
                break;
            case "KIX" :
                for ($i = 0; $i < $lencode; $i++) {
                    $encodedString .= PiBarCodeType::KIX[$a_tmp[$i]];
                }

                $this->codeWidth = (strlen($encodedString) * 4) - 4;
                if ($this->hr != '') {
                    $this->height = 32;
                } else {
                    $this->height = 22;
                }
                break;
            case "CMC7" :
                $encodedString = $this->fullCode;

                $this->codeWidth = ($lencode * 24) - 8;
                $this->height = 35;
                break;
            case "ERR" :
                $this->codeWidth = (imagefontwidth(2) * $lencode);
                $this->height = max($this->height, 36);
                break;
            default:
                // ignore
                break;
        }

        $nb_elem = strlen($encodedString);
        $this->codeWidth = max($this->codeWidth, $nb_elem);
        $this->width = max($this->width, $this->codeWidth + ($this->calmZone * 2));

        /**
         * Création de l'image du code
         */

        //Initialisation de l'image
        $txtPosX = $posX = intval(($this->width - $this->codeWidth) / 2); // position X
        $posY = 0; // position Y
        $intL = 1; // largeur de la barre

        // détruire éventuellement l'image existante
        if ($this->ih) {
            imagedestroy($this->ih);
        }

        $this->ih = imagecreate($this->width, $this->height);

        // colors
        $color[0] = ImageColorAllocate(
            $this->ih,
            0xFF & ($this->background >> 0x10),
            0xFF & ($this->background >> 0x8),
            0xFF & $this->background
        );
        $color[1] = ImageColorAllocate(
            $this->ih,
            0xFF & ($this->foreground >> 0x10),
            0xFF & ($this->foreground >> 0x8),
            0xFF & $this->foreground
        );
        $color[2] = ImageColorAllocate($this->ih, 160, 160, 160); // greyed

        imagefilledrectangle($this->ih, 0, 0, $this->width, $this->height, $color[0]);


        // Gravure du code
        for ($i = 0; $i < $nb_elem; $i++) {
            // Hauteur des barres dans l'image
            $intH = $this->height;
            if ($this->hr != '') {
                switch ($this->type) {
                    case "EAN" :
                    case "UPC" :
                        if ($i <= 2 || $i >= ($nb_elem - 3) || ($i >= ($nb_elem / 2) - 2 && $i <= ($nb_elem / 2) + 2)) {
                            $intH -= 6;
                        } else {
                            $intH -= 11;
                        }
                        break;
                    default :
                        if ($i > 0 && $i < ($nb_elem - 1)) {
                            $intH -= 11;
                        }
                }
            }

            // Gravure des barres
            $fill_color = $encodedString[$i];
            switch ($this->type) {
                case "POSTNET" :
                    if ($fill_color == "1") {
                        imagefilledrectangle($this->ih, $posX, ($posY + 1), $posX + 1, ($posY + 20), $color[1]);
                    } else {
                        imagefilledrectangle($this->ih, $posX, ($posY + 12), $posX + 1, ($posY + 20), $color[1]);
                    }
                    $intL = 4;
                    break;
                case "KIX" :
                    if ($fill_color == "0") {
                        imagefilledrectangle($this->ih, $posX, ($posY + 1), $posX + 1, ($posY + 13), $color[1]);
                    } elseif ($fill_color == "1") {
                        imagefilledrectangle($this->ih, $posX, ($posY + 7), $posX + 1, ($posY + 19), $color[1]);
                    } elseif ($fill_color == "2") {
                        imagefilledrectangle($this->ih, $posX, ($posY + 7), $posX + 1, ($posY + 13), $color[1]);
                    } else {
                        imagefilledrectangle($this->ih, $posX, ($posY + 1), $posX + 1, ($posY + 19), $color[1]);
                    }
                    $intL = 4;
                    break;
                case "CMC7" :
                    $tmp = PiBarCodeType::CMC7[$fill_color];
                    $coord = explode("|", $tmp);

                    for ($j = 0; $j < sizeof($coord); $j++) {
                        $pts = explode("-", $coord[$j]);
                        $deb = explode(",", $pts[0]);
                        $X1 = $deb[0] + $posX;
                        $Y1 = $deb[1] + 5;
                        $fin = explode(",", $pts[1]);
                        $X2 = $fin[0] + $posX;
                        $Y2 = $fin[1] + 5;

                        imagefilledrectangle($this->ih, $X1, $Y1, $X2, $Y2, $color[1]);
                    }
                    $intL = 24;
                    break;
                default :
                    if ($fill_color == "1") {
                        imagefilledrectangle($this->ih, $posX, $posY, $posX, ($posY + $intH), $color[1]);
                    }
            }

            //Déplacement du pointeur
            $posX += $intL;
        }

        // Ajout du texte
        $ifw = imagefontwidth(3);
        $ifh = imagefontheight(3) - 1;

        $text = ($this->hr == 'AUTO' ? $this->code : $this->hr);

        switch ($this->type) {
            case "ERR" :
                $ifw = imagefontwidth(3);
                imagestring($this->ih, 3, floor((($this->width) - ($ifw * 7)) / 2), 1, "ERROR :", $color[1]);
                imagestring($this->ih, 2, 10, 13, $this->fullCode, $color[1]);
                $ifw = imagefontwidth(1);
                imagestring(
                    $this->ih,
                    1,
                    ($this->width) - ($ifw * 9) - 2,
                    $this->height - $ifh,
                    "Pitoo.com",
                    $color[2]
                );
                break;
            case "EAN" :
                if ($text != '' && (strlen($this->fullCode) > 10) && ($this->fullCode[0] > 0)) {
                    imagestring(
                        $this->ih,
                        3,
                        $txtPosX - 7,
                        $this->height - $ifh,
                        substr($this->fullCode, -13, 1),
                        $color[1]
                    );
                }
            case "UPC" :
                if ($text != '') {
                    if (strlen($this->fullCode) > 10) {
                        imagestring(
                            $this->ih,
                            3,
                            $txtPosX + 4,
                            $this->height - $ifh,
                            substr($this->fullCode, 1, 6),
                            $color[1]
                        );
                        imagestring(
                            $this->ih,
                            3,
                            $txtPosX + 50,
                            $this->height - $ifh,
                            substr($this->fullCode, 7, 6),
                            $color[1]
                        );
                    } else {
                        imagestring(
                            $this->ih,
                            3,
                            $txtPosX + 4,
                            $this->height - $ifh,
                            substr($this->fullCode, 0, 4),
                            $color[1]
                        );
                        imagestring(
                            $this->ih,
                            3,
                            $txtPosX + 36,
                            $this->height - $ifh,
                            substr($this->fullCode, 4, 4),
                            $color[1]
                        );
                    }
                }
                break;
            case "CMC7" :
                break;
            default :
                if ($text != '') {
                    imagestring(
                        $this->ih,
                        3,
                        intval((($this->width) - ($ifw * strlen($text))) / 2) + 1,
                        $this->height - $ifh,
                        $text,
                        $color[1]
                    );
                }
        }

        // de temps à autre, ajouter pitoo.com *** Merci de ne pas supprimer cette fonction ***
        $ifw = imagefontwidth(1) * 9;
        if ((rand(0, 50) < 1) && ($this->height >= $ifw)) {
            imagestringup($this->ih, 1, $nb_elem + 12, $this->height - 2, "Pitoo.com", $color[2]);
        }

        // impression du type de code (si demandé)
        if ($this->showType === true) {
            if (($this->type == "EAN") && (strlen($this->fullCode) > 10) && ($this->fullCode[0] > 0) && ($text != '')) {
                imagestringup($this->ih, 1, 0, $this->height - 12, $this->type, $color[2]);
            } elseif ($this->type == "POSTNET") {
                imagestringup($this->ih, 1, 0, $this->height - 2, "POST", $color[2]);
            } elseif ($this->type != "ERR") {
                imagestringup($this->ih, 1, 0, $this->height - 2, $this->type, $color[2]);
            }
        }
    }
}
