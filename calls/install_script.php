<?php

include_once '_head.php';


$id = $db->make_data_safe($_POST['id']);

$exp = explode("_", $id);

$what = array_shift($exp);
$source = implode("_", $exp);

switch($what){
	case "page":
		delPage($source);
		break;
	case "truncate":
		truncate($source);
		break;
	case "dir":
		emptyDir($source);
		break;
	case "dt":
		deleteDir($source);
		break;
	default:
		$output['error'] = "command-not-valid";
		$output['msg'] = "The type of operation is not valid";
}

echo json_encode($output);
die();


function deleteDir($table){
	
	global $db, $output;
	
	if( substr($table, 0, 5) != 'data_' ){
		$output['error'] = "not-data-table";
		$output['msg'] = "Table is not a data table, cannot delete!";
		
	}
	
	$table_info = $db->get_table_info($table);
	
	if($table_info){
		
		$records = $db->count_rows( $table );
		
		$db->drop($table);
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "Eliminata tabella con ".$records." rec.";
		$output['errorlevel'] = "success";
		
	}else{
		
		$output['error'] = "table-does-not-exist";
		$output['msg'] = "Table does not exist";
		
	}
	
	return;	
	
}

function truncate($table){
	
	global $db, $output;
	
	$table_info = $db->get_table_info($table);
	
	if($table_info){
		
		$records = $db->count_rows( $table );

		
		$db->truncate($table);
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "Eliminati ".$records." rec.";
		$output['errorlevel'] = "success";
		
	}else{
		
		$output['error'] = "table-does-not-exist";
		$output['msg'] = "Table does not exist";
		
	}
	
	return;	
	
}

function emptyDir($dir){
	
	global $output;
	
	$path = FILEROOT.$dir;
	$nfiles = $totfiles = 0;
	$notDelete = array();
	
	if(file_exists($path)){
		
		$fi = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
		
		foreach($fi as $file){
			
			if($file->isDir()) continue;
			$totfiles++;
			$filename = $file->getFilename();
			if(unlink($path."/".$filename)){
				$nfiles++;				
			}else{
				$notDeleted[] = $filename;
			}
 			
		}
		
		if(empty($notDeleted)){
			
			$output['result'] = true;
			$output['error'] = "";
			$output['msg'] = "Cancellati ".$nfiles." file";
			$output['errorlevel'] = "success";
			
		}else{
			
			$nd_count = count($notDeleted);
			$output['error'] = "some-files-not-deleted";
			$output['msg'] = "Non cancellati ".$nd_count." files su ".$totfiles;
			$output['dbg'] = $notDeleted;
			
		}
		
	}else{
		
		$output['error'] = "dir_not_exist";
		$output['msg'] = "Dir ".$dir." does not exist";
	
	}
	
	return;
	
}

function delPage($pid){
	
	global $db, $output;
	
	$errors = array();
	
	$check = $db->get1row(DBTABLE_PAGES, "WHERE id = '".$pid."' AND system_page = '0'");	
	
	if(!$check){
		$output['error'] = "page-not-found"; // translation in pages section 
		$output['msg'] = "Pagina non trovata!"; // translation in pages section 
		return;
	}
	
	$file_name = $check['file_name'];
	
	/* DELETE FILES */
	$file_paths = array();
	$file_paths[] = "models/".$file_name.".php";
	$file_paths[] = "models/tables/".$file_name.".class.php";
	$file_paths[] = "views/".$file_name.".php";
	$file_paths[] = "css/pages/".$file_name.".css";
	$file_paths[] = "js/pages/".$file_name.".js";
	$file_paths[] = "calls/copy/".$file_name.".php";
	$file_paths[] = "calls/del/".$file_name.".php";
	$file_paths[] = "calls/publish/".$file_name.".php";
	$file_paths[] = "calls/save/".$file_name.".php";
	$file_paths[] = "calls/save/".$file_name."_post_actions.php";
	
	// REMOVE PAGE RECORDS FROM TABLES
	$tables = array();
	$tables['pages'] = 'id';
	$tables['page_permissions'] = 'page';
	$tables['media'] = 'page';
	
	$tables_translate = array();
	$tables_translate['translations'] = 'section';
	$tables_translate['translations_lost'] = 'file';
	
	$num_tab = count($tables);
	$num_tab2 = count($tables_translate);
	$num_files = count($file_paths);
	
	
	foreach($tables as $table => $field){
		if(!$db->delete($table, "WHERE ".$field." = '".$pid."'")){
			$errors[] = $db->get_query();
		}else{
			$num_tab_deleted++;
		}
	}
	
	if(!empty($file_name)){
		foreach($tables_translate as $table => $field){
			if(!$db->delete($table, "WHERE ".$field." = '".$file_name."'")){
				$errors[] = $db->get_query();
			}else{
				$num_tab2_deleted++;
			}
		}
	}
		
	foreach($file_paths as $file2del){

		$full_path = FILEROOT.$file2del;

		if(file_exists($full_path)){

			if( unlink($full_path) ){
				$num_files_deleted++;
			}else{
				$errors[] = "Unable to delete ".$file2del;
			}

		}

	} 
	
	/*** RESET PAGE ORDER ***/
	$qry = "UPDATE ".DBTABLE_PAGES." SET `order` = `order`-1 WHERE `order` > ".$check['order']." AND parent = '".$check['parent']."'";
	if(!$db->execute_query($qry)){
		$errors[] = $db->getQuery();
	}
	
	if(empty($errors)){
		
		$msg = "File canc: ".$num_files_deleted."\n";
		$msg .= "Pag. eliminate da ".$num_tab_deleted." tab su ".$num_tab."\n";
		if($num_tab2_deleted == $num_tab2) $msg .= "Traduzioni eliminate";
		
		$info = "<i class='fa fa-info text-primary' title='".$msg."'></i>";
		
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = $info;
		$output['errorlevel'] = "success";
		
		
		
	}else{
		
		$output['error'] = "errors";
		$output['msg'] = "Ci sono stati degli errori";		
		$output['dbg'] = $errors;
		
	}
	
	return;
	
	
}



?>