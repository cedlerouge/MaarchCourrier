<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief  Script called by an ajax object to process the document type change during indexing (index_mlb.php), process limit date calcul and possible services from apps or module
*
* @file change_doctype.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_types.php");

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$type = new types();

if(!isset($_REQUEST['type_id']) || empty($_REQUEST['type_id']))
{
	$_SESSION['error'] = _DOCTYPE.' '._IS_EMPTY;
	echo "{status : 1, error_txt : '".addslashes($_SESSION['error'])."'}";
	exit();
}

if(!isset($_REQUEST['id_action']) || empty($_REQUEST['id_action']))
{
	$_SESSION['error'] = _ACTION_ID.' '._IS_EMPTY;
	echo "{status : 1, error_txt : '".addslashes($_SESSION['error'])."'}";
	exit();
}
$id_action = $_REQUEST['id_action'];

if(isset($_REQUEST['res_id']) && !empty($_REQUEST['res_id']) && isset($_REQUEST['coll_id']) && !empty($_REQUEST['coll_id']))
{
	$res_id = $_REQUEST['res_id'];
	$coll_id = $_REQUEST['coll_id'];
}

// Process limit date calcul
if ($core->service_is_enabled('param_mlb_doctypes')) //Bug fix if delay process is disabled in services
{
	$db->connect();
	$db->query("select process_delay from ".$_SESSION['tablename']['mlb_doctype_ext']." where type_id = ".$_REQUEST['type_id']);
	//$db->show();

	if($db->nb_result() == 0)
	{
		$_SESSION['error'] = _NO_DOCTYPE_IN_DB;
		echo "{status : 2, error_txt : '".addslashes($_SESSION['error'])."'}";
		exit();
	}

	$res = $db->fetch_object();
	$delay = $res->process_delay;
}

$mandatory_indexes = $type->get_mandatory_indexes($_REQUEST['type_id'], 'letterbox_coll');
$indexes = $type->get_indexes($_REQUEST['type_id'], 'letterbox_coll');

$opt_indexes = '';
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"]))
{
	$browser_ie = true;
	$display_value = 'block';
}
elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT) )
{
	$browser_ie = true;
	$display_value = 'block';
}
else
{
	$browser_ie = false;
	$display_value = 'table-row';
}
$opt_indexes  = '';
if(count($indexes) > 0)
{
	if(isset($res_id) && isset($coll_id))
	{
		$sec = new security();
		$table = $sec->retrieve_table_from_coll($coll_id);
		if(!empty($table))
		{
			$fields = 'res_id ';
			foreach(array_keys($indexes) as $key)
			{
				$fields .= ', '.$key;
			}
			$db->query("select ".$fields." from ".$table." where res_id = ".$res_id);
			$values_fields = $db->fetch_object();
		}
	}
	$opt_indexes .= '<table width="100%" align="center" border="0">';
	foreach(array_keys($indexes) as $key)
	{
		//echo $key.' ';
		$mandatory = false;
		if(in_array($key, $mandatory_indexes))
		{
			$mandatory = true;
		}

			$opt_indexes .= '<tr >';

			$opt_indexes.='<td><label for="'.$key.'" class="form_title" >'.$indexes[$key]['label'].'</label></td>';
			$opt_indexes .='<td>&nbsp;</td>';
			$opt_indexes .='<td class="indexing_field">';
			if($indexes[$key]['type_field'] == 'input')
			{
				if($indexes[$key]['type'] == 'date')
				{
					$opt_indexes .='<input name="'.$key.'" type="text" id="'.$key.'" value="';
					if(isset($values_fields->$key))
					{
						$opt_indexes .= $db->format_date_db($values_fields->key, true);
					}
					else if($indexes[$key]['default_value'] <> false)
					{
						$opt_indexes .= $db->format_date_db($indexes[$key]['default_value'], true);
					}
					$opt_indexes .= '" onclick="clear_error(\'frm_error_'.$id_action.'\');showCalender(this);"/>';
				}
				else
				{
					$opt_indexes .= '<input name="'.$key.'" type="text" id="'.$key.'" value="';
					if(isset($values_fields->$key))
					{
						$opt_indexes .= $db->show_string($values_fields->key, true);
					}
					else if($indexes[$key]['default_value'] <> false)
					{
						$opt_indexes .= $db->show_string($indexes[$key]['default_value'], true);
					}
					$opt_indexes .= '" onclick="clear_error(\'frm_error_'.$id_action.'\');" />';
				}
			}
			else
			{
				$opt_indexes .= '<select name="'.$key.'" id="'.$key.'" >';
					$opt_indexes .= '<option value="">'._CHOOSE.'...</option>';
					for($i=0; $i<count($indexes[$key]['values']);$i++)
					{
						$opt_indexes .= '<option value="'.$indexes[$key]['values'][$i]['id'].'"';
						if($indexes[$key]['values'][$i]['id'] == $values_fields->$key)
						{
							$opt_indexes .= 'selected="selected"';
						}
						else if($indexes[$key]['default_value'] <> false && $indexes[$key]['values'][$i]['id'] == $indexes[$key]['default_value'])
						{
							$opt_indexes .= 'selected="selected"';
						}
						$opt_indexes .= ' >'.$indexes[$key]['values'][$i]['label'].'</option>';
					}
				$opt_indexes .= '</select>';
			}
			$opt_indexes .='</td>';
			//$opt_indexes .='<td><span class="red_asterisk" id="'.$key.'_mandatory" style="display:';
			$opt_indexes .='<td><span class="red_asterisk" id="'.$key.'_mandatory" >';
			if($mandatory)
			{
				//$opt_indexes .= 'inline';
				$opt_indexes .= '*</span>&nbsp;</td>';
			}
			else
			{
				//$opt_indexes .= 'none';
				$opt_indexes .= '&nbsp;</span>&nbsp;</td>';
			}
			//$opt_indexes .= ';">*</span>&nbsp;</td>';
		$opt_indexes .= '</tr>';
	}
	$opt_indexes .= '</table>';
}

