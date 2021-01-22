<?php
/*****************************************************
 *get_servizi_sociali                                *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// controllo che sia impostato un modulo
if(empty($_POST['user']) or  empty($_POST['ticket']) or  empty($_POST['message'])){
	echo json_encode($output);
	die();
}


// sanitize
$ticket = (int) $_POST['ticket'];
$user = (int) $_POST['user'];
$message = trim(strip_tags($_POST['message']));
$message = $db->make_data_safe($message);

$date = date("Y-m-d H:i:s");
$timestamp = date("d M Y H:i");

$myAvatar = $db->get1value("avatar", LOGIN_TABLE, "WHERE id = '".$user."'");
if(empty($myAvatar)) $myAvatar = "generic-user.png";

$fields = array("ticket", "date", "from", "message");
$values = array($ticket, $date, $user, $message);

if( $db->insert(DBTABLE_TICKETS_REPLIES, $values, $fields) ){
	
	$output['message'] = "			
		<div class=\"direct-chat-msg\">
			<div class=\"direct-chat-info clearfix\">
				<span class=\"direct-chat-name pull-left\">You</span>
				<span class=\"direct-chat-timestamp pull-right\">".$timestamp."</span>
			</div>
			<!-- /.direct-chat-info -->
			<img class=\"direct-chat-img\" src=\"avatars/".$myAvatar."\" alt=\"message user image\">
			<!-- /.direct-chat-img -->
			<div class=\"direct-chat-text\">
				".$message."
			</div>
			<!-- /.direct-chat-text -->
		</div>
	"; 
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	
}else{
	$output['error'] = "Error during insert reply in DB";
	$output['msg'] = "ERROR: ".$db->getError("msg")."<br>".$db->getQry();
	$output['message'] = "";
	
}

// output

echo json_encode($output);

	
?>
