<?php
session_start();
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);
$updated = 0;
$parent = '0';


function resetPageOrder($parent){

	global $db, $updated; 
	
	$c = 0;

	// get the pages ordered by order
	$pages = $db->col_value("id", "pages", "WHERE `parent` = '".$parent."' ORDER BY `order` ASC");

	
	if($pages){
		
		foreach($pages as $page){
			$c++;
			if($db->update("pages", array("order" => $c), "WHERE id = '".$page."'")){
				$updated++;
				
				/*** recursive part for child items ***/
				// check if page has children
				$haschildren =  $db->col_value("id", "pages", "WHERE parent = '".$page."'");
				if($haschildren) resetPageOrder($page);

			}else{
				echo "Error during update of record in page tab.<br>\n";
				echo $db->getError("msg");
				echo "<br>\n";
				echo $db->getquery();
				die();
			}
			
		}
		
		//echo "Num ".$updated." pages updated.";
		
	}else{
		echo "No page found with parent value of <strong>".$parent."</strong><br>\n";
		echo $db->getquery();
		die();
	}
	
}

resetPageOrder($parent);
echo $updated;


?>