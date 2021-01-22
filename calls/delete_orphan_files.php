<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';
$dbg = "";
$c = 0;
$nodeleted = array();

if(!empty($_POST['list'])){
	
	$tot = count($_POST['list']);
	
	foreach($_POST['list'] as $file){
		
		$path = FILEROOT.$file;
		if(unlink($path)){
			$c++;
		}else{
			$nodeleted[] = $file;
		}
		
	}
	
	if($c == $tot){
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "";		
	}else{
		$diff = $tot - $c;
		$output['error'] = "Errore durante la cancellazione di alcuni file";
		$output['msg'] = "Non sono riuscito a cancellare i seguenti "+$diff+" file:";
		$output['msg'] .= implode("<br>\n", $nodeleted);
	}
	
}else{
		$output['error'] = "Lista file da cancellare vuota";
		$output['msg'] = "Non è stato passato l'elenco dei file da cancellare.";		
		$output['errorlevel'] = "warning";		
}





echo json_encode($output);

	
?>
