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
$recid 	 = (int) $_POST['record'];
$pid 	 = (int) $_POST['page'];
$section = $db->make_data_safe($_POST['section']);
$path    = sanify_path($_POST['path']); // path upload, potrebbe essere vuoto e allora prendo valore default


// controllo che la path esista e sia scrivibile se no restituisco errore
if( !file_exists(FILEROOT.$path) ){
	$output['error'] = $_t->get('nofolder'); // translation in general section 
	$output['msg'] = $_t->get('folder_does_not_exist'); // translation in general section 
	echo json_encode($output);
	die();    
}
if( !is_writable(FILEROOT.$path) ){
	$output['error'] = $_t->get('nofolder_permission'); // translation in general section 
	//$output['msg'] = $_t->get('cannot_write_to_folder'); // translation in general section 
	$output['msg'] = "La path ".FILEROOT.$path." non è scrivibile"; 
	echo json_encode($output);
	die();    
}


// loop files ricevuti
foreach($_FILES as $k=>$v){
	// get orignal filename
    
	$oriFileName = $_FILES[$k]["name"];
    $pathinfo = pathinfo($oriFileName);
    $name = $pathinfo['filename'];
    $extension = $pathinfo['extension'];
    
	$filetype = $_FILES[$k]["type"]; // non affidabile al 100% -  è il mime type mandato dal browsers (es. image/png);
    
	// hash name
	$file = sha1($_FILES[$k]["name"].time()).".".$extension;
    
    $uploadedby = (isset($_SESSION['access_log_id'])) ? (int) $_SESSION['access_log_id'] : '0';
    
    // Se ho record determino ordine
    if(!empty($recid)){
        $order = (int) $db->get_max_row('order', DBTABLE_MEDIA, "WHERE page= '".$pid."' AND section = '".$section."' AND record = '".$recid."'" );
        $order++;
    }else{
        $order = 0;
    }
    
    /*** TERRIBILE ACCORCCHIO: SE SLIDE VERIFICO CHE IL NOME NON SIà GIA' PRESENTE IN DB ***/
    if($pid == '122'){
        $checkname = $db->get1value('name', DBTABLE_MEDIA, "WHERE name='".$name."' AND page= '".$pid."' AND section = '".$section."' AND record = '".$recid."'" );
        if($checkname){
            
            http_response_code(503);
			echo "Impossible caricare la slide <strong>".$name."</strong>, poiché è già stata caricata in questa relazione";
			die();
        }
    }
    
	if($db->insert(DBTABLE_MEDIA, 
                   array($name, $file, $_FILES[$k]["size"], $filetype, $pid, $recid, $section, $path, $order, $uploadedby), 
                   array("name", "file", "size",  "filetype", "page", "record", "section", "path", "order", "uploadedby"))){
        
        // sposto file in cartella 
		if(!move_uploaded_file($_FILES[$k]["tmp_name"],FILEROOT.$path.$file)){
			//$output['error'] = $_t->get('move_media');
			//$output['msg'] = $_t->get('move_media_message');
            http_response_code(503);
			echo "Impossible spostare file tmp <strong>".$_FILES[$k]["tmp_name"]."</strong> in ".FILEROOT.$path.$file;
			die();

		}
		// get insert id 
		$insert_id = $db->get_insert_id();
	}else{
		//$output['error'] = $_t->get('record_media');
		//$output['msg'] = $_t->get('record_media_message');
        http_response_code(503);
		echo "Errore durante inserimento file media in DB";
        //if($_user->getSubscriptionType() == '0'){
		  echo "<br>\n".$db->getError("msg")." - ".$db->getQuery();
        //}
		die();
	}
}



$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['id'] = $insert_id;
$output['filename'] = $file;


echo json_encode($output);


function sanify_path($path){
    
    global $db;
    
    $path = $db->make_data_safe($path);
    $path = trim($path, ' /');
    
    if(empty($path))return PATH_PHOTO;
    
    return $path.'/';

}

	
?>
