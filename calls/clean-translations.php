<?php
/*****************************************************
 * clean-translations                                *
 * Remove translations that have a section that does *
 * not exist in pages table                          *
 * IN: (POST) nothing                                *
 * OUT: result                                       *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';
$output['errorcode'] = "";

// set specific translations for this script
$_t->setSection("translate");

$pages = $db->col_value("file_name", DBTABLE_PAGES, "", true);
$sections_system = array("LOGIN", "copyrecord", "delrecord", "onoff", "write2db");
$sections_all = array_merge($pages, $sections_system);
$sections_flat = implode("', '", $sections_all);

$clause = "WHERE section NOT IN ( '".$sections_flat."') "; // used several times

// get list of sections thta do not exist
$sections = $db->col_value("section", DBTABLE_TRANSLATIONS, $clause, true);

// open log file
$handle = fopen("log_clean_translation.txt", "a");
fwrite($handle, "*** CLEAN-UP DATE: ".date("Y-m-d H:i:s", time())." ***\n\n");

if($sections){
	$nsections = count($sections);
	fwrite($handle, "Total number of sections: ".$nsections." \n");
	
	
	foreach($sections as $section){
		$list = $db->select_all(DBTABLE_TRANSLATIONS, "WHERE section = '".$section."'");
		$ntranslations = count($list);
		fwrite($handle, "\nSection: ".$section." - ".$ntranslations." translations \n");
		if($list){
			foreach($list as $row){
				$flat = implode(", ", $row);
				fwrite($handle, "-".$flat."\n");				
			}
		}
	}
	// proceed with delete
	if( $db->delete( DBTABLE_TRANSLATIONS, $clause ) ){

		$nrows = $db->rows_affected;

		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "Deleted ".$nrows." translations with non-existing section.";

	}else{

		$output['error'] = "Could not delete orphan translations...";
		$output['msg'] = $db->getError("msg")."<br>\n".$db->getquery();

	}
}else{
	// all clean
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "No non-existing sections found - all ok.";
	
	fwrite($handle, "Nothing to delete.\n");
}

fwrite($handle, "\n*** END CLEAN-UP: ".date("Y-m-d H:i:s", time())." ***\n\n");
fclose($handle);




echo json_encode($output);

	
?>
