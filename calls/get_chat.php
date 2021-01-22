<?php
/*****************************************************
 *get_servizi_sociali                                *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// controllo che sia impostato un modulo
if(empty($_POST['user']) or  empty($_POST['ticket'])){
	echo json_encode($output);
	die();
}


// sanitize
$ticket = (int) $_POST['ticket'];
$user = (int) $_POST['user'];

// vars
$reply_html = "";
$chat_head = "<small><em>Nessun messaggio, usa la casella qua sotto per inviare un sollecito o una ulteriore delucidazione</em></small>";

$ticket_details = $db->get1row(DBTABLE_TICKETS, "WHERE id = '".$ticket."'");
$tsObject = new DateTime($ticket_details['date']);
$ticket_date = $tsObject->format('d/m/Y');

$replies = $db->fetch_array("SELECT t.from, t.message, t.date, t.ts AS updated, t.read, CONCAT(u.name, ' ', u.surname) AS user_name, u.avatar, u.subscription_type 
FROM `".DBTABLE_TICKETS_REPLIES."` AS t, ".LOGIN_TABLE." AS u WHERE t.from = u.id AND t.ticket = '".$ticket."' ORDER BY t.date DESC");

if($replies){
	// count new messages
	$num_new_messages = $db->count_rows( DBTABLE_TICKETS_REPLIES, "WHERE `ticket` = '".$ticket."' AND `read` = '0' AND `from` != '".$_SESSION['login_id']."'" );
	// update read flag of the messages send to me
	$db->update(DBTABLE_TICKETS_REPLIES, array( "read" => '1'), "WHERE `ticket` = '".$ticket."' AND `read` = '0' AND `from` != '".$_SESSION['login_id']."'");

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
			// you - left side
			$reply_chat_side = ""; // default left side
			$reply_name_side = "left";
			$reply_timestamp_side = "right";
			$reply_user_name = "Tu";
			$read_date = "";
		}else{
			// other user - right side
			$reply_chat_side = "right";
			$reply_name_side = "right";
			$reply_timestamp_side = "left";
			$reply_user_name = $reply['user_name'];
			$read_date = ($reply['read'] == '0') ? "" : sprintf("Letto il %s alle %s", $rutObject->format('d M Y'), $rutObject->format('H:i:s') );
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


ob_start();
?>

		<div class="box box-warning direct-chat direct-chat-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Risposte alla richiesta <?php echo $ticket_details['id']; ?> del <?php echo $ticket_date; ?></h3>
				<div class="box-tools pull-right">
					<span data-toggle="tooltip" title="<?php echo $num_new_messages; ?> New Messages" class="badge bg-yellow"><?php echo $num_new_messages; ?></span>
					<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					<button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- Conversations are loaded here -->
				<div class="direct-chat-messages">
					<?php if(empty($reply_html)){ ?>
					<div id="chat-instuctionschat-instuctions" class="text-muted text-center">
						<?php echo $chat_head; ?>
					</div>
					<?php }else{ echo $reply_html; } ?>

				</div>
				<!--/.direct-chat-messages-->

				<!-- /.direct-chat-pane -->
			</div>
			<!-- /.box-body -->
			<div class="box-footer">
				<div class="input-group">
					<input data-ticket="<?php echo $ticket; ?>" data-user="<?php echo $user; ?>" id="reply-message" placeholder="Scrivi un messaggio ..." class="form-control" type="text">
					<span class="input-group-btn">
						<button id="send-reply" type="button" class="btn btn-warning btn-flat">Rispondi</button>
					</span>
				</div>
			</div>
			<!-- /.box-footer-->
		</div>


<?php
$output['html'] = ob_get_contents();
ob_end_clean();

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
	

// output

echo json_encode($output);

	
?>
