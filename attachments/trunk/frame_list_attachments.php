<?php
$core = new core_tools();
//here we loading the lang vars
$core->load_lang();
$core->test_service('manage_attachments', 'attachments');

$func = new functions();

if (empty($_SESSION['collection_id_choice'])) {
	$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
}
$viewOnly = false;
if (isset($_REQUEST['view_only'])) {
	$viewOnly = true;
}
require_once "core/class/class_request.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_list_show.php";
$func = new functions();

$select[RES_ATTACHMENTS_TABLE] = array();
array_push(
    $select[RES_ATTACHMENTS_TABLE], "res_id", "creation_date", "title", "format"
);

$where = " res_id_master = " . $_SESSION['doc_id'] . " and coll_id ='"
       . $_SESSION['collection_id_choice'] . "' and status <> 'DEL' ";
$request = new request;
$attachArr = $request->select(
    $select, $where, "", $_SESSION['config']['databasetype'], "500"
);
//$request->show();
$indAtt1d = '';
for ($indAtt1d = 0; $indAtt1d < count($attachArr); $indAtt1d ++) {
	$modifyValue = false;
	for ($indAtt2 = 0; $indAtt2 < count($attachArr[$indAtt1d]); $indAtt2 ++) {
		foreach (array_keys($attachArr[$indAtt1d][$indAtt2]) as $value) {
			if ($attachArr[$indAtt1d][$indAtt2][$value] == "res_id") {
				$attachArr[$indAtt1d][$indAtt2]["res_id"] = $attachArr[$indAtt1d][$indAtt2]['value'];
				$attachArr[$indAtt1d][$indAtt2]["label"] = _ID;
				$attachArr[$indAtt1d][$indAtt2]["size"] = "18";
				$attachArr[$indAtt1d][$indAtt2]["label_align"] = "left";
				$attachArr[$indAtt1d][$indAtt2]["align"] = "left";
				$attachArr[$indAtt1d][$indAtt2]["valign"] = "bottom";
				$attachArr[$indAtt1d][$indAtt2]["show"] = false;
				$indAtt1d = $attachArr[$indAtt1d][$indAtt2]['value'];
			}
			if ($attachArr[$indAtt1d][$indAtt2][$value] == "title") {
				$attachArr[$indAtt1d][$indAtt2]["title"]=$attachArr[$indAtt1d][$indAtt2]['value'];
				$attachArr[$indAtt1d][$indAtt2]["label"]= _TITLE;
				$attachArr[$indAtt1d][$indAtt2]["size"]="30";
				$attachArr[$indAtt1d][$indAtt2]["label_align"]="left";
				$attachArr[$indAtt1d][$indAtt2]["align"]="left";
				$attachArr[$indAtt1d][$indAtt2]["valign"]="bottom";
				$attachArr[$indAtt1d][$indAtt2]["show"]=true;
			}
			if($attachArr[$indAtt1d][$indAtt2][$value]=="creation_date")
			{
				$attachArr[$indAtt1d][$indAtt2]['value']=$request->format_date_db($attachArr[$indAtt1d][$indAtt2]['value'], true);
				$attachArr[$indAtt1d][$indAtt2]["creation_date"]=$attachArr[$indAtt1d][$indAtt2]['value'];
				$attachArr[$indAtt1d][$indAtt2]["label"]=_DATE;
				$attachArr[$indAtt1d][$indAtt2]["size"]="30";
				$attachArr[$indAtt1d][$indAtt2]["label_align"]="left";
				$attachArr[$indAtt1d][$indAtt2]["align"]="left";
				$attachArr[$indAtt1d][$indAtt2]["valign"]="bottom";
				$attachArr[$indAtt1d][$indAtt2]["show"]=true;
			}
			if($attachArr[$indAtt1d][$indAtt2][$value]=="format")
			{
				$attachArr[$indAtt1d][$indAtt2]['value']=$request->show_string($attachArr[$indAtt1d][$indAtt2]['value']);
				$attachArr[$indAtt1d][$indAtt2]["format"]=$attachArr[$indAtt1d][$indAtt2]['value'];
				$attachArr[$indAtt1d][$indAtt2]["label"]=_FORMAT;
				$attachArr[$indAtt1d][$indAtt2]["size"]="5";
				$attachArr[$indAtt1d][$indAtt2]["label_align"]="left";
				$attachArr[$indAtt1d][$indAtt2]["align"]="left";
				$attachArr[$indAtt1d][$indAtt2]["valign"]="bottom";
				$attachArr[$indAtt1d][$indAtt2]["show"]=false;

				if($attachArr[$indAtt1d][$indAtt2]['value'] == "maarch")
				{
					$modifyValue = true;
				}

			}
		}
	}
	if(!$viewOnly)
	{
		$tmp = array('column' => 'modify_item', 'value'=>$modifyValue, 'label' =>  _MODIFY, 'size' => '22', 'label_align' => "right", 'align'=> "center", 'valign' => "bottom", 'show' => false);
		array_push($attachArr[$indAtt1d], $tmp);

		$tmp2 = array('column' => 'delete_item','value'=>true, 'label' =>  _DELETE, 'size' => '22', 'label_align' => "right", 'align'=> "center", 'valign' => "bottom", 'show' => false);
		array_push($attachArr[$indAtt1d], $tmp2);
	}
}

//$request->show_array($attachArr);
//here we loading the html
$core->load_html();
//here we building the header
$core->load_header('', true, false);
$mode = "small";
if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'normal')
{
	$mode = 'normal';
}

?>
<body <?php if($mode == 'small'){ echo 'id="iframe"';}?>>
 <?php
$list_attach = new list_show();

$used_css = 'listingsmall';
if($mode == 'normal')
{
	$used_css = 'listing spec';
}
	$list_attach->list_simple($attachArr, count($attachArr), '','res_id','res_id', true, $_SESSION['config']['businessappurl']."index.php?display=true&module=attachments&page=view_attachment",$used_css,$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&mode=up",450,  500, $page_del = $_SESSION['config']['businessappurl']."index.php?display=true&module=attachments&page=del_attachment");
$core->load_js();
?></body>
</html>
