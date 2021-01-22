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


// Check if record id is passed
if(empty($_POST['id'])){
	$output['error'] = $_t->get('norec'); // translation in general section 
	$output['msg'] = $_t->get('norec_message'); // translation in general section 
	echo json_encode($output);
	die();
}

if(empty($_POST['ord'])){
	$output['error'] = $_t->get('noord'); // translation in general section 
	$output['msg'] = $_t->get('noord_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// sanitize post values
$id 	= (int) $_POST['id'];
$ord = (int) $_POST['ord'];

$media = $db->get1row(DBTABLE_MEDIA, "WHERE id = '".$id."'");
$old_pos = (int) $media['order'];

if($ord < $old_pos){
	// new position is smaller than old position - moving to the left /  move inbetweens to the right (+)
	$update_qry = "UPDATE ".DBTABLE_MEDIA." SET `order` = `order` + 1 
					WHERE page = '".$media['page']."' AND record = '".$media['record']."' 
					AND `order` BETWEEN '".$ord."' AND '".$old_pos."'";
					
}else{
	// new position is larger than old position - moving to the right /  move inbetweens to the left (-)
	$update_qry = "UPDATE ".DBTABLE_MEDIA." SET `order` = `order` - 1 
					WHERE page = '".$media['page']."' AND record = '".$media['record']."' 
					AND `order` BETWEEN '".$old_pos."' AND '".$ord."'";
					
}


// update inbetween media
$db->execute_query($update_qry);

// update moved item
$db->update(DBTABLE_MEDIA, array('order' => $ord), "WHERE id = '".$id."'");

// output... for output's sake
echo "Media moved from position ".$old_pos." to position ".$ord;
?>
