<?php
defined('_CCCMS') or die;
/*******************************************************
 *** MODEL                                          
 *** filename: text-field-edit.php               
 *** edit params of editable text field 
 *********************************************************/

// get data if $_record is not empty
if(!empty($_record)){
	// date the data from the table
	$boxtitle = "Edit text field ";
	
	$_data = $db->get1row(DBTABLE_TEXT_FIELDS, "WHERE id='".$_record."'");
	
	
}else{
	//$dati_cliente = array();	STATO SOSTITUITO DA _data E CREATO VUOTO IN required.php
	$boxtitle = "Create a new text field";
}



?>