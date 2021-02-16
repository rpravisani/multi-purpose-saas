<?php
/******************************************************
 * DELETE MEDIA / PHOTO FROM FOLDER AND FROM DB TABLE *
 * # IN : ARRAY WITH IDS OF MEDIA TABLE                           *
 * # OUT: RESULT                                      *
 ******************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("media_upload");

// default vars
$path = FILEROOT.PATH_PHOTO;


// Check if record id is passed
if(empty($_POST['ids'])){
	$output['error'] = $_t->get('norec'); // translation in general section 
	$output['msg'] = $_t->get('norec_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// sanitize post values
$ids 	= (array) $_POST['ids'];


$ids_flat = implode(", ", $ids);

// get record from DB
$medias = $db->select_all(DBTABLE_MEDIA, "WHERE id IN (".$ids_flat.")");

// se non ho trovato il record in db resitutisco errore
if(!$medias){
	$output['error'] = $_t->get('media_records_not_found'); // translation in general section 
	$output['msg'] = $_t->get('media_records_not_found_message'); // translation in general section 
	echo json_encode($output);
	die();
}

foreach($medias as $media){
    
    // sostituisco path generico con path definito in DB
    if(!empty($media['path'])) $path = FILEROOT.$media['path'];

    if( file_exists($path.$media['file']) ){

        // elimina file se non riesco a cancellare invio messaggio di errore
        if(!unlink($path.$media['file'])){
            $output['error'] = $_t->get('unlink_media'); // translation in general section 
            $output['msg'] = $_t->get('unlink_media_message'); // translation in general section 
            echo json_encode($output);
            die();
        }

    }
    
    // rimuovo singolo record da tabella media
    if($db->delete(DBTABLE_MEDIA, "WHERE id = '".$media['id']."'")){
        
        $output['deleted'][] = $media['id'];

        // Reest order: diminiuisco di 1 il valore ordine per tutti i media della pagina, record e sezione che avevano un ordine maggiore del file cancellato
        $update_qry = "UPDATE ".DBTABLE_MEDIA." 
                        SET `order` = `order` -1 
                        WHERE page = '".$media['page']."' 
                        AND record = '".$media['record']."' 
                        AND section = '".$media['section']."' 
                        AND `order` > '".$media['order']."'";

        $db->execute_query($update_qry);

        $output['result'] = true;
        $output['error'] = "";
        $output['msg'] = "";
        
    }else{
        
        // errore: non sono risucito a cancellare file
        $output['error'] = $_t->get('delrec_media'); // translation in general section 
        $output['msg'] = $_t->get('delrec_media_message'); // translation in general section 
    }
    
    
} // end foreach

echo json_encode($output);

	
?>
