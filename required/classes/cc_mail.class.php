<?php

class cc_mail{
	
	// invia email passando array con "mittente", "destinatari" (array x multipli, se jo txt), "oggetto" e "messaggio"
	function invia($variables = false){
		if(empty($variables)) return false;
		
		$destinatari = $variables['destinatari'];
		if(is_array($destinatari)) $destinatari = implode(" , ", $destinatari);
		
		$mail_corpo  = "<BODY BGCOLOR='#ffffff'><p><font color=#000000>".$variables['messaggio']."</font></p>";
		
		$mail_in_html = "MIME-Version: 1.0\r\n";
		$mail_in_html .= "Content-type: text/html; charset=UTF-8\r\n";
		$mail_in_html .= "From: <".$variables['mittente'].">";

		if (mail($destinatari, $variables['oggetto'], $mail_corpo, $mail_in_html)){
			return true;
		}else{
			return false;
		}
	}
	
}

?>