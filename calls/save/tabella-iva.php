<?php

$tables = array(DBTABLE_IVA);

/*** CHECK VALUES ***/
if($safevalues['valore'] > 99){
	errorOutput($_t->get("tabella-iva"), sprintf ($_t->get('value-too-high'), $page), $_SERVER['HTTP_REFERER']);
}



?>