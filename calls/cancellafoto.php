<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$output = array();
$output['result'] = false;
$output['error'] = "nopost";
$output['msg'] = "Nessun dato inviato";

if (isset($_POST['id'])){
	$db = new cc_dbconnect(DB_NAME);
	$err = array();
	$foto_2_del = $db->get1row(TABELLA_FOTO, "WHERE id='".$_POST['id']."'");
	$foto = "../../".PATH_FOTO.$foto_2_del['nome_file'];
	
	if(file_exists($foto)){
		if(!unlink($foto)) $err[] = "Impossibile eliminare foto principale";
	}
				   		
	
	if( $db->delete(TABELLA_FOTO, "WHERE id='".$_POST['id']."'") ){
		$qry = "UPDATE ".TABELLA_FOTO." SET ordine = ordine-1 WHERE immobile='".$foto_2_del['immobile']."' AND ordine > '".$foto_2_del['ordine']."'";
		if( !$db->execute_query($qry) ) $err[] = "Impossibile cambiare ordine alle foto!\n".$qry;
	}else{
		$err[] = "Impossibile eliminare il record dal database foto.";
	}
	
	if(count($err) > 0){
		$output['msg'] = implode("\n", $err);
		$output['error'] = "errori";
	}else{
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "cancellato";
	}
}

echo json_encode($output);
?>
