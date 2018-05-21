<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Rest Routes File
* @author dev@maarch.org
*/

require '../vendor/autoload.php';

//Root application position
chdir('..');

$userId = null;
if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    if (\SrcCore\models\SecurityModel::authentication(['userId' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
        $userId = $_SERVER['PHP_AUTH_USER'];
    }
} else {
    $cookie = \SrcCore\models\SecurityModel::getCookieAuth();
    if (!empty($cookie) && \SrcCore\models\SecurityModel::cookieAuthentication($cookie)) {
        \SrcCore\models\SecurityModel::setCookieAuth(['userId' => $cookie['userId']]);
        $userId = $cookie['userId'];
    }
}

if (empty($userId)) {
    echo 'Authentication Failed';
    exit();
}

$language = \SrcCore\models\CoreConfigModel::getLanguage();

$customId = \SrcCore\models\CoreConfigModel::getCustomId();
if (file_exists("custom/{$customId}/src/core/lang/lang-{$language}.php")) {
    require_once("custom/{$customId}/src/core/lang/lang-{$language}.php");
}

require_once("src/core/lang/lang-{$language}.php");


$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);


//Initialize
$app->get('/initialize', \SrcCore\controllers\CoreController::class . ':initialize');

//Actions
$app->get('/actions', \Action\controllers\ActionController::class . ':get');
$app->get('/initAction', \Action\controllers\ActionController::class . ':initAction');
$app->get('/actions/{id}', \Action\controllers\ActionController::class . ':getById');
$app->post('/actions', \Action\controllers\ActionController::class . ':create');
$app->put('/actions/{id}', \Action\controllers\ActionController::class . ':update');
$app->delete('/actions/{id}', \Action\controllers\ActionController::class . ':delete');

//Administration
$app->get('/administration', \SrcCore\controllers\CoreController::class . ':getAdministration');

//AutoComplete
$app->get('/autocomplete/contacts', \SrcCore\controllers\AutoCompleteController::class . ':getContacts');
$app->get('/autocomplete/users', \SrcCore\controllers\AutoCompleteController::class . ':getUsers');
$app->get('/autocomplete/users/administration', \SrcCore\controllers\AutoCompleteController::class . ':getUsersForAdministration');
$app->get('/autocomplete/users/visa', \SrcCore\controllers\AutoCompleteController::class . ':getUsersForVisa');
$app->get('/autocomplete/entities', \SrcCore\controllers\AutoCompleteController::class . ':getEntities');
$app->get('/autocomplete/statuses', \SrcCore\controllers\AutoCompleteController::class . ':getStatuses');
$app->get('/autocomplete/banAddresses', \SrcCore\controllers\AutoCompleteController::class . ':getBanAddresses');

//Baskets
$app->get('/baskets', \Basket\controllers\BasketController::class . ':get');
$app->post('/baskets', \Basket\controllers\BasketController::class . ':create');
$app->get('/baskets/{id}', \Basket\controllers\BasketController::class . ':getById');
$app->put('/baskets/{id}', \Basket\controllers\BasketController::class . ':update');
$app->delete('/baskets/{id}', \Basket\controllers\BasketController::class . ':delete');
$app->get('/baskets/{id}/groups', \Basket\controllers\BasketController::class . ':getGroups');
$app->post('/baskets/{id}/groups', \Basket\controllers\BasketController::class . ':createGroup');
$app->put('/baskets/{id}/groups/{groupId}', \Basket\controllers\BasketController::class . ':updateGroup');
$app->delete('/baskets/{id}/groups/{groupId}', \Basket\controllers\BasketController::class . ':deleteGroup');
$app->get('/baskets/{id}/groups/data', \Basket\controllers\BasketController::class . ':getDataForGroupById');
$app->get('/sortedBaskets', \Basket\controllers\BasketController::class . ':getSorted');
$app->put('/sortedBaskets/{id}', \Basket\controllers\BasketController::class . ':updateSort');

//BatchHistories
$app->get('/batchHistories', \History\controllers\BatchHistoryController::class . ':get');

//Contacts
$app->post('/contacts', \Contact\controllers\ContactController::class . ':create');
$app->get('/contacts/{contactId}/communication', \Contact\controllers\ContactController::class . ':getCommunicationByContactId');
$app->get('/contactsGroups', \Contact\controllers\ContactGroupController::class . ':get');
$app->post('/contactsGroups', \Contact\controllers\ContactGroupController::class . ':create');
$app->get('/contactsGroups/{id}', \Contact\controllers\ContactGroupController::class . ':getById');
$app->put('/contactsGroups/{id}', \Contact\controllers\ContactGroupController::class . ':update');
$app->delete('/contactsGroups/{id}', \Contact\controllers\ContactGroupController::class . ':delete');
$app->post('/contactsGroups/{id}/contacts', \Contact\controllers\ContactGroupController::class . ':addContacts');
$app->delete('/contactsGroups/{id}/contacts/{addressId}', \Contact\controllers\ContactGroupController::class . ':deleteContact');
$app->get('/contactsTypes', \Contact\controllers\ContactTypeController::class . ':get');


