<?php

defined('_CCCMS') or die;

/*** TIPO USCITE ***/
$tipo_uscite 	= $db->key_value("id", "nome", DBTABLE_TIPO_USCITE, "WHERE active = '1'");

/*** AUTOMEZZI ***/
$qry_automezzi = "SELECT id, CONCAT(numero_radio, '/', targa) AS codice, strade_strette FROM ".DBTABLE_AUTOMEZZI." WHERE active='1' AND uscito = '0'";
$automezzi = $db->fetch_array($qry_automezzi);

// Formatto data e recupero fascia oraria attuale
$data = date("Y-m-d", time());
$fascia = $helper->getfasciaOrario(time());

/*** SQUADRE E MILITI DISPONIBILI ***/
// recupero elementi della squadra in questo momento (data / fascia)
$squadra = $helper->getSquadra($data, $fascia);

// recupero elementi da squadra che sono da escludere da militi disponibili
$exclude_autista = $exclude_caposquadra = $exclude_militi = array();
if($squadra){
	$exclude_autista = (empty($squadra['autista'])) ? array() : array($squadra['autista']);
	$exclude_caposquadra = (empty($squadra['caposquadra'])) ? array() : array($squadra['caposquadra']);
	if(!empty($squadra['milite1'])) $exclude_militi[] = $squadra['milite1'];
	if(!empty($squadra['milite2'])) $exclude_militi[] = $squadra['milite2'];
}

// recupero disponibili
$autisti 	 = $helper->getAutisti(false, "", $data, $fascia, false, $exclude_autista );
$caposquadra = $helper->getCapisquadra(false, "", $data, $fascia, false, $exclude_caposquadra );
$militi 	 = $helper->getMiliti(false, "", $data, $fascia, false, $exclude_militi );


/*** SERVIZI PROGRAMMATI - estrapolo tutti i servizi programmati del giorno non ancora effettuati ***/
$qry_servizi_programmati = "
	SELECT 
	a.id, 
	p.codice_identificativo, 
	CONCAT(s.nome, ' • ', r.nome) AS destinazione, 
	a.ora_partenza,
	a.autista, 
	a.milite1, 
	a.milite2, 
	x.strada_stretta
	FROM 
	data_servizi_programmati AS p, 
	data_servizi_programmati_agenda AS a, 
	data_strutture AS s, 
	data_reparti AS r, 
	data_pazienti AS x
	WHERE 
	r.id = p.reparto_destinazione AND 
	s.id = p.struttura_destinazione AND 
	p.id = a.servizio AND 
	x.id = p.paziente AND 
	a.data = '".$data."' AND
	a.effettuato = '0' 
	AND a.active = '1' 
	ORDER BY a.ora_partenza
";

$servizi_programmati = $db->fetch_array($qry_servizi_programmati);

/*** SERVIZI SOCIALI  ***/
$qry_servizi_sociali = "
	SELECT 
	a.id, 
	CONCAT(p.cognome, ' ', p.nome) AS paziente, 
	a.andata_autista AS autista, 
	a.andata_milite1 AS milite1, 
	a.andata_milite2 AS milite2, 
	a.prelevare_ora AS ora, 
	CONCAT(s.nome, ' • ', r.nome) AS destinazione
	FROM 
	`data_servizi_sociali_agenda` AS a, 
	`data_servizi_sociali` AS x, 
	`data_pazienti` AS p,
	`data_strutture` AS s, 
	`data_reparti` AS r 
	WHERE 
	x.id = a.servizio AND 
	p.id = x.paziente AND 
	s.id = a.destinazione_struttura AND 
	r.id = a.destinazione_reparto AND
	a.data = '".$data."' AND 
	a.active = '1' AND a.effettuato = '0'
";
$servizi_sociali = $db->fetch_array($qry_servizi_sociali);


/*** DIMISSIONI ***/

// per ora estrapolo solo pazienti che non sono ne privati ne social, poi vediamo
$pazienti = $helper->getPazienti(); // default options select senza selected
$strutture = $helper->getAllStrutture();
$reparti = $helper->getReparti();


?>