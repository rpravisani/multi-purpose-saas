<?php

/**********************************************************************************************************
  SCRIPT THAT SCANS THE DIRECTORY calls/notifications IN SEARCH OF FILES THAT START WITH "menu_" OR 
  WITH THE FILENAME OF THE PAGE (i.e. dashboard). IF SUCH FILES ARE FOUND THEY WILL BE INCLUDED IN
  THE SCRIPT. 
  
  THE FILENAME RELATED FILES MUST SET A $results VARIABLE WITH SOME SORT OF DATA AND A 
  $function VARIABLE WITH THE NAME OF A JAVASCRIPT FUNCTION TO BE LAUNCHED IF $result IS NOT FALSE.
  THE CONTENT OF $result WILL BE MAPPED TO $output['pageNotifications'] WHILE $function WILL BE
  MAPPED TO $output['pageFunction']. FOR NOW ONLY ONE SWITCHFILE PER PAGE IS PERMITTED
  
  THE MENU RELATED FILES MUST SET $id (MENU ITEM ID), $tag (THE TAG WITHOUT THE '<' AND '>' PART 
  TO TO BE USED ON THE MENU NOTIFICATION, USUALLY small), $class (STRING CONTAINING A CLASS TO
  ADD TO THE NOTIFICATION TAG) AND $result (CONTENT OF THE TAG I.E. '3' OR 'New')
  
  FURTHERMORE A NON NOTIFICATION PART IS ALSO INCLUDED. THIS PART UPDATES THE last_active FIELD
  OF THE RECORD RELATIVE TO THE LOGIN SESSION IN logs_access WITH CURRENT DATE&TIME.
  ALSO A CHECK IS PERFORMED ON logs_access TO SEE IF THE force_logout FLAG IS SET. IF THIS IS THE 
  CASE THE SCRIPT IN general.js WILL REDIRECT THE LOGED USER TO logout.php
 **********************************************************************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// turn off error messages
$output['result'] 			= true;
$output['error'] 			= "";
$output['msg'] 				= "";
$output['errorlevel'] 		= "";
$output['notifications'] 	= false;
$output['force_logout'] 	= false;

// path from calls/
$path = "notifications";

// id in pages table
$pid = (int) $_POST['page'];

// get filename of page
$pagename = ($pid == '0') ? "dashboard" : $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'");
// numof caracters of filename
$fnlen = strlen($pagename);
$output['dbg'] = $pagename;

// scan calls/notifications dir in search of files that start with "menu_" or with the filename of the page
$dir = new DirectoryIterator($path);
foreach ($dir as $fileinfo) {
	// if the file found in path is not dot and starts with menu_ with the filename proceed 
    if (!$fileinfo->isDot() and ( substr($fileinfo->getFilename(), 0, $fnlen) == $pagename or substr($fileinfo->getFilename(), 0, 5) == "menu_" )) {
		$result = $function = false;
		$filename = $fileinfo->getFilename();
        include $path.DS.$filename; // include the file 
		if( substr($filename, 0, $fnlen) == $pagename ){
			// if file relative to the page...
			$output['pageNotifications'] = $result; // Can be text or array, set in included file
			$output['pageFunction'] = (string) $function; // Must be text, set in included file - should be name of js function
		}else{
			// if file relative to the menu...
			$output['menuNotifications'][$id] = ($result === false) ? "" : "<".$tag." class=\"notification ".$class."\">".$result."</".$tag.">";
			
		}
    }
}


/********************************************************************************************
   QUESTO NON C'ENTRA NULLA CON LE NOTIFICHE MA LO METTO QUA PER NON FARE UN ALTRA CHIAMATA
 ********************************************************************************************/
//  aggiorno campo last active della tabella log_access così anche se l'utente non fa niente so che è ancora aperto il browser
// con la sessione.
$db->update(DBTABLE_ACCESS_LOGS, array("last_active" => date("Y-m-d H:i:s")), "WHERE id = '".$_SESSION['access_log_id']."'");
// se in log_access ho messo 1 nel campo force_logout del record relativo alla sessione aperta setto flag
// force_logout a truecosì poi la funzione in general.js effettuare un redirect verso logout.php e scollega l'utente
$kickout = $db->get1value("force_logout", DBTABLE_ACCESS_LOGS, "WHERE id = '".$_SESSION['access_log_id']."'");
if(!empty($kickout)) $output['force_logout'] = true;


echo json_encode($output);
?>