//Docservers
$app->get('/docservers', \Docserver\controllers\DocserverController::class . ':get');
$app->get('/docservers/{id}', \Docserver\controllers\DocserverController::class . ':getById');

//DocserverTypes
$app->get('/docserverTypes', \Docserver\controllers\DocserverTypeController::class . ':get');
$app->get('/docserverTypes/{id}', \Docserver\controllers\DocserverTypeController::class . ':getById');

//doctypes
$app->get('/doctypes', \Doctype\controllers\FirstLevelController::class . ':getTree');
$app->post('/doctypes/firstLevel', \Doctype\controllers\FirstLevelController::class . ':create');
$app->get('/doctypes/firstLevel/{id}', \Doctype\controllers\FirstLevelController::class . ':getById');
$app->put('/doctypes/firstLevel/{id}', \Doctype\controllers\FirstLevelController::class . ':update');
$app->delete('/doctypes/firstLevel/{id}', \Doctype\controllers\FirstLevelController::class . ':delete');
$app->post('/doctypes/secondLevel', \Doctype\controllers\SecondLevelController::class . ':create');
$app->get('/doctypes/secondLevel/{id}', \Doctype\controllers\SecondLevelController::class . ':getById');
$app->put('/doctypes/secondLevel/{id}', \Doctype\controllers\SecondLevelController::class . ':update');
$app->delete('/doctypes/secondLevel/{id}', \Doctype\controllers\SecondLevelController::class . ':delete');
$app->post('/doctypes/types', \Doctype\controllers\DoctypeController::class . ':create');
$app->get('/doctypes/types/{id}', \Doctype\controllers\DoctypeController::class . ':getById');
$app->put('/doctypes/types/{id}', \Doctype\controllers\DoctypeController::class . ':update');
$app->delete('/doctypes/types/{id}', \Doctype\controllers\DoctypeController::class . ':delete');
$app->put('/doctypes/types/{id}/redirect', \Doctype\controllers\DoctypeController::class . ':deleteRedirect');
$app->get('/administration/doctypes/new', \Doctype\controllers\FirstLevelController::class . ':initDoctypes');

//Entities
$app->get('/entities', \Entity\controllers\EntityController::class . ':get');
$app->post('/entities', \Entity\controllers\EntityController::class . ':create');
$app->get('/entities/{id}', \Entity\controllers\EntityController::class . ':getById');
$app->put('/entities/{id}', \Entity\controllers\EntityController::class . ':update');
$app->delete('/entities/{id}', \Entity\controllers\EntityController::class . ':delete');
$app->get('/entities/{id}/details', \Entity\controllers\EntityController::class . ':getDetailledById');
$app->put('/entities/{id}/reassign/{newEntityId}', \Entity\controllers\EntityController::class . ':reassignEntity');
$app->put('/entities/{id}/status', \Entity\controllers\EntityController::class . ':updateStatus');
$app->get('/entityTypes', \Entity\controllers\EntityController::class . ':getTypes');

//Groups
$app->get('/groups', \Group\controllers\GroupController::class . ':get');
$app->post('/groups', \Group\controllers\GroupController::class . ':create');
$app->get('/groups/{id}', \Group\controllers\GroupController::class . ':getById');
$app->put('/groups/{id}', \Group\controllers\GroupController::class . ':update');
$app->delete('/groups/{id}', \Group\controllers\GroupController::class . ':delete');
$app->get('/groups/{id}/details', \Group\controllers\GroupController::class . ':getDetailledById');
$app->put('/groups/{id}/services/{serviceId}', \Group\controllers\GroupController::class . ':updateService');
$app->put('/groups/{id}/reassign/{newGroupId}', \Group\controllers\GroupController::class . ':reassignUsers');

//Histories
$app->get('/histories', \History\controllers\HistoryController::class . ':get');
$app->get('/histories/users/{userSerialId}', \History\controllers\HistoryController::class . ':getByUserId');

//Jnlp
$app->get('/jnlp', \SrcCore\controllers\CoreController::class . ':renderJnlp');

//Links
$app->get('/links/resId/{resId}', \Link\controllers\LinkController::class . ':getByResId');

//Listinstance
$app->get('/listinstance/{id}', \Entity\controllers\ListInstanceController::class . ':getById');

