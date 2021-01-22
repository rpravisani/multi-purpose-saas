<?php
/***********************************************************************************************************
 * SCRIPT CHE CREA PDF TRAMITE API pdfcrowd VERSIONE AJAX                                                  *
 * IN: $outputpdf 				: download, inline, email o save. default: "download"                      *
 *     $id_scheda_intervento 	: id della scheda intervento, se non settata die()                         *
 *     $redirect_after 			: true (default) o false. Se reindirizzare in caso di errore o fine lavoro *
 ***********************************************************************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

if(empty($_POST['id_scheda_intervento'])){
	$output['error'] 	= "Impossibile creare PDF"; // translation in general section 
	$output['msg'] 		= "Non è stata impostata alcune scheda intervento per la creazione del pdf"; // translation in general section 
	echo json_encode($output);
	die();
}

$id_scheda_intervento 	= $_POST['id_scheda_intervento'];
$outputpdf				= (!isset($_POST['outputpdf'])) ? "download" : $_POST['outputpdf'];

// per sicurezza - per ora solo così
$outputpdf = "save";

require_once '../pdf/pdfcrowd.php';

$nome_file_pdf = "Scheda Intervento N. ".$id_scheda_intervento." - Picasso Gomme.pdf";
$output['msg'] = $nome_file_pdf;

try
{   
    // create an API client instance
    $client = new Pdfcrowd("evertech_it", "e088742bd70a2391f50221539f0ed952");

    // convert a web page and store the generated PDF into a $pdf variable
	if($outputpdf == "save" or $outputpdf == "email"){
		
		// setto path/nome file
		$nome_file_pdf = FILEROOT."schede-pdf/".$nome_file_pdf;
	    $pdf = $client->convertURI('http://evertech.it/picassotpl/cpanel.php?pid=4&v=pdf&a=view&r='.$id_scheda_intervento, fopen($nome_file_pdf, 'wb'));
		
		if($outputpdf == "save"){
			
			$output['result'] = true;
			$output['error'] = false;
			echo json_encode($output);
			die();
			
		}else{
			// set attachment
			$attachments[] = $nome_file_pdf;
			// include script invio email
			include 'required/send-email.php';
		}
		
	}else if($outputpdf == "download" or $outputpdf == "inline"){
		$content_disposition = ( $outputpdf == "inline") ? "inline" : "attachment";
	    $pdf = $client->convertURI('http://evertech.it/picassotpl/cpanel.php?pid=4&v=pdf&a=view&r='.$id_scheda_intervento);

		// set HTTP response headers
		header("Content-Type: application/pdf");
		header("Cache-Control: max-age=0");
		header("Accept-Ranges: none");
		header("Content-Disposition: ".$content_disposition."; filename=\"".$nome_file_pdf."\"");
	
		// send the generated PDF 
		echo $pdf;
		
	}else{
		$_SESSION['error_title'] 	= "Impossibile creare PDF";
		$_SESSION['error_message'] 	= "Nessuna azione settata o riconosciuta!";
		if($redirect_after){
			header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
			exit();
		}else{
			return;
		}
	}
		
	
}

catch(PdfcrowdException $why){
	$_SESSION['error_title'] 	= "Impossibile creare PDF";
	$_SESSION['error_message'] 	= "Pdfcrowd Error: " . $why;
	
	if($redirect_after){
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return;
	}
}

?>