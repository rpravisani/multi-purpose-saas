<?php
$tabella = TABELLA_CONTRATTI;
$isfoto = false;
// controlli - se il risultato di stop === true viene non viene eseguita la cancellazione
//$stop = cc_mysql_count(TABELLA_CONTATTI, "user", $_POST['id']);
$stop = false;
//$stop_msg = "Impossibile proseguire con la cancellazione, poiche' vi e' l'utente ha un in archivio uno o più clienti. Per ora usare la funzione di disabilitazione.";

$unpublish = false; // se true invece di cancellare depubblica il record

?>