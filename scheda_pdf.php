<?php
/***********************************************************************************************************
 * SCRIPT CHE CREA PDF TRAMITE API pdfcrowd                                                                *
 * IN: $outputpdf 				: download, inline, email o save. default: "download"                      *
 *     $id_scheda_intervento 	: id della scheda intervento, se non settata die()                         *
 *     $redirect_after 			: true (default) o false. Se reindirizzare in caso di errore o fine lavoro *
 ***********************************************************************************************************/

require_once 'pdf/pdfcrowd.php';

if(!isset($redirect_after)) $redirect_after = true;
if(!isset($outputpdf)) $outputpdf = "download";
if(!isset($id_scheda_intervento)) $id_scheda_intervento = $_GET['r'];

if(empty($id_scheda_intervento)){
	$_SESSION['error_title'] 	= "Impossibile creare PDF";
	$_SESSION['error_message'] 	= "Non è stata impostata alcune scheda intervento per la creazione del pdf";
	if($redirect_after){
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return;
	}
}
$nome_file_pdf = "Scheda Intervento N. ".$id_scheda_intervento." - Picasso Gomme.pdf";


try
{   
    // create an API client instance
    $client = new Pdfcrowd("rpravisani", "ce9712a53abb17238e154e62f8a3430c");

    // convert a web page and store the generated PDF into a $pdf variable
	if($outputpdf == "save" or $outputpdf == "email"){
		
		// setto path/nome file
		$nome_file_pdf = FILEROOT."schede-pdf/".$nome_file_pdf;
	    $pdf = $client->convertURI('http://evertech.it/picassotpl/cpanel.php?pid=4&v=pdf&a=view&r='.$id_scheda_intervento, fopen($nome_file_pdf, 'wb'));
		
		if($outputpdf == "save"){
			$_SESSION['success_title'] = "PDF salvato correttamente";
			$_SESSION['success_message']	= "Il pdf <strong>".$id_scheda_intervento."</strong> è stato salvato correttamente!";
			if($redirect_after){
				header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
				exit();
			}
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