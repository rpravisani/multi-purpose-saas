<?php

/*** CHECK FOR TOKEN (OUTSIDE STANDARD PAGE SYSTEM) ***/

function customToken(){
	
	global $db;
	
	$_token = $db->make_data_safe($_GET['t']);
	
	// check if token exists in DB
	$_token_param = $db->get1row(DBTABLE_TOKENS, "WHERE token = '".$_token."'");
	
	if($_token_param){
		
		$first_acces_date = $_token_param['data'];		

		if($first_acces_date == '0000-00-00'){
		
			// if not set, get currente date
			$first_acces_date = date( "Y-m-d");
			
			// update table
			$db->update(DBTABLE_TOKENS, array("data" => $first_acces_date ), "WHERE id = '".$_token_param['id']."'");
		
		} // end first access date empty 

		// calculate time limit (date first access + days after which it's not valid anymore
		$ttl = new DateTime($first_acces_date);
		$ttl->modify("+".$_token_param['durata']." days");
				
		$today = new DateTime();
		$interval = $today->diff($ttl);
		$diff = (int) $interval->format('%R%a');		
		
		if($diff < 0){
			//scaduto - compilo messaggio e restituisco false
			$_SESSION['error'] = "Token scaduto"; // TODO: translate
			return false;
		}else{
			// controllo che l'url custom corrisonda a questo
			if($_SERVER['PHP_SELF'] == $_token_param['custom_url'] ){
				// ok recupero record e lo restituisco
				return $_token_param['record'];
			}else{
				$_SESSION['error'] = "Token non valdo"; // TODO: translate
				return false;
				
			}
		} // end if diff
		
		
	}else{
		
		// token not found
		$_SESSION['error'] = "Token non trovato"; // TODO: translate
		return false;
		
		
	}
	
}

?>