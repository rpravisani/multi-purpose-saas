<?php
/***************************************************************
 * UPLOAD FILE TO UPLOAD DIR + PAGE_PID WHERE PID IS PAGE ID   *
 * TODO: ERROR HANDELING VIA _SESSION                          *
 ***************************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';
session_start();

// set specific translations for this script
//$_t->setSection("onoff");

// Check if record id is passed
if(empty($_FILES)){
	$_SESSION['error_title'] = "Nessun file selezionato";
	$_SESSION['error_message'] = "Seleziona un file prima di cliccare Carica"; 
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=html&a=upload");
	die();
}

// Check if page id is passed
if(empty($_POST['pid'])){
	$_SESSION['error_title'] = $_t->get('nopage');;
	$_SESSION['error_message'] =  $_t->get('nopage_message'); 
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=html&a=upload");
	die();
}


// sanitize
$cliente 	= (int) $_POST['cliente'];
$pid 		= (int) $_POST['pid'];
$rename 		= (int) $_POST['rename'];

// define directory
$path = UPLOAD_DIR."page_".$pid;

// if dir doesn't exist create it
if(!file_exists($path)){
	if(!mkdir($path, 0700)){
		$_SESSION['error_title'] = "Impossibile creare cartella";
		$_SESSION['error_message'] = "Impossibile creare cartella ".$path; 
		header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=html&a=upload");
		die();
	}
}

$path .= "/";

// start loop
foreach($_FILES as $k=>$v){
	// get filename -- if rename is true create hash of name
	$oriFileName = $_FILES[$k]["name"];
	$fileName = (empty($rename)) ? $oriFileName : sha1($_FILES[$k]["name"].time()).".xls";
	
	if($db->insert(DBTABLE_UPLOADS, array($pid, $cliente, $oriFileName, $fileName, $_FILES[$k]['size']), array("pid", "cliente", "filename_ori", "filename", "dimensione"))){
		if(!move_uploaded_file($_FILES[$k]["tmp_name"],$path.$fileName)){
			$_SESSION['error_title'] = "Impossibile spostare file";
			$_SESSION['error_message'] = "Impossibile spostare il file caricato nella cartella di destinazione"; 
			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=html&a=upload");
			die();
		}
	}else{
		$_SESSION['error_title'] = "Impossibile registrare file in DB";
		$_SESSION['error_message'] = "Impossibile scrivere nella tabella ".DBTABLE_UPLOADS."<br>".$db->getError("msg")."<br>Query : ".$db->getquery(); 
		header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=html&a=upload");
		die();
	}
}

$_SESSION['filename'] 		= $path.$fileName;
$_SESSION['ori_filename'] 	= $oriFileName;

header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=html&a=show");
// SONO RIMASTO QUA: DEVO CREARE MODEL E VIEW carica_excel_km.php -- 
// nel model mettere funzioni loadXls e in view il risultato / report

?>
