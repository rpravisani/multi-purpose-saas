<?php
$function 	= "updateTicketChat";

$qry = "
SELECT ticket, COUNT(id) AS 'total', SUM(`read`) AS 'areread' 
FROM ".DBTABLE_TICKETS_REPLIES." WHERE `from` != '".$_SESSION['login_id']."' 
GROUP BY ticket
";

$ticket_replies = $db->fetch_array($qry);


if(!empty($ticket_replies)){
	foreach($ticket_replies as $ticket_reply){
		$diff = $ticket_reply['total'] - $ticket_reply['areread'];
		if($diff > 0){
			$result[$ticket_reply['ticket']] = "<span class=\"get-chat label bg-green blink \">".$diff."</span>";			
		}
	}
}
?>