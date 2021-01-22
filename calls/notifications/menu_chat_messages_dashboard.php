<?php
$id 		= "menu-item-1";
$class 		= "label pull-right bg-red";
$tag 		= "small";


// check for new chat messages and print label next to Dashboard menu item if found
$tickets = $db->col_value("id", DBTABLE_TICKETS, "WHERE state != 'concluded' AND user = '".$_SESSION['login_id']."'");
if($tickets){
	$tickets_flat = implode(",", $tickets);
	$num_replies = $db->fetch_array_row("SELECT COUNT(id) AS 'totale', SUM(`read`) AS 'letti' 
	FROM ".DBTABLE_TICKETS_REPLIES." WHERE ticket IN (".$tickets_flat.") AND `from` != '".$_SESSION['login_id']."'");
	if(!empty($num_replies)){
		$result = (int) $num_replies['totale'] - $num_replies['letti'];
		if(empty($result)) $result = false;
	}
}
?>