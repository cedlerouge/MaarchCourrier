<?php
/*
*
*    Copyright 2012 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief    Script to return ajax result
*
* @file     notes_ajax_content.php
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
* @ingroup  notes
*/

require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "modules".DIRECTORY_SEPARATOR."entities"
        .DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."EntityControler.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
        ."class".DIRECTORY_SEPARATOR."class_lists.php";
require_once 'modules/notes/notes_tables.php';
require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
    . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";
    
$core_tools = new core_tools();
$request    = new request();
$sec        = new security();
$ent        = new EntityControler();
$notesTools = new notes();
$list       = new lists();

$destination = '';

function _parse($text) {
    //...
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);

    //
    $text = str_replace("\n", "\\n ", $text);
    return $text;
}
    
$core_tools->load_lang();
$request->connect();

$status = 0;
$error = $content = $js = $parameters = '';

$labels_array = array();

if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
} else {
    $error = _ERROR_IN_NOTES_FORM_GENERATION;
    $status = 1;
}

//Identifier of the element wich is noted
$identifier = '';
if (isset($_REQUEST['identifier']) && ! empty($_REQUEST['identifier'])) {
    $identifier = trim($_REQUEST['identifier']);
}

//Collection
if (isset($_REQUEST['coll_id']) && ! empty($_REQUEST['coll_id'])) {
    $collId = trim($_REQUEST['coll_id']);
    $parameters .= '&coll_id='.$_REQUEST['coll_id'];
    $view = $sec->retrieve_view_from_coll_id($collId);
    $table = $sec->retrieve_table_from_coll($collId);
    //retrieve the process entity of document
    $request->query(
        "select destination from " . $table . " where res_id = " . $identifier
    );
    $resultDest = $request->fetch_object();
    $destination = $resultDest->destination;
}

//Keep some origin parameters
if (isset($_REQUEST['size']) && !empty($_REQUEST['size'])) $parameters .= '&size='.$_REQUEST['size'];
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
    $parameters .= '&order='.$_REQUEST['order'];
    if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) $parameters .= '&order_field='.$_REQUEST['order_field'];
}
if (isset($_REQUEST['what']) && !empty($_REQUEST['what'])) $parameters .= '&what='.$_REQUEST['what'];
if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) $parameters .= '&template='.$_REQUEST['template'];
if (isset($_REQUEST['start']) && !empty($_REQUEST['start'])) $parameters .= '&start='.$_REQUEST['start'];

//Keep the origin to reload the origin list
$list_origin = $origin = '';
if (isset($_REQUEST['origin']) && !empty($_REQUEST['origin'])) {
    //
    $origin = $_REQUEST['origin'];

    if ($_REQUEST['origin'] == "document") {
        //From document
        $list_origin = "loadList('".$_SESSION['config']['businessappurl']
                ."index.php?display=true&module=notes&page=notes&identifier="
                .$identifier."&origin=document".$parameters."', 'divList', true);";
    } elseif ($_REQUEST['origin'] == "folder") {
        
        //From folders
        $collId = 'folders';
        $table = $_SESSION['tablename']['fold_folders'];
        $list_origin = "loadList('".$_SESSION['config']['businessappurl']
                    ."index.php?display=true&module=notes&page=notes&identifier="
                    .$identifier."&origin=folder".$parameters."', 'divList', true);";
    }
}

//Path to actual script
$path_to_script = $_SESSION['config']['businessappurl']
            ."index.php?display=true&module=notes&page=notes_ajax_content&identifier="
            .$identifier."&origin=".$origin.$parameters;