//ListTemplates
$app->get('/listTemplates', \Entity\controllers\ListTemplateController::class . ':get');
$app->post('/listTemplates', \Entity\controllers\ListTemplateController::class . ':create');
$app->get('/listTemplates/{id}', \Entity\controllers\ListTemplateController::class . ':getById');
$app->put('/listTemplates/{id}', \Entity\controllers\ListTemplateController::class . ':update');
$app->delete('/listTemplates/{id}', \Entity\controllers\ListTemplateController::class . ':delete');
$app->get('/listTemplates/entityDest/itemId/{itemId}', \Entity\controllers\ListTemplateController::class . ':getByUserWithEntityDest');
$app->put('/listTemplates/entityDest/itemId/{itemId}', \Entity\controllers\ListTemplateController::class . ':updateByUserWithEntityDest');
$app->get('/listTemplates/types/{typeId}/roles', \Entity\controllers\ListTemplateController::class . ':getTypeRoles');
$app->put('/listTemplates/types/{typeId}/roles', \Entity\controllers\ListTemplateController::class . ':updateTypeRoles');

//Parameters
$app->get('/parameters', \Parameter\controllers\ParameterController::class . ':get');
$app->post('/parameters', \Parameter\controllers\ParameterController::class . ':create');
$app->get('/parameters/{id}', \Parameter\controllers\ParameterController::class . ':getById');
$app->put('/parameters/{id}', \Parameter\controllers\ParameterController::class . ':update');
$app->delete('/parameters/{id}', \Parameter\controllers\ParameterController::class . ':delete');

//Priorities
$app->get('/priorities', \Priority\controllers\PriorityController::class . ':get');
$app->post('/priorities', \Priority\controllers\PriorityController::class . ':create');
$app->get('/priorities/{id}', \Priority\controllers\PriorityController::class . ':getById');
$app->put('/priorities/{id}', \Priority\controllers\PriorityController::class . ':update');
$app->delete('/priorities/{id}', \Priority\controllers\PriorityController::class . ':delete');
$app->get('/sortedPriorities', \Priority\controllers\PriorityController::class . ':getSorted');
$app->put('/sortedPriorities', \Priority\controllers\PriorityController::class . ':updateSort');

//Reports
$app->get('/reports/groups', \Report\controllers\ReportController::class . ':getGroups');
$app->get('/reports/groups/{groupId}', \Report\controllers\ReportController::class . ':getByGroupId');
$app->put('/reports/groups/{groupId}', \Report\controllers\ReportController::class . ':updateForGroupId');

//Ressources
$app->post('/res', \Resource\controllers\ResController::class . ':create');
$app->post('/resExt', \Resource\controllers\ResController::class . ':createExt');
$app->put('/res/resource/status', \Resource\controllers\ResController::class . ':updateStatus');
$app->post('/res/list', \Resource\controllers\ResController::class . ':getList');
$app->get('/res/{resId}/lock', \Resource\controllers\ResController::class . ':isLock');
$app->get('/res/{resId}/notes/count', \Resource\controllers\ResController::class . ':getNotesCountForCurrentUserById');
$app->put('/res/externalInfos', \Resource\controllers\ResController::class . ':updateExternalInfos');

//SignatureBook
$app->get('/{basketId}/signatureBook/resList', \SignatureBook\controllers\SignatureBookController::class . ':getResList');
$app->get('/{basketId}/signatureBook/resList/details', \SignatureBook\controllers\SignatureBookController::class . ':getDetailledResList');
$app->get('/groups/{groupId}/baskets/{basketId}/signatureBook/{resId}', \SignatureBook\controllers\SignatureBookController::class . ':getSignatureBook');
$app->get('/signatureBook/{resId}/attachments', \SignatureBook\controllers\SignatureBookController::class . ':getAttachmentsById');
$app->get('/signatureBook/{resId}/incomingMailAttachments', \SignatureBook\controllers\SignatureBookController::class . ':getIncomingMailAndAttachmentsById');
$app->put('/signatureBook/{resId}/unsign', \SignatureBook\controllers\SignatureBookController::class . ':unsignFile');
$app->put('/attachments/{id}/inSignatureBook', \Attachment\controllers\AttachmentController::class . ':setInSignatureBook');

//statuses
$app->get('/statuses', \Status\controllers\StatusController::class . ':get');
$app->post('/statuses', \Status\controllers\StatusController::class . ':create');
$app->get('/statuses/{identifier}', \Status\controllers\StatusController::class . ':getByIdentifier');
$app->get('/status/{id}', \Status\controllers\StatusController::class . ':getById');
$app->put('/statuses/{identifier}', \Status\controllers\StatusController::class . ':update');
$app->delete('/statuses/{identifier}', \Status\controllers\StatusController::class . ':delete');
$app->get('/administration/statuses/new', \Status\controllers\StatusController::class . ':getNewInformations');

//Templates
$app->post('/templates/{id}/duplicate', \Template\controllers\TemplateController::class . ':duplicate');

//Users
$app->get('/users', \User\controllers\UserController::class . ':get');
$app->post('/users', \User\controllers\UserController::class . ':create');
$app->get('/users/{id}/details', \User\controllers\UserController::class . ':getDetailledById');
$app->put('/users/{id}', \User\controllers\UserController::class . ':update');
$app->put('/users/{id}/password', \User\controllers\UserController::class . ':resetPassword');
$app->get('/users/{userId}/status', \User\controllers\UserController::class . ':getStatusByUserId');
$app->put('/users/{id}/status', \User\controllers\UserController::class . ':updateStatus');
$app->delete('/users/{id}', \User\controllers\UserController::class . ':delete');
$app->post('/users/{id}/groups', \User\controllers\UserController::class . ':addGroup');
$app->put('/users/{id}/groups/{groupId}', \User\controllers\UserController::class . ':updateGroup');
$app->delete('/users/{id}/groups/{groupId}', \User\controllers\UserController::class . ':deleteGroup');
$app->post('/users/{id}/entities', \User\controllers\UserController::class . ':addEntity');
$app->put('/users/{id}/entities/{entityId}', \User\controllers\UserController::class . ':updateEntity');
$app->put('/users/{id}/entities/{entityId}/primaryEntity', \User\controllers\UserController::class . ':updatePrimaryEntity');
$app->get('/users/{id}/entities/{entityId}', \User\controllers\UserController::class . ':isEntityDeletable');
$app->delete('/users/{id}/entities/{entityId}', \User\controllers\UserController::class . ':deleteEntity');
$app->post('/users/{id}/signatures', \User\controllers\UserController::class . ':addSignature');
$app->get('/users/{id}/signatures/{signatureId}', \User\controllers\UserController::class . ':getImageSignature');
$app->put('/users/{id}/signatures/{signatureId}', \User\controllers\UserController::class . ':updateSignature');
$app->delete('/users/{id}/signatures/{signatureId}', \User\controllers\UserController::class . ':deleteSignature');
$app->post('/users/{id}/redirectedBaskets', \User\controllers\UserController::class . ':setRedirectedBaskets');
$app->delete('/users/{id}/redirectedBaskets/{basketId}', \User\controllers\UserController::class . ':deleteRedirectedBaskets');
$app->put('/users/{id}/baskets', \User\controllers\UserController::class . ':updateBasketsDisplay');

//VersionsUpdate
$app->get('/versionsUpdate', \VersionUpdate\controllers\VersionUpdateController::class . ':get');

//CurrentUser
$app->get('/currentUser/profile', \User\controllers\UserController::class . ':getProfile');
$app->put('/currentUser/profile', \User\controllers\UserController::class . ':updateProfile');
$app->put('/currentUser/password', \User\controllers\UserController::class . ':updateCurrentUserPassword');
$app->post('/currentUser/emailSignature', \User\controllers\UserController::class . ':createCurrentUserEmailSignature');
$app->put('/currentUser/emailSignature/{id}', \User\controllers\UserController::class . ':updateCurrentUserEmailSignature');
$app->delete('/currentUser/emailSignature/{id}', \User\controllers\UserController::class . ':deleteCurrentUserEmailSignature');
$app->put('/currentUser/groups/{groupId}/baskets/{basketId}', \User\controllers\UserController::class . ':updateBasketPreference');

//Notifications
$app->get('/notifications', \Notification\controllers\NotificationController::class . ':get');
$app->post('/notifications', \Notification\controllers\NotificationController::class . ':create');
$app->get('/notifications/schedule', \Notification\controllers\NotificationScheduleController::class . ':get');
$app->post('/notifications/schedule', \Notification\controllers\NotificationScheduleController::class . ':create');
$app->put('/notifications/{id}', \Notification\controllers\NotificationController::class . ':update');
$app->delete('/notifications/{id}', \Notification\controllers\NotificationController::class . ':delete');
$app->get('/administration/notifications/new', \Notification\controllers\NotificationController::class . ':initNotification');
$app->get('/notifications/{id}', \Notification\controllers\NotificationController::class . ':getBySid');
$app->post('/scriptNotification', \Notification\controllers\NotificationScheduleController::class . ':createScriptNotification');

$app->post('/saveNumericPackage', \Sendmail\Controllers\ReceiveMessageExchangeController::class . ':saveMessageExchange');
$app->post('/saveMessageExchangeReturn', \Sendmail\Controllers\ReceiveMessageExchangeController::class . ':saveMessageExchangeReturn');
$app->post('/saveMessageExchangeReview', \Sendmail\Controllers\MessageExchangeReviewController::class . ':saveMessageExchangeReview');

$app->run();
