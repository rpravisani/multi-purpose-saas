<?php
/*****************************************************
 * search-translation                                *
 * Search for translated string in DB                *
 * IN: (POST) searchfor (string to search),          *
 *            language (in xx format)                *
 * OUT: section (string) and id of tranlation table  *
 *      or error in case non found                   *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';
$output['errorcode'] = "";

// set specific translations for this script
$_t->setSection("translate");

$db_host = $db->make_data_safe($_POST['host']);
$db_user = $db->make_data_safe($_POST['user']);
$db_pwd = $db->make_data_safe($_POST['pwd']);

$table = (empty($_POST['table'])) ? false : $db->make_data_safe($_POST['table']);

$db_list = "<option></option>";

$other_db = new cc_dbconnect("", $db_host, $db_user, $db_pwd);

if(!$other_db->checkConnection()){
	$output['result'] = false;
	$output['error'] = $other_db->getError("num");
	$output['msg'] = $other_db->getError("msg");
	$output['dblist'] = "<option></option>";
	$output['dbg'] = "";
	echo json_encode($output);
	die();
}


$qry = "SHOW DATABASES";
$dbs = $other_db->fetch_array($qry, MYSQLI_NUM);
foreach($dbs as $d){
	if($d[0] == DB_NAME) continue;
	if($other_db->changeDB($d[0])){
		// let's see if there's a certain table
		$tablist = $other_db->getTablenames();
		if($tablist){
			if($table){
				if(in_array($table, $tablist)){
					$db_list .= "<option value='".$d[0]."'>".$d[0]."</option>";
				}
				
			}else{
				$db_list .= "<option value='".$d[0]."'>".$d[0]."</option>";
				
			}
			
		}
	}
}

if(empty($db_list)){
	$output['result'] = false;
	$output['error'] = "";
	$output['msg'] = "";
	$output['dblist'] = "<option>No DB with translation found!</option>";
	$output['dbg'] = "";
}else{
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['dblist'] = $db_list;
	$output['dbg'] = "";
	
}


echo json_encode($output);

	
?>
