<?php
/*****************************************************
  backup-db                                 
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$filename = $db->make_data_safe($_POST['filename']);

$backup_path = FILEROOT."backup/";

if(file_exists($backup_path.$filename)){
	if(unlink($backup_path.$filename)){
		
		$output['result'] = true;
		$output['error'] = ""; // title of modal box
		$output['msg'] = ""; // message inside modal box
		$output['errorlevel'] = "success"; // color of modal box
		
	}else{
		
		$output['error'] = "Impossibile cancellare file"; // title of modal box
		$output['msg'] = "Non è stato possibile cancellare il file di backup DB <strong>".$filename."</strong>"; // message inside modal box
		
	}
	
}else{
	
	$output['error'] = "File non esiste"; // title of modal box
	$output['msg'] = "Il file di backup DB <strong>".$filename."</strong> non è stato trovato in <strong>".$backup_path."</strong>"; // message inside modal box
	
}
	

echo json_encode($output);

	
?>
