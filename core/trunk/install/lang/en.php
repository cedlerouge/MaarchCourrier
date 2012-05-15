<?php

//LANGUAGE
if (!defined('_LANGUAGE')) {
    define('_LANGUAGE', "Language");
}
if (!defined('_CHOOSE_LANGUAGE')) {
    define('_CHOOSE_LANGUAGE', "Choose your language");
}

//WELCOME
if (!defined('_WELCOME')) {
    define('_WELCOME', "Welcome");
}

if (!defined('_DESC')) {
    define('_DESC', "Welcome to Maarch install tool !
					<br>Maarch is an Electronic Archiving System that comes with Document Management, Content Management, Mail Management, Coolaborative and legal archiving/record management capabilities. Maarch is a full Open Source product under GPL v3 licence : there is no licence cost, but professional services offered by Maarch and its partner global network.
					<br>Services cover audit, installation, parametrization, customizing, IT integration, training, support and maintenance. Go to <A style='color: #800000; font-family:verdana;' href='http://www.maarch.com'> www.maarch.com</A> for more information on Maarch Professional Services.");
}

//LICENCE
if (!defined('_LICENCE')) {
    define('_LICENCE', "Licence");
}

if (!defined('_OK_WITH_LICENCE')) {
    define('_OK_WITH_LICENCE', "I agree with the terms of the licence");
}

//PREREQUISITES
if (!defined('_PREREQUISITES')) {
    define('_PREREQUISITES', "Prerequisites");
}
if (!defined('_PREREQUISITES_HEAD')) {
    define('_PREREQUISITES_HEAD', "Pr�-requis");
}

if (!defined('_LINK')) {
    define('_LINK', "See <A>http://www.maarch.org/projets/entreprise/architecture-technique-et-prerequis-pour-maarch-entreprise-1.3</A>");
}
if (!defined('_PREREQUISITES_EXP')) {
    define('_PREREQUISITES_EXP', "Maarch installation needs some prerequisites on PHP. Please report to <A style='color: #800000; font-family:verdana;' href='http://www.maarch.org/en/projects/entreprise/architecture-and-requirements'> Architecture and requirements</A> for details");
}
if (!defined('_ACTIVATED')) {
    define('_ACTIVATED', "Ok");
}if (!defined('_OPTIONNAL')) {
    define('_OPTIONNAL', "Not Ok but optional");
}if (!defined('_NOT_ACTIVATED')) {
    define('_NOT_ACTIVATED', "Not Ok");
}
if (!defined('_GENERAL')) {
    define('_GENERAL', "General");
}
if (!defined('_PHP_VERSION')) {
    define('_PHP_VERSION', "PHP Version");
}
if (!defined('_MAARCH_PATH_RIGHTS')) {
    define('_MAARCH_PATH_RIGHTS', "Rights to Maarch root directory (read/write)");
}
if (!defined('_THE_MAARCH_PATH_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS')) {
    define('_THE_MAARCH_PATH_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS', "The Maarch root path does not have the adequate rights");
}
if (!defined('_PGSQL')) {
    define('_PGSQL', "pgsql library");
}
if (!defined('_GD')) {
    define('_GD', "gd library");
}
if (!defined('_SVN')) {
    define('_SVN', "svn library");
}
if (!defined('_PEAR')) {
    define('_PEAR', "PEAR");
}
if (!defined('_MIMETYPE')) {
    define('_MIMETYPE', "MIME-TYPE");
}
if (!defined('_CLITOOLS')) {
    define('_CLITOOLS', "CLITools");
}
if (!defined('_ERROR_REPORTING')) {
    define('_ERROR_REPORTING', "error_reporting");
}
if (!defined('_DISPLAY_ERRORS')) {
    define('_DISPLAY_ERRORS', "display_errors");
}
if (!defined('_SHORT_OPEN_TAGS')) {
    define('_SHORT_OPEN_TAGS', "short_open_tags");
}
if (!defined('_MAGIC_QUOTES_GPC')) {
    define('_MAGIC_QUOTES_GPC', "magic_quotes_gpc");
}

if (!defined('_MUST_FIX')) {
    define('_MUST_FIX', "You must fix errors before continuing");
}


//DOCSERVERS
if (!defined('_DOCSERVERS')) {
    define('_DOCSERVERS', "Docservers");
}
if (!defined('_DOCSERVERS_EXP')) {
    define('_DOCSERVERS_EXP', "Electronic resources are stored on an HD, a disc bay, or a logical storage system like EMC Centera. Type in an existing folder name, on which Apache gets write access. The install will create the sub-folders declared in the dataset. More information on <A style='color: #800000; font-family:verdana;' href='http://wiki.maarch.org/Maarch_Entreprise/fr/Man/Admin/Stockage'> Gestion du stockage(FR)</A>");
}
if (!defined('_DOCSERVER_ROOT')) {
    define('_DOCSERVER_ROOT', "Root directory for storage");
}
if (!defined('_CREATE_DOCSERVERS')) {
    define('_CREATE_DOCSERVERS', "Validate");
}
if (!defined('_MUST_CHOOSE_DOCSERVERS_ROOT')) {
    define('_MUST_CHOOSE_DOCSERVERS_ROOT', "You must choose the docservers root directory");
}


//DATABASE
if (!defined('_DATABASE')) {
    define('_DATABASE', "Database");
}
if (!defined('_DATABASE_DESC')) {
    define('_DATABASE_ADD_INF', "Maarch installer is going to create a new database. Type in the database name (ex:maarch_db)");
}
if (!defined('_DATABASE_EXP')) {
    define('_DATABASE_EXP', "Maarch uses as a standard PostgreSQL engine (Version 8.3 ou more - advised : version 9). Type in server name, port #, user and password to the database.");
}

if (!defined('_DATABASE_INFOS')) {
    define('_DATABASE_INFOS', "Connection to database");
}
if (!defined('_DATABASE_CREATE')) {
    define('_DATABASE_CREATE', "Create database");
}
if (!defined('_DATABASESERVER')) {
    define('_DATABASESERVER', "Server");
}
if (!defined('_DATABASESERVERPORT')) {
    define('_DATABASESERVERPORT', "Port");
}
if (!defined('_DATABASEUSER')) {
    define('_DATABASEUSER', "User");
}
if (!defined('_DATABASEPASSWORD')) {
    define('_DATABASEPASSWORD', "Password");
}
if (!defined('_DATABASENAME')) {
    define('_DATABASENAME', "Name");
}
if (!defined('_DATABASETYPE')) {
    define('_DATABASETYPE', "Type");
}
if (!defined('_DATASET_CHOICE')) {
    define('_DATASET_CHOICE', "Selection of the sample dataset");
}
if (!defined('_DATASET_EXP')) {
    define('_DATASET_EXP', "Maarch comes with two sample test datasets : one is quite simple and illustrates basic archiving functionalities(data.sql), and the other one is showing mail management functionalities for public offices (data_mlb.sql). Both include users, document types, and everything to get a global understanding of Maarch.");
}
if (!defined('_DATASET')) {
    define('_DATASET', "Dataset");
}
if (!defined('_CHOOSE')) {
    define('_CHOOSE', "Choose one...");
}
if (!defined('_INSTALL_SUCCESS')) {
    define('_INSTALL_SUCCESS', "Installation ended successfully");
}
if (!defined('_SUBMIT')) {
    define('_SUBMIT', "Test connection");
}

//PASSWORD
if (!defined('_PASSWORD')) {
    define('_PASSWORD', "Password");
}
if (!defined('_CHOOSE_ADMIN_PASSWORD')) {
    define('_CHOOSE_ADMIN_PASSWORD', "Choose the admin password");
}

//RESUME
if (!defined('_RESUME')) {
    define('_RESUME', "Resume");
}

if (!defined('_START_MEP_1_3')) {
    define('_START_MEP_1_3', "Start with Maarch Entreprise 1.3");
}

//ERROR
if (!defined('_ERROR')) {
    define('_ERROR', "Error");
}
if (!defined('_NO_STEP')) {
    define('_NO_STEP', "No step choosen");
}
if (!defined('_BAD_STEP')) {
    define('_BAD_STEP', "The step doesn't exist");
}
if (!defined('_INSTALL_ISSUE')) {
    define('_INSTALL_ISSUE', "Installation issue");
}
if (!defined('_TRY_AGAIN')) {
    define('_TRY_AGAIN', "Please try again");
}

//BUTTON
if (!defined('_PREVIOUS')) {
    define('_PREVIOUS', "Previous");
}
if (!defined('_NEXT')) {
    define('_NEXT', "Next");
}


if (!defined('_ONE_FIELD_EMPTY')) {
    define('_ONE_FIELD_EMPTY', "You must fill all the fields");
}