if(!$core->is_module_loaded('alert_diffusion'))
{
	$_SESSION['error'] = _MODULE.' alert_diffusion '._IS_MISSING;
	echo "{status : 3, error_txt : '".addslashes($_SESSION['error'])."'}";
	exit();
}

$services = '[';
$_SESSION['indexing_services'] = array();
$_SESSION['indexing_type_id'] = $_REQUEST['type_id'];
// Module and apps services
$core->execute_modules_services($_SESSION['modules_services'], 'change_doctype.php', 'include');
$core->execute_app_services($_SESSION['app_services'], 'change_doctype.php', 'include');
for($i=0;$i< count($_SESSION['indexing_services']);$i++)
{
	$services .= "{ script : '".$_SESSION['indexing_services'][$i]['script']."', function_to_execute : '".$_SESSION['indexing_services'][$i]['function_to_execute']."', arguments : '[";
	for($j=0;$j<count($_SESSION['indexing_services'][$i]['arguments']);$j++)
	{
		$services .= " { id : \'".$_SESSION['indexing_services'][$i]['arguments'][$j]['id']."\', value : \'".addslashes($_SESSION['indexing_services'][$i]['arguments'][$j]['value'])."\' }, ";
	}
	$services = preg_replace('/, $/', '', $services);
	$services .= "]' }, ";
}
$services = preg_replace('/, $/', '', $services);
$services .= ']';
unset($_SESSION['indexing_type_id']);
unset($_SESSION['indexing_services']);
if(isset($delay) && $delay > 0)
{
	require_once('modules'.DIRECTORY_SEPARATOR.'alert_diffusion'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_alert_engine.php');
	$alert_engine = new alert_engine();
	$date = $alert_engine->date_max_treatment($delay, false);
	$process_date = $db->dateformat($date, '-');
	echo "{status : 0, process_date : '".trim($process_date)."', opt_indexes : '".addslashes($opt_indexes)."', services : ".$services."}";
	exit();
}
else
{
	echo "{status : 1, opt_indexes : '".addslashes($opt_indexes)."', services : ".$services."}";
	exit();
}
?>
