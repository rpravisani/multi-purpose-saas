<?php
/*****************************************************
 * SAVE MEDIA / PHOTO TO DESTINATION FOLDER AND      *
 * WRITE RECORD TO MEDIA TABLE IN DATABASE.          *
 * # IN : $_FILE, RECORD (INT), PAGE ID (INT)        *
 * # OUT: INSERT RECORD ID                           *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("media_upload");

// default vars
$path = FILEROOT.PATH_PHOTO;

// Check if $_FILES is passed
if(empty($_FILES)){
	$output['error'] = $_t->get('nofile'); // translation in general section 
	$output['msg'] = $_t->get('nofile_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// Check if page id is passed
if(empty($_POST['page'])){
	$output['error'] = $_t->get('nopage'); // translation in general section 
	$output['msg'] = $_t->get('nopage_message'); // translation in general section 
	echo json_encode($output);
	die();
}


// sanitize post values
$recid 	= (int) $_POST['record'];
$pid 	= (int) $_POST['page'];


foreach($_FILES as $k=>$v){
	// get orignal filename
	$oriFileName = $_FILES[$k]["name"];
	// get the file extension
	$exp = explode(".", $oriFileName);
	$extension = end($exp);
	// get name without extension
	array_pop($exp);
	$name = implode(".", $exp);
	// hash name
	$file = sha1($_FILES[$k]["name"].time()).".".$extension;
	
	if($db->insert(DBTABLE_MEDIA, array($name, $file, $_FILES[$k]["size"], $pid, $record, 0, $_SESSION['login_id']), 
                   array("name", "file", "size", "page", "record", "order", "uploadedby"))){
		if(!move_uploaded_file($_FILES[$k]["tmp_name"],$path.$file)){
			$output['error'] = $_t->get('move_media');
			$output['msg'] = $_t->get('move_media_message');
			echo json_encode($output);
			die();
		}
		// get insert id 
		$insert_id = $db->get_insert_id();
	}else{
		//$output['error'] = $_t->get('record_media');
		//$output['msg'] = $_t->get('record_media_message');
		$output['error'] = "Errore durante inserimento in DB";
		$output['msg'] = $db->getError("msg")." - ".$db->getQuery();
		echo json_encode($output);
		die();
	}
}



$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['id'] = $insert_id;
$output['filename'] = $file;


echo json_encode($output);

	
?>
