<?php
defined('_CCCMS') or die;
/**********************************************
 *** MODEL                                  ***
 *** filename: gestione-milite.php          ***
 *** Inserisci e modifica l'annagrafica     ***
 *** del milite                             ***
 **********************************************/
setlocale(LC_ALL, 'it_IT');
$disabled_qualifiche = false;
$img = "<h4><em>Immagine non disponibile</em></h4>\n";
$new_messages = 0;
$reply_html = "";

// get data if $_record is not empty
if(!empty($_record)){
	// get the data from the table
	$boxtitle = "Update ticket";
	
	// TICKET DATA
	$_data = $db->get1row(DBTABLE_TICKETS, "WHERE id='".$_record."'");
	
	// USER INFO
	$username = $db->get1value("username", LOGIN_TABLE, "WHERE id = '".$_data['user']."'");
	
	// DATES
	$ticket_ts = strtotime($_data['date']);
	$update_ts = strtotime($_data['ts']);
	
	// PAGE URL
	$pageurl_parts = parseUrl($_data['url']);
	$pageurl = $pageurl_parts['script'].$pageurl_parts['query'].$pageurl_parts['section'];

	// SCREENSHOT
	if(!empty($_data['screenshot']) and file_exists(FILEROOT."screenshots/".$_data['screenshot']) ){
		$img_url = "required/img.php?file=../screenshots/".$_data['screenshot']."&w=960&h=520&c=1";
		
		$img = "
                    	<a href=\"screenshots/".$_data['screenshot']."\" class=\"fancybox\">\n
                    		<img src=\"".$img_url."\" width=\"100%\">\n
						</a>\n
		";
	}
	
	// REPLIES
	$replies = $db->fetch_array("SELECT t.from, t.message, t.date, t.ts AS updated, CONCAT(u.name, ' ', u.surname) AS user_name, u.avatar, u.subscription_type FROM `".DBTABLE_TICKETS_REPLIES."` AS t, ".LOGIN_TABLE." AS u WHERE t.from = u.id AND t.ticket = '".$_record."' ORDER BY t.ts DESC");
	if($replies){
		// count new messages
		$num_new_messages = $db->count_rows( DBTABLE_TICKETS_REPLIES, "WHERE `ticket` = '".$_record."' AND `read` = '0' AND `from` != '".$_SESSION['login_id']."'" );
		// update read flag of the messages send to me
		$db->update(DBTABLE_TICKETS_REPLIES, array( "read" => '1'), "WHERE `ticket` = '".$_record."' AND `read` = '0' AND `from` != '".$_SESSION['login_id']."'");
		
		//loop to create html
		foreach($replies as $reply){
			$rtsObject = new DateTime($reply['date']);
			$reply_timestamp = $rtsObject->format('d M Y H:i');
			$rutObject = new DateTime($reply['ts']);
			$reply_read = $rutObject->format('d M Y H:i');
			
			if(empty($reply['avatar'])){
				switch($reply['subscription_type']){ // TODO altri avatar
					case '1':
					case '2':
					case '3':
					case '4':
					default:
						$reply['avatar'] = "generic-user.png";
						break;						
				} // end switch
			} // end if no avatar
			
			if($reply['from'] == $_SESSION['login_id']){
				// (super)admin / you - left side
				$reply_chat_side = ""; // default left side
				$reply_name_side = "left";
				$reply_timestamp_side = "right";
				$reply_user_name = "You";
				$read_date = "";
			}else{
				// customer - right side
				$reply_chat_side = "right";
				$reply_name_side = "right";
				$reply_timestamp_side = "left";
				$reply_user_name = $reply['user_name'];
				$read_date = sprintf("Read on %s at %s", $rutObject->format('D d M Y'), $rutObject->format('H:i:s') );
				
			}
			
			
			$reply_html .= "			
				<div class=\"direct-chat-msg ".$reply_chat_side."\">
					<div class=\"direct-chat-info clearfix\">
						<span class=\"direct-chat-name pull-".$reply_name_side."\">".$reply_user_name."</span>
						<span class=\"direct-chat-timestamp pull-".$reply_timestamp_side."\">".$reply_timestamp."</span>
					</div>
					<!-- /.direct-chat-info -->
					<img class=\"direct-chat-img\" src=\"avatars/".$reply['avatar']."\" alt=\"message user image\">
					<!-- /.direct-chat-img -->
					<div title=\"".$read_date."\" class=\"direct-chat-text\">
						".$reply['message']."
					</div>
					<!-- /.direct-chat-text -->
				</div>
			"; 
		}
	}
	
	
	
	
}else{
	$ticket_ts = $update_ts = time();
	$pageurl = $username = $_data['pid'] = "--";
}

$datum = date("d/m/Y H:i:s", $ticket_ts);

$datum_update = date("d/m/Y", time());
$time_update = date("H:i:s", time());

$datum_last_update = date("d/m/Y H:i:s", $update_ts);

$states = $db->enum2options("state", DBTABLE_TICKETS, $_data['state']);

$js_assets[] = "plugins/fancybox/source/jquery.fancybox.js";
$css_assets[] = "plugins/fancybox/source/jquery.fancybox.css";




?>