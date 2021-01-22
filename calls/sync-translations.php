<?php
/*****************************************************
 * sync-translations                                 *
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

// sanify
$db_host = $db->make_data_safe($_POST['host']);
$db_user = $db->make_data_safe($_POST['user']);
$db_pwd = $db->make_data_safe($_POST['pwd']);
$db_name = $db->make_data_safe($_POST['db']);
$systemonly = ($_POST['systemonly'] == '1') ? true : false;
$clearnotfound = ($_POST['clearnotfound'] == '1') ? true : false;
$languages = $db->make_data_safe($_POST['languages']);
$languages_flat = implode("', '", $languages);

$db_list = "<option></option>";

$other_db = new cc_dbconnect($db_name, $db_host, $db_user, $db_pwd);

if(!$other_db->checkConnection()){
	$output['result'] = false;
	$output['error'] = $other_db->getError("num");
	$output['msg'] = $other_db->getError("msg");
	$output['dblist'] = "<option></option>";
	$output['dbg'] = "";
	echo json_encode($output);
	die();
}

$fields = array("section", "string", "language", "translation");
$inserted = array();
$updated = array();
$output['truncated'] = false;

// get pages of THIS installation
$clause = ($systemonly) ? "WHERE system_page = '1'" : "";
$pages = $db->col_value("file_name", DBTABLE_PAGES, $clause, true);

if($pages){
	$pages_flat = implode("', '", $pages);
	$current_ai = $db->get_next_id(DBTABLE_TRANSLATIONS);
	
	$translations = $other_db->select_all(DBTABLE_TRANSLATIONS, "WHERE section IN ('".$pages_flat."') AND language IN ('".$languages_flat."')");
	
	if($translations){
		
		$handle = fopen("logsync.txt", "a");
		fwrite($handle, "*** SYNC DATE: ".date("Y-m-d H:i:s", time())." ***\n\n");

				
		foreach($translations as $c=>$translation){
			
			$values = array( $translation['section'], $translation['string'], $translation['language'], $translation['translation'] );
			$update = array( "translation" => $translation['translation']);
			
			$db->insert(DBTABLE_TRANSLATIONS, $values, $fields, $update);
			
			$insid = (int) $db->get_insert_id();
			
			if(empty($insid)){
				// Already in the table and with same value, nothing is inserted, nothing is updated
				// but auto_increment is updated, set it back
				$ai = (int) $db->get_next_id(DBTABLE_TRANSLATIONS);
				$ai--;
				$db->set_auto_increment_value(DBTABLE_TRANSLATIONS, $ai);
				
			}else{
				// inserted or updated.
				if($insid >= $current_ai){
					// inserted
					$inserted[] = $insid;
					$t = "INSERT";
				}else{
					// updated - set ai counter back though
					$updated[] = $insid;
					$ai = (int) $db->get_next_id(DBTABLE_TRANSLATIONS);
					$ai--;
					$db->set_auto_increment_value(DBTABLE_TRANSLATIONS, $ai);
					$t = "UPDATE";
				}
				fwrite($handle, $t.". Id: ".$insid." - Section: ".$translation['section']." - String: ".$translation['string']." - Lang: ".$translation['language']." - Trans: ".$translation['translation']."\n");
				
			}
			
		} // end foreach
		$nins = (int) count($inserted);
		$nups = (int) count($updated);
		$tot = (int) $nins+$nups;
		fwrite($handle, "\nTOTAL RECS INS: ".$nins);
		fwrite($handle, "\nTOTAL RECS UPDATED: ".$nups);
		fwrite($handle, "\nTOTAL RECORDS: ".$tot);
		fwrite($handle, "\n\n*** END SYNC ".date("Y-m-d H:i:s", time() )." ***\n\n" );
		fclose($handle);
		
		// if delete translations lost flag is on i will truncate that table
		if($clearnotfound){
			if($db->truncate(DBTABLE_TRANSLATIONS_LOST)) $output['truncated'] = true;
		}
		

		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "";
		$output['inserted'] = $inserted;
		$output['updated'] = $updated;
		
		
	}else{
		
		$output['error'] = "Error syncronizing";
		$output['msg'] = "No translations found";
		$output['dbg'] = $db->getQuery();;
		
	} // if $translation
	
}else{
	$output['error'] = "Error syncronizing";
	$output['msg'] = "No pages found";
	$output['dbg'] = $db->getQuery();;
} // end if pages



echo json_encode($output);

	
?>
