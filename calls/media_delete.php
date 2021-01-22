<?php
/******************************************************
 * DELETE MEDIA / PHOTO FROM FOLDER AND FROM DB TABLE *
 * # IN : ID OF MEDIA TABLE                           *
 * # OUT: RESULT                                      *
 ******************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("media_upload");

// default vars
$path = FILEROOT.PATH_PHOTO;


// Check if record id is passed
if(empty($_POST['id'])){
	$output['error'] = $_t->get('norec'); // translation in general section 
	$output['msg'] = $_t->get('norec_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// sanitize post values
$id 	= (int) $_POST['id'];

// get filename from DB
$media = $db->get1row(DBTABLE_MEDIA, "WHERE id = '".$id."'");

if(!$media){
	$output['error'] = $_t->get('nofilename'); // translation in general section 
	$output['msg'] = $_t->get('nofilename_message'); // translation in general section 
	echo json_encode($output);
	die();
}

if(!unlink($path.$media['file'])){
	$output['error'] = $_t->get('unlink_media'); // translation in general section 
	$output['msg'] = $_t->get('unlink_media_message'); // translation in general section 
	echo json_encode($output);
	die();
}

if($db->delete(DBTABLE_MEDIA, "WHERE id = '".$id."'")){
	// reset order
	$update_qry = "UPDATE ".DBTABLE_MEDIA." SET `order` = `order` -1 WHERE page = '".$media['page']."' 
							AND record = '".$media['record']."' AND `order` > '".$media['order']."'";
	$db->execute_query($update_qry);
	
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
}else{
	$output['error'] = $_t->get('delrec_media'); // translation in general section 
	$output['msg'] = $_t->get('delrec_media_message'); // translation in general section 
}


echo json_encode($output);

	
?>
