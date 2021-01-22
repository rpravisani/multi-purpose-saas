<?php
$tabella['id'] = TABELLA_CLIENTI;
$tabella['cliente'] = TABELLA_CLIENTI_RICHIEDENTI;
$isfoto = false;

// ricupero eventuali sedi
$sedi = $db->col_value("id", TABELLA_CLIENTI, "WHERE genitore = '".$_POST['id']."'");
$cliente = $db->get1row(TABELLA_CLIENTI, "WHERE id = '".$_POST['id']."'");

if($sedi){
	$sedi = implode(", ", $sedi);
	$sedi = $_POST['id'].", ".$sedi;
}else{
	$sedi = $_POST['id'];
}

// controlli - se il risultato di stop === true viene non viene eseguita la cancellazione
$stop = $db->col_value ("id", TABELLA_RICHIESTE, "WHERE cliente IN (".$sedi.")");

if($cliente['genitore'] == '0'){
	$stop_msg = "Impossibile proseguire con la cancellazione di questo cliente, risulta una o più richieste a nome di questo cliente.";
}else{
	$stop_msg = "Impossibile proseguire con la cancellazione di questa sede, risulta una o più richieste a nome di questo cliente.";
}


$extra_clause[TABELLA_CLIENTI] = " OR genitore = '".$_POST['id']."'"; // se $tabella è stringa, questo è stringa, se $tabella è array anche questo è array
$extra_clause[TABELLA_CLIENTI_RICHIEDENTI] = " OR cliente IN (".$sedi.")"; // se $tabella è stringa, questo è stringa, se $tabella è array anche questo è array

$unpublish = false; // se true invece di cancellare depubblica il record
?>