require 'modules/templates/class/templates_controler.php';
$templatesControler = new templates_controler();
$templates = array();
if ($destination <> '') {
    $templates = $templatesControler->getAllTemplatesForProcess($destination);
} else {
    $templates = $templatesControler->getAllTemplatesForSelect();
}            
switch ($mode) {
    case 'add':
        if (empty($identifier)) {
            $error = $request->wash_html(_IDENTIFIER.' '._IS_EMPTY.'!','NONE');
            $status = 1;
        } else {
            $entitiesList = array();
            $entitiesList = $ent->getAllEntities();
            $content .= '<div class="block">';
            $content .= '<form name="formNotes" id="formNotes" method="post" action="#">';
            $content .= '<input type="hidden" value="'.$identifier.'" name="identifier" id="identifier">';
            $content .= '<h2>'._ADD_NOTE.'</h2>';
            $content .= '<label for="templateNotes">' . _NOTE_TEMPLATE .' : </label>';
            $content .= '<select name="templateNotes" id="templateNotes" style="width:250px" '
                . 'onchange="addTemplateToNote($(\'templateNotes\').value, \''
                            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
                            . '&module=templates&page=templates_ajax_content_for_notes\');">';
            $content .= '<option value="">' . _SELECT_NOTE_TEMPLATE . '</option>';
                for ($i=0;$i<count($templates);$i++) {
                    if ($templates[$i]['TYPE'] == 'TXT' && ($templates[$i]['TARGET'] == 'notes' || $templates[$i]['TARGET'] == '')) {
                        $content .= '<option value="';
                            $content .= $templates[$i]['ID'];
                            $content .= '">';
                            $content .= $templates[$i]['LABEL'];
                        }
                    $content .= '</option>';
                }
            $content .= '</select><br />';
            $content .= '<textarea style="width:500px" cols="70" rows="10"  name="notes"  id="notes" ></textarea>';
            $content .= '<h3 class="sstit">'._THIS_NOTE_IS_VISIBLE_BY.'</h3>';
            $content .= '<table align="center" width="100%" id="template_entities">';
            $content .= '<tr><td width="20%" align="center">';
            $content .= '<select name="entitieslist[]" id="entitieslist" size="7" style="width: 206px" ';
            $content .= 'ondblclick=\'moveclick($(entitieslist), $(entities_chosen));\' multiple="multiple">';
            for ($i=0;$i<count($entitiesList);$i++) {
                $state_entity = false;
                if ($state_entity == false) {
                    $content .= '<option value="'
                        .$entitiesList[$i]->entity_id.'" alt="'
                        .$entitiesList[$i]->short_label.'" title="'
                        .$entitiesList[$i]->short_label.'">'
                        .$entitiesList[$i]->short_label.'</option>';
                }
            }
            $content .= '</select><br/> </td>';
            $content .= '<td width="20%" align="center">';
            $content .= '<input type="button" class="button" value="'._ADD.' &gt;&gt;" onclick=\'Move($(entitieslist), $(entities_chosen));\' />';
            $content .= '<br /><br />';
            $content .= '<input type="button" class="button" value="&lt;&lt; '._REMOVE.'" onclick=\'Move($(entities_chosen), $(entitieslist));\' />';
            $content .= '</td>';
            $content .= '<td width="30%" align="center">';
            $content .= '<select name="entities_chosen[]" id="entities_chosen" size="7" style="width: 206px" ';
            $content .= 'ondblclick=\'moveclick($(entities_chosen), $(entitieslist));\' multiple="multiple">';
            for ($i=0;$i<count($entitiesList);$i++) {
               $state_entity = false;
               if ($state_entity == true) {
                    $content .= '<option value="'
                        .$entitiesList[$i]->entity_id.'" alt="'
                        .$entitiesList[$i]->short_label.'" title="'
                        .$entitiesList[$i]->short_label.'" selected="selected">'
                        .$entitiesList[$i]->short_label.'</option>';
               }
            }
            $content .= '</select></td>';
            $content .= '</tr></table>';
            // Buttons
            $content .='<hr />';
            $content .='<div align="center">';
            $content .=' <input type="button" name="valid" value="&nbsp;'._ADD_NOTE
                        .'&nbsp;" id="valid" class="button" onclick="'
                        .'selectall($(\'entities_chosen\'));validNotesForm(\''
                        .$path_to_script.'&mode=added\', \'formNotes\');" />&nbsp;';
            $content .='<input type="button" name="cancel" id="cancel" class="button" value="'
                        ._CANCEL.'" onclick="destroyModal(\'form_notes\');"/>';
            $content .='</div">';
            $content .= '</form>';
            $content .= '</div>';
        }
    break;
    case 'added':
        if (strlen(trim($_REQUEST['notes'])) > 0) {
            //Identifier?
            if (empty($identifier)) {
                $error = $request->wash_html(_IDENTIFIER.' '._IS_EMPTY.'!','NONE');
                $status = 1;
            } else {
                
                //Add notes
                $notes_protected1 = str_replace(";", ".", $_REQUEST['notes']);
                $notes_protected2 = str_replace("--", "-", $notes_protected1);
                $notes = $request->protect_string_db($notes_protected2);
                $date = $request->current_datetime();
                $userId = $request->protect_string_db($_SESSION['user']['UserId']);
                $collId = $request->protect_string_db($collId);
                $table = $request->protect_string_db($table);

                $request->query(
                    "INSERT INTO " . NOTES_TABLE . "(identifier, note_text, date_note, "
                    . "user_id, coll_id, tablename) VALUES ("
                    . $identifier . ", '" . $notes. "', " . $date . ", '"
                    . $userId . "', '".$collId . "', '".$table . "')"
                );
                
                //Last insert ID from sequence
                $id = $request->last_insert_id('notes_seq');
                
                //Entities selected
                if (!empty($id) && isset($_REQUEST['entities_chosen']) && !empty($_REQUEST['entities_chosen']))
                {
                    for ($i=0; $i<count($_REQUEST['entities_chosen']); $i++) 
                    {  
                        $request->query(
                            "INSERT INTO " . NOTE_ENTITIES_TABLE . "(note_id, item_id) VALUES"
                            . " (".$id . ", '"
                            . $request->protect_string_db($_REQUEST['entities_chosen'][$i])."')"
                        );
                    }
                }
                
                //History
                if ($_SESSION['history']['noteadd']) {
                    $hist = new history();

                if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "folder") {
                        if (!empty($id) && isset($_REQUEST['entities_chosen']) && !empty($_REQUEST['entities_chosen'])){

                            $hist->add(
                                    $table, $identifier, "UP", 'folderup', _ADDITION_NOTE_PRIVATE . _ON_FOLDER_NUM
                                    . $identifier . ' (' . $id . ')',
                                    $_SESSION['config']['databasetype'], 'notes'
                                );
                        }else{
                            $hist->add(
                                $table, $identifier, "UP", 'folderup', _ADDITION_NOTE . _ON_FOLDER_NUM
                                . $identifier . ' (' . $id . ') : "' . $request->cut_string($notes, 254) .'"',
                                $_SESSION['config']['databasetype'], 'notes'
                            );
                        }
                    } else if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "document") {
                        if (!empty($id) && isset($_REQUEST['entities_chosen']) && !empty($_REQUEST['entities_chosen'])){

                            $hist->add(
                                    $table, $identifier, "UP", 'folderup', _ADDITION_NOTE_PRIVATE . _ON_DOC_NUM
                                    . $identifier . ' (' . $id . ')',
                                    $_SESSION['config']['databasetype'], 'notes'
                                );
                        }else{
                            $hist->add(
                                $view, $identifier, "UP", 'resup',  _ADDITION_NOTE . _ON_DOC_NUM
                                . $identifier . ' (' . $id . ') : "' . $request->cut_string($notes, 254) .'"',
                                $_SESSION['config']['databasetype'], 'notes'
                            );
                        }
                    }

                    $hist->add(
                        NOTES_TABLE, $id, "ADD", 'noteadd', _NOTES_ADDED . ' (' . $id . ')',
                        $_SESSION['config']['databasetype'], 'notes'
                    );
                }
                
                //Reload and show message
                $js =  $list_origin."window.top.$('main_info').innerHTML = '"._NOTES_ADDED."';";

                //Count notes
                $nb_notes = $notesTools->countUserNotes($identifier, $collId); 
                $js .= "window.parent.top.$('nb_note').innerHTML='".$nb_notes."';";
            }
        } else {
            $error = $request->wash_html(_NOTES.' '._IS_EMPTY.'!','NONE');
            $status = 1;
        }
    break;
    case 'up':
        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            
            $id = $_REQUEST['id'];
            //Check if ID exists
            $where = "";
            if (!empty($collId)) {
                $where .= " and coll_id = '" . $collId . "'";
            }
            if (!empty($table)) {
                $where .= " and tablename = '" . $table . "'";
            }
            $request->query(
                "select n.identifier, n.date_note, n.user_id, n.note_text, u.lastname, "
                . "u.firstname from " . NOTES_TABLE . " n inner join ". USERS_TABLE
                . " u on n.user_id  = u.user_id where n.id = " . $id . " " . $where
                // . " and user_id = '".$request->protect_string_db($_SESSION['user']['UserId'])."' " . $where
            );
            // $request->show();
            
            if($request->nb_result() > 0) {
                
                $line = $request->fetch_object();
                
                $user = $request->show_string($line->lastname . " " . $line->firstname);
                $notes = $request->show_string($line->note_text);
                $userId = $line->user_id;
                $date = $line->date_note;
                $identifier = $line->identifier;
    
                $notesEntities = array();
                $entitiesList = array();
                $notesEntities = $notesTools->getNotesEntities($id);
                $entitiesList = $ent->getAllEntities();
                //Form
                $content .= '<div class="block">';
                $content .= '<form name="formNotes" id="formNotes" method="post" action="#">';
                $content .= '<input type="hidden" value="'.$identifier.'" name="identifier" id="identifier">';
                $content .= '<input type="hidden" value="'.$id.'" name="id" id="id">';
                $content .= '<h2 class="sstit">'._NOTES . " " . _OF . " " . $user . " (" . $date . ")".'</h2>';
                $content .= '<label for="templateNotes">' . _NOTE_TEMPLATE .' : </label>';
                $content .= '<select name="templateNotes" id="templateNotes" style="width:250px" '
                             . 'onchange="addTemplateToNote($(\'templateNotes\').value, \''
                             . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
                             . '&module=templates&page=templates_ajax_content_for_notes\');">';
                $content .= '<option value="">' . _SELECT_NOTE_TEMPLATE . '</option>';
                for ($i=0;$i<count($templates);$i++) {
                    if ($templates[$i]['TYPE'] == 'TXT' && ($templates[$i]['TARGET'] == 'notes' || $templates[$i]['TARGET'] == '')) {
                        $content .= '<option value="';
                            $content .= $templates[$i]['ID'];
                            $content .= '">';
                            $content .= $templates[$i]['LABEL'];
                        }
                    $content .= '</option>';
                }
                $content .= '</select><br />';
                $content .= '<textarea style="width:500px" cols="70" rows="10"  name="notes"  id="notes">'.$notes.'</textarea>';
                $content .= '<h3 class="sstit">'._THIS_NOTE_IS_VISIBLE_BY.'</h3>';
                $content .= '<table align="center" width="100%" id="template_entities">';
                $content .= '<tr><td width="20%" align="center">';
                $content .= '<select name="entitieslist[]" id="entitieslist" size="7" style="width: 206px" ';
                $content .= 'ondblclick=\'moveclick($(entitieslist), $(entities_chosen));\' multiple="multiple">';
                for ($i=0;$i<count($entitiesList);$i++) {
                    if (!in_array($entitiesList[$i], $notesEntities)) {
                        $content .= '<option value="'
                            .$entitiesList[$i]->entity_id.'" alt="'
                            .$entitiesList[$i]->short_label.'" title="'
                            .$entitiesList[$i]->short_label.'">'
                            .$entitiesList[$i]->short_label.'</option>';
                    }
                }
                $content .= '</select><br/> </td>';
                $content .= '<td width="20%" align="center">';
                $content .= '<input type="button" class="button" value="'._ADD.' &gt;&gt;" onclick=\'Move($(entitieslist), $(entities_chosen));\' />';
                $content .= '<br /><br />';
                $content .= '<input type="button" class="button" value="&lt;&lt; '._REMOVE.'" onclick=\'Move($(entities_chosen), $(entitieslist));\' />';
                $content .= '</td>';
                $content .= '<td width="30%" align="center">';
                $content .= '<select name="entities_chosen[]" id="entities_chosen" size="7" style="width: 206px" ';
                $content .= 'ondblclick=\'moveclick($(entities_chosen), $(entitieslist));\' multiple="multiple">';
                for ($i=0;$i<count($notesEntities);$i++) {
                    $content .= '<option value="'
                        .$notesEntities[$i]->entity_id.'" alt="'
                        .$notesEntities[$i]->short_label.'" title="'
                        .$notesEntities[$i]->short_label.'" selected="selected">'
                        .$notesEntities[$i]->short_label.'</option>';
                }
                $content .= '</select></td>';
                $content .= '</tr></table>';
                // Buttons
                $content .='<hr />';
                $content .='<div align="center">';
                $content .=' <input type="button" name="valid" value="&nbsp;'._MODIFY
                         .'&nbsp;" id="valid" class="button" onclick="'
                         .'selectall($(\'entities_chosen\'));validNotesForm(\''
                         .$path_to_script.'&mode=updated\', \'formNotes\');" />&nbsp;';
                $content .=' <input type="button" name="valid" value="&nbsp;'._DELETE
                         .'&nbsp;" id="valid" class="button" onclick="if(confirm(\''._REALLY_DELETE.': '
                         .$request->cut_string(str_replace(array("'", "\n"),array("'", " "), $notes), 20).' ?\')) validNotesForm(\''
                         .$path_to_script.'&mode=del\', \'formNotes\');" />&nbsp;';
                $content .='<input type="button" name="cancel" id="cancel" class="button" value="'
                    ._CANCEL.'" onclick="destroyModal(\'form_notes\');"/>';
                $content .='</div">';
                $content .= '</form>';
                $content .= '</div>';
            } else {
                $error = $request->wash_html($id.': '._NOTE_DONT_EXIST.'!','NONE');
                $status = 1;
            }
        
        } else {
            $error = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
            $status = 1;
        }
    break;
    case 'updated':
        if (strlen(trim($_REQUEST['notes'])) > 0) {
            //ID?
            if (empty($_REQUEST['id'])) {
                $error = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
                $status = 1;
            } else {
                $id = $_REQUEST['id'];
                //Identifier?
                if (empty($identifier)) {
                    $error = $request->wash_html(_IDENTIFIER.' '._IS_EMPTY.'!','NONE');
                    $status = 1;
                } else {
                    
                    //Update note
                    $notes_protected1 = str_replace(";", ".", $_REQUEST['notes']);
                    $notes_protected2 = str_replace("--", "-", $notes_protected1);
                    $notes = $request->protect_string_db($notes_protected2);
                    // $notes = $request->protect_string_db($_REQUEST['notes']);
                    $date = $request->current_datetime();

                    $request->query(
                        "UPDATE ".NOTES_TABLE." SET note_text = '". $notes
                        . "', date_note = " . $date . " WHERE id = "
                        . $id
                    );
                    
                    //Entities selected
                    $request->query(
                            "DELETE FROM " . NOTE_ENTITIES_TABLE . " where note_id = " . $id
                        );
                    if (isset($_REQUEST['entities_chosen']) && !empty($_REQUEST['entities_chosen'])) {
                    
                        for ($i=0; $i<count($_REQUEST['entities_chosen']); $i++) 
                        {  
                            $request->query(
                                "INSERT INTO " . NOTE_ENTITIES_TABLE . "(note_id, item_id) VALUES"
                                . " (".$id . ", '"
                                . $request->protect_string_db($_REQUEST['entities_chosen'][$i])."')"
                            );
                        }
                    }
                    
                    //History
                    if ($_SESSION['history']['noteup']) {
                        $hist = new history();
                        if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "folder") {
                            $hist->add(
                                $table, $identifier, "UP", 'folderup', _NOTE_UPDATED . _ON_FOLDER_NUM
                                . $identifier . ' (' . $id . ') : "' . $request->cut_string($notes, 254) .'"',
                                $_SESSION['config']['databasetype'], 'notes'
                            );
                        } else if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "document") {
                            $hist->add(
                                $view, $identifier, "UP", 'resup',  _NOTE_UPDATED . _ON_DOC_NUM
                                . $identifier . ' (' . $id . ') ',
                                $_SESSION['config']['databasetype'], 'notes'
                            );
                        }

                        $hist->add(
                            NOTES_TABLE, $id, "UP", 'noteup', _NOTE_UPDATED . ' (' . $id . ')',
                            $_SESSION['config']['databasetype'], 'notes'
                        );
                    }
                    
                    //Reload and show message
                    $js =  $list_origin."window.top.$('main_info').innerHTML = '"._NOTE_UPDATED."';"; 
                }
            }
        } else {
            $error = $request->wash_html(_NOTES.' '._IS_EMPTY.'!','NONE');
            $status = 1;
        }
    break;
    case 'del':
        //ID?
        if (empty($_REQUEST['id'])) {
            $error = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
            $status = 1;
        } else {
            $id = $_REQUEST['id'];
            
            $request->query("delete from " . NOTE_ENTITIES_TABLE . " where note_id = " . $id);
            $request->query("delete from " . NOTES_TABLE . " where id = " . $id);

            if ($_SESSION['history']['notedel']) {
                $hist = new history();
                $hist->add(
                    NOTES_TABLE, $id, "DEL", 'notedel', _NOTES_DELETED . ' (' . $id . ')',
                    $_SESSION['config']['databasetype'], 'notes'
                );
                if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "folder") {
                    $hist->add(
                        $table, $identifier, "UP", 'folderup', _NOTES_DELETED . _ON_FOLDER_NUM
                        . $identifier . ' (' . $id . ')',
                        $_SESSION['config']['databasetype'], 'notes'
                    );
                } else if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "document") {
                    $hist->add(
                        $view, $identifier, "UP", 'resup',  _NOTES_DELETED . _ON_DOC_NUM
                        . $identifier . ' (' . $id . ')',
                        $_SESSION['config']['databasetype'], 'notes'
                    );
                }
            }
            
            //Reload and show message
            $js =  $list_origin."window.top.$('main_info').innerHTML = '"._NOTES_DELETED."';";
            
            //Count notes
            $nb_notes = $notesTools->countUserNotes($identifier, $collId); 
            $js .= "window.parent.top.$('nb_note').innerHTML='".$nb_notes."';";
        }
    break;
}

echo "{status : " . $status . ", content : '" . addslashes(_parse($content)) . "', error : '" . addslashes($error) . "', exec_js : '".addslashes($js)."'}";
exit ();
?>

