<?php
error_reporting(E_ALL);
set_time_limit(0);

date_default_timezone_set('Europe/Rome');

include 'required/variables.php';
include 'required/functions.php';
include 'required/classes/cc_mysqli.class.php';

/** PHPExcel_IOFactory */
include 'required/phpExcelClass/PHPExcel/IOFactory.php';

// define excel file name
$excelFileName = 'xls/km-per-gommista.xls';

// load excel in class
$objPHPExcel = PHPExcel_IOFactory::load($excelFileName);

/** Memorizza contenuto in array 2-dim 1a chiave il numero di riga (quidni inizia con 1), 2a chiave colonna (quind AS, B, C...) **/
$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

if(!empty($sheetData)){
	
	$db = new cc_dbconnect(DB_NAME);
	
	// elimino prima riga (intestazione)
	array_shift($sheetData);
	
	$nrighe = count($sheetData);
	
	// loop righe foglio excel
	foreach($sheetData as $row){
		
		// sanify vars
		// template colonne : A: id_veicolo, B: targa, C: deposito (non lo uso), D: km, E: data/ora
		$id_veicolo 	= (int) $row['A'];
		$targa 		= $db->make_data_safe($row['B']);
		$km 			= (int) $row['D'];
		$data_excel 	= onlyDate($row['E']); // viene trasformato in yyyy-mm-gg
		echo "<strong>Dati foglio excel</strong><br>";
		echo "id_veicolo ".$id_veicolo."<br>\n";
		echo "targa ".$targa."<br>\n";
		echo "km ".$km."<br>\n";
		echo "data_excel ".$data_excel."<br>\n";
		
		if(empty($targa)) continue; // riga vuota?
		
		// recupero id targa in base a id_veicolo
		$id_targa = $db->get1value("id", DBTABLE_TARGHE, "WHERE id_veicolo = '".$id_veicolo."'");
		if(empty($id_targa)){
			echo "targa ".$id_veicolo." non trovata";
			die("");		
		}
		echo "id targa ".$id_targa."<br>\n<br>\n";
		
		// recupero tutti gli interventi con km = 0 in ordine crecente di id 
		// quindi dal più vecchio al più nuovo (quindi non solo quelli più vecchi della data excel)
		$interventi_km0 = $db->select_all(DBTABLE_SCHEDE_INTERVENTO, "WHERE targa = '".$id_targa."' AND km_non_rilevati = '1' ORDER BY id ASC");
		
		if($interventi_km0){ // ok uno o più intervento con km = 0
			
			// ricupero intervento con km che abbia data < a data primo intervento senza km 
			// (importante quest'ultima cosa, perché mi serve un km inferiore a quello del primo intervento senza km)
			$intervento_con_km = $db->get1row(DBTABLE_SCHEDE_INTERVENTO, "WHERE targa = '".$id_targa."' AND km_non_rilevati = '0' AND data < '".$interventi_km0[0]['data']."' ORDER BY id DESC LIMIT 1");
			
			// vediamo se c'è un intervento con km se no per ora die()
			if(empty($intervento_con_km)) die("nessun km trovato");
			
			echo "<strong>Calcoli</strong><br>";
			
			// verifico che non vi sia un cambio km tra data foglio excel e data primo intervento con km
			// Recupero eventuale record di cambio contachilometri con data maggiore dell'interveno con km
			$cambio_km = $db->get1row(DBTABLE_CAMBIO_KM, "WHERE targa = '".$id_targa."' AND data BETWEEN '".$intervento_con_km['data']."' AND '".$data_excel."'");
			if($cambio_km){
				echo "Ho cambio km - uso km e data tab cambio km<br>";
				// setto le variabile $km_start e $data_start presi da tab cambio km 
				$km_start = $cambio_km['km_nuovo'];
				$data_start = $cambio_km['data'];
			}else{
				echo "nessun cambio km - uso km e data da intervento con km<br>";
				// setto le variabile $km_start e $data_start presi da intervento con km 
				$km_start = $intervento_con_km['km'];
				$data_start = $intervento_con_km['data'];
			}
			echo "km_start (km intervento_con_km) ".$km_start."<br>\n";
			echo "data_start (data intervento_con_km) ".$data_start."<br>\n";

			// Calcolo differenza in gg tra data foglio excel e data intervento_con_km
			$giorni_range = diffInDays($data_excel, $data_start);
			echo "giorni_range (diff in gg tra data_excel e data_start) ".$giorni_range."<br>\n";
			
			// Calcolo differenza km tra data foglio excel e data intervento_con_km
			$km_totali = (int) $km - $km_start;
			echo "km_totali (diff in gg tra km foglio excel e km_start) ".$km_totali."<br>\n";
			
			// se la differenza di km è negativo vuol dire che l'intervento con km ha un km > di quello del foglio, per ora die
			if($km_totali < 0) die("Intervento ha km superiore a foglio excel");
			
			// calcolo media km giornaliera per questo veicolo in questo range di tempo
			$media = $km_totali / $giorni_range;
			echo "media (km_totali / giorni_range) ".$media."<br>\n";

			
			// inizio loop interventi a km 0
			foreach($interventi_km0 as $c => $intervento_km0){
				echo "<br><strong>schede con km a 0</strong><br>\n";
				
				// vediamo se la data dell'intervento a 0 è successiva ad eventuale cambio km, se si risetteo variabile $km_start
				if(!empty($cambio_km) and $intervento_km0['data'] > $cambio_km['data']){
					// uso come km a cui aggiungere per arrivare ai km del veicolo i km del cambio km
					$km_start = $cambio_km['km_nuovo'];
					$data_start = $cambio_km['data'];
				}else{
					// uso come km a cui aggiungere per arrivare ai km del veicolo i km dell'intervento con km
					$km_start = $intervento_con_km['km'];
					$data_start = $intervento_con_km['data'];
				}

				// Calcolo la differenza in giorni tra intervento con km e intervento del loop
				$giorni_diff = diffInDays($data_start, $intervento_km0['data']);
				echo "giorni_diff - diff in giorni tra intervento con km ($data_start) e intervento del loop (".$intervento_km0['data'].") = ".$giorni_diff."<br>\n";
				
				
				// Moltiplico i giorni_diff per la media giornaliera
				$km_diff = ceil($giorni_diff * $media);
				echo "km_diff -  moltiplicazione di giorni_diff ($giorni_diff) per la media giornaliera ($media) = ".$km_diff."<br>\n";
				
				
				// Sommo km_diff a km intervento precedente per ottenere km veicolo calcolato in data intervento
				$km_veicolo = $km_diff + $km_start;
				echo "km_veicolo - Sommo km_diff ($km_diff) a km intervento precedente ($km_start) = ".$km_veicolo."<br>\n";
				
				// update scheda intervento con km 0
				echo "<br><strong>Processo di update DB</strong><br>";
				echo "Aggiorno scheda intervento...<br>";
				if($db->update(DBTABLE_SCHEDE_INTERVENTO, array("km" => $km_veicolo, "km_non_rilevati" => '0', "update_km_date" => $data_excel), "WHERE id = '".$intervento_km0['id']."'")){
					echo " Ok!<br><br>";
				}else{
					echo " Errore!";
					echo "<br>".$db->getError("msg")." - ".$db->getquery();
					die();
				}
				
				// get records progressivo gomme
				$progressivo_gomme = $db->select_all(DBTABLE_PROGRESSIVO_GOMME, "WHERE scheda_intervento = '".$intervento_km0['id']."'");
				echo "Recupero progressivo gomme... ";
				if($progressivo_gomme){
					echo "ok!<br><br>Update progressivo gomme... ";
					
					// loop progressivo gomme
					foreach($progressivo_gomme as $pg){
						
						// update progressivo gomme
						if($db->update(DBTABLE_PROGRESSIVO_GOMME, array("km" => $km_diff, "km_veicolo" => $km_veicolo, "km_non_rilevati" => '0', "update_km_date" => $data_excel), "WHERE id='".$pg['id']."'")){
							echo $pg['id']."... ";
						}else{
							echo " Errore!";
							echo "<br>".$db->getError("msg")." - ".$db->getquery();
							die();
						}
												
					} // end foreach progressivo_gomme
					
				} // fine if($progressivo_gomme)				
				
			} // fine loop interventi km 0
			
		} // end if $interventi_km0
		
		/*********************************************************************************************************** 
		 *** Gestione controlli scaduti, ovvero se nei progressivi gomme relativo all'ultimo intervento effettuato *
		 *** (indifferentemente da km) ci sono dei campi km prox compilati e se con il caricamento di questo       *
		 *** foglio excel si evidenzia che sono già passati questi km scrivo in tab gomme_da_controllare           *                                          *
		 ***********************************************************************************************************/ 
		 echo "<br><br><strong>Gestione controlli scaduti</strong><br>";
		 
		 echo "Get id dell'ultimo intervento sulla targa in schede_intervento<br>";
		 $id_ultimo_intervento = $db->get1value("id", DBTABLE_SCHEDE_INTERVENTO, "WHERE targa = '".$id_targa."' ORDER BY id DESC LIMIT 1");
		 if($id_ultimo_intervento){
			 echo "Ok, trovato: ".$id_ultimo_intervento."<br>";
			 
			 // recupero progressivi gomme
			 echo "recupero progressivi gomme avente come scheda intervento = ".$id_ultimo_intervento."<br><br>";
			$progressivo_gomme = $db->select_all(DBTABLE_PROGRESSIVO_GOMME, "WHERE scheda_intervento = '".$id_ultimo_intervento."'");

			// se sono ultimo intervento a km 0 (il più recente) faccio controllo di prox km per vedere se ci sono overdue
			if($progressivo_gomme){
				echo "Ho progressivi, inizio loop<br>";

				foreach($progressivo_gomme as $pg){				
					echo "vedo se ho compilato prox_intervento_km... ";
					// se prox_intervento_km non è vuoto ci aggiungo $km_veicolo e vediamo se supera $km
					if(!empty($pg['prox_intervento_km'])){
						echo "yes: ".$pg['prox_intervento_km']."<br>";
						$prox = $pg['prox_intervento_km'] + $km_veicolo;
						echo "calcolo prossimo km ovvero prox_intervento_km (". $pg['prox_intervento_km'].") + km_veicolo (".$km_veicolo.") = ".$prox."<br>";
						if($prox < $km){
							echo "km foglio excel ($km) supera il valore di prossimo intervento ($prox), quindi aggiungo record in gomme_da_controllare.<br>";
							// ok questa gomma / veicolo dev'essere controllato scrivo in db
							$db->insert(DBTABLE_GOMME_DA_CONTROLLARE, array($id_targa, $pg['targa_gomma'], $data_excel, $prox, $km, $intervento_km0['id']), 
																	array("targa", "targa_x_gomme", "data", "prox", "km", "scheda"));
						}
					} // end if(!empty($pg['prox_intervento_km']))
					
				} // end foreach progressivo_gomme
				echo "<br>Fine loop foreach progressivo_gomme<br>";
				
			} // end if progressivo_gomme					
		 
		 } // end if($id_ultimo_intervento)
		 
	} // end loop righe foglio excel
	echo "<br>Fine loop righe foglio excel<br>";
		
} // fine if !empty sheetdata

function onlyDate($date){
	$date = trim($date);
	$exp = explode(" ", $date);
	$d = $exp[0];
	return cc_date_eu2us($d);
}

function diffInDays($high, $low){
	if(empty($high) or empty($low)) return 0;
	$h = strtotime($high);
	$l = strtotime($low);
	if(!$h or !$l) return false;
	$diff = abs($h-$l); // in seconds
	$days = floor($diff / (60*60*24));
	return $days;
}


?>