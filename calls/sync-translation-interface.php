<?php
/*****************************************************
 * get_sedi_cliente                                  *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id = (int) $_POST['id']; // id agenda
$servizio = (int) $_POST['servizio'];
$date = $db->make_data_safe($_POST['data']);


$qry = "
	SELECT 
	CONCAT(p.cognome, ' ', p.nome) AS paziente, 
	a.prelevare, 
	a.prelevare_ora, 
	a.andata_autista, 
	a.andata_milite1, 
	a.andata_milite2, 
	CONCAT(s.nome, ' • ', r.nome) AS destinazione,
	a.destinazione_ora, 
	a.ritorno, 
	a.ritorno_ora, 
	a.ritorno_autista, 
	a.ritorno_milite1, 
	a.ritorno_milite2, 
	a.effettuato
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
	a.id = '".$id."'
";

$data = $db->fetch_array_row($qry);

if(!$data){
	// new. Get data from tab servizi
	$qry_serv = "
		SELECT 
		CONCAT(p.cognome, ' ', p.nome) AS paziente, 
		x.prelevare, 
		x.prelevare_ora, 
		CONCAT(s.nome, ' • ', r.nome) AS destinazione,
		x.destinazione_ora, 
		x.ritorno, 
		x.ritorno_ora 
		FROM 
		`data_servizi_sociali` AS x, 
		`data_pazienti` AS p,
		`data_strutture` AS s, 
		`data_reparti` AS r 
		WHERE 
		p.id = x.paziente AND 
		s.id = x.struttura_destinazione AND 
		r.id = x.reparto_destinazione AND
		x.id = '".$servizio."'
	";
	$data = $db->fetch_array_row($qry_serv);
	
	if(!$data) die("riga 73");
	
}

// orari
$prelevare_ora = substr($data['prelevare_ora'], 0, 5);
$destinazione_ora = substr($data['destinazione_ora'], 0, 5);
$ritorno_ora = substr($data['ritorno_ora'], 0, 5);

// fasce
$time_andata = strtotime($date." ".$data['prelevare_ora']);
$fascia_andata = $helper->getfasciaOrario($time_andata);
$time_ritorno = strtotime($date." ".$data['ritorno_ora']);
$fascia_ritorno = $helper->getfasciaOrario($time_ritorno);

$exclude_autista_andata = $exclude_autista_ritorno = false;

$exclude_militi1_andata  = array( $data['andata_milite2'] );
$exclude_militi2_andata  = array( $data['andata_milite1'] );
$exclude_militi1_ritorno = array( $data['ritorno_milite2'] );
$exclude_militi2_ritorno = array( $data['ritorno_milite1'] );

// get autisti e militi
$autisti_andata = $helper->getAutisti(true, $data['andata_autista'], $date, $fascia_andata, true, $exclude_autista_andata);
$militi1_andata = $helper->getMiliti(true, $data['andata_milite1'], $date, $fascia_andata, true, $exclude_militi1_andata);
$militi2_andata = $helper->getMiliti(true, $data['andata_milite2'], $date, $fascia_andata, true, $exclude_militi2_andata);
$autisti_ritorno = $helper->getAutisti(true, $data['ritorno_autista'], $date, $fascia_ritorno, true, $exclude_autista_ritorno);
$militi1_ritorno = $helper->getMiliti(true, $data['ritorno_milite1'], $date, $fascia_ritorno, true, $exclude_militi1_ritorno);
$militi2_ritorno = $helper->getMiliti(true, $data['ritorno_milite2'], $date, $fascia_ritorno, true, $exclude_militi2_ritorno);


/*** ELABORAZIONE DATI ***/

ob_start();

?>
	<div id='modifica-agenda'>
	
		<div class='row'>
		
			<div class='col col-md-6 text-center'>
				<h3>ANDATA</h3>
			</div>

			<div class='col col-md-6 text-center'>
				<h3>RITORNO</h3>
			</div>
						
		</div>
		
		<div class='row luoghi'>
		
			<div class='col col-md-4 text-center'>
				<?php echo $data['prelevare']; ?> 
			</div>
			<div class='col col-md-4 text-center'>
				<?php echo $data['destinazione']; ?>
			</div>
			<div class='col col-md-4 text-center'>
				<?php echo $data['ritorno']; ?>
			</div>
						
		</div>

		<div class='row orari'>
		
			<div class='col col-md-4 text-center'>
				<?php echo $prelevare_ora; ?>
			</div>
			<div class='col col-md-4 text-center'>
				<?php echo $destinazione_ora; ?>
			</div>
			<div class='col col-md-4 text-center'>
				<?php echo $ritorno_ora; ?>
			</div>

		</div>
				
		
		<div class='row'>
			<div class='col col-md-6'>
				<div class="form-group">
				  <label>Autista*</label>
				  <select name='andata_autista' id='andata_autista' class='select2 form-control'>
				  	<?php echo $autisti_andata; ?>
				  </select>
				</div>
			</div>
			<div class='col col-md-6'>
				<div class="form-group">
				  <label>Autista*</label>
				  <select name='ritorno_autista' id='ritorno_autista' class='select2 form-control'>
				  	<?php echo $autisti_ritorno; ?>
				  </select>
				</div>
			</div>
		</div>
		
		<div class='row'>
			<div class='col col-md-6'>
				<div class="form-group">
				  <label>Milite*</label>
				  <select name='andata_milite1' id='andata_milite1' class='select2 form-control'>
				  	<?php echo $militi1_andata; ?>
				  </select>
				</div>
			</div>
			<div class='col col-md-6'>
				<div class="form-group">
				  <label>Milite*</label>
				  <select name='ritorno_milite1' id='ritorno_milite1' class='select2 form-control'>
				  	<?php echo $militi1_ritorno; ?>
				  </select>
				</div>
			</div>
		</div>
		<div class='row'>
			<div class='col col-md-6'>
				<div class="form-group">
				  <label>Milite</label>
				  <select name='andata_milite2' id='andata_milite2' class='select2 form-control'>
				  	<?php echo $militi2_andata; ?>
				  </select>
				</div>
			</div>
			<div class='col col-md-6'>
				<div class="form-group">
				  <label>Milite</label>
				  <select name='ritorno_milite2' id='ritorno_milite2' class='select2 form-control'>
				  	<?php echo $militi2_ritorno; ?>
				  </select>
				</div>
			</div>
		</div>
		<div class='row'>
			<div class='col col-md-12'>
				<?php echo $bootstrap->checkbox("effettuato", $data['effettuato'], "Effettuato"); ?>
			</div>
		</div>
		
	</div>
<?php

$html = ob_get_contents();
ob_end_clean();


// output
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['html'] = $html;

echo json_encode($output);

	
?>
