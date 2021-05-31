<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         ***
 *** filename: tabella-iva.php                     ***
 *** elenco dei valori iva                         ***
 *** Modificai e inseriemnto inline                ***
 *****************************************************/

$fields 	= array("id", "valore", "denominazione");
$alias	= array("id", "valore", "denominazione");
$qrytmpl = "SELECT %s FROM ".DBTABLE_IVA;

$_table->del = true;
$_table->edit = true;
$_table->copy = false;
$_table->eye = false;

$_table->editablecols = array("valore" => "number", "denominazione" => "text");

// sort on (use alias name) TODO: in table engine far sì che se non è definito sortme prenda una colonna a caso.
$_table->sortme = "valore";
$_table->sortdir = "asc"; // asc o desc TODO: idem come sopra

// Creating the query...
$_table->createQuery($fields, $alias, $qrytmpl);

$table = $_table->getTable(true);


$menu->pageMessage("DA FARE", "<ul>
<li>Implementazioni per versione full:
	<ul>
		<li>Controllo dependecies quando elimino</li>
	</ul>
</li>
</ul>", "SA");	

?>