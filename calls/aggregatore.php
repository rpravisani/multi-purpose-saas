<?php
/***  AGREGATORE PER RICERCA IMMOBILI ***/


function aggrega($immobile, $azione, $lingua_def=1){
	global $db;
	$lingue = $db->key_value("id", "sigla", TABELLA_LINGUE);
	
	if($azione == "update"){
		$check = $db->get1value("immobile", TABELLA_RICERCA_LIBERA, "WHERE immobile='".$immobile."'");
	}else{
		$check = true;
	}

	foreach($lingue as $lingua=>$sigla){
		
		//$sigla = cc_get1value("sigla", TABELLA_LINGUE, "WHERE id='".$lingua."'");
		if(strtoupper($sigla) == "EN"){
			$sigla = "en-GB";
		}else{
			$sigla = strtolower($sigla)."-".strtoupper($sigla);
		}
		$langinifile = "../../language/".$sigla."/".$sigla.".com_ccimmobili.ini";
		$trad = new cc_translate($langinifile);
		
		if($lingua != $lingua_def){
			$tipologie =  $db->key_value ("riga", "traduzione", TABELLA_TRADUZIONI, "WHERE tabella='".TABELLA_TIPOLOGIA."' AND campo='tipologia' AND lingua = '".$lingua."'");
			$condizioni =  $db->key_value ("riga", "traduzione", TABELLA_TRADUZIONI, "WHERE tabella='".TABELLA_CONDIZIONI."' AND campo='condizione' AND lingua = '".$lingua."'");
			$categorie =  $db->key_value ("riga", "traduzione", TABELLA_TRADUZIONI, "WHERE tabella='".TABELLA_CATEGORIE."' AND campo='categoria' AND lingua = '".$lingua."'");
			$riscaldamenti =  $db->key_value ("riga", "traduzione", TABELLA_TRADUZIONI, "WHERE tabella='".TABELLA_RISCALDAMENTI."' AND campo='riscaldamento' AND lingua = '".$lingua."'");
			//$descrizioni =  $db->key_value ("riga", "traduzione", TABELLA_TRADUZIONI, "WHERE tabella='immobili' AND campo='descrizione' AND lingua = '".$lingua."'");
			$descrizione =  $db->get1value("traduzione", TABELLA_TRADUZIONI, "WHERE tabella='".TABELLA_IMMOBILII."' AND campo='descrizione' AND lingua = '".$lingua."' AND riga='".$immobile."'");
			
			$qry = "SELECT 
						i.id,
						i.rif, 
						i.balconi_nr, 
						i.terrazzi_nr, 
						i.giardino_mq, 
						i.posto_auto, 
						i.posto_moto,
						i.garage_nr,
						i.cantine_nr,
						i.airco, 
						i.mq, 
						i.num_vani,
						i.piano, 
						i.ascensore, 
						i.esposizione_solare,   
						l.Comune, 
						l.Provincia, 			
						i.riscaldamento AS 'riscaldamento_id',
						i.tipologia AS 'tipologia_id',			
						i.categoria AS 'categoria_id',						
						i.contratto AS contratto_id,			
						n.nazione,			
						l.Regione
					FROM 
						jos_cc_immobili AS i, 
						jos_cc_localita AS l, 
						jos_cc_nazioni AS n 
					WHERE 
						i.localita = l.id 
					AND 
						i.nazione = n.id 
					AND 
						i.id = ".$immobile;
			
		}else{
			
			$qry = "SELECT 
						i.id, 
						i.rif, 
						i.descrizione, 
						c.categoria, 
						x.contratto,			
						t.tipologia, 			
						i.num_vani, 
						i.mq, 
						r.riscaldamento,
						i.airco, 
						i.piano, 
						i.ascensore, 
						i.balconi_nr, 
						i.terrazzi_nr, 
						i.giardino_mq, 
						i.cantine_nr,						
						i.posto_auto, 
						i.posto_moto,						
						i.garage_nr,
						i.esposizione_solare, 
						l.Comune, 
						l.Provincia, 			
						n.nazione,			
						l.Regione
					FROM 
						jos_cc_immobili AS i, 
						jos_cc_localita AS l, 
						jos_cc_riscaldamenti AS r, 
						jos_cc_tipologie AS t, 
						jos_cc_categorie AS c, 
						jos_cc_contratti AS x, 
						jos_cc_nazioni AS n 
					WHERE 
						i.localita = l.id 
					AND 
						i.riscaldamento = r.id 
					AND 
						i.tipologia = t.id 
					AND 
						i.categoria = c.id 
					AND 
						i.contratto = x.id 
					AND 
						i.nazione = n.id 
					AND 
						i.id = ".$immobile;
		} // END IF 
		
		
		$result = $db->fetch_array($qry);
		if ($result){
			
			foreach($result as $row){
				$stringa = "";
				$elems = array();
			
				foreach($row as $k=>$v){
					//$v = strtolower($v);
					$v = $db->make_data_safe($v);
					switch($k){
						case "id":
							$immobile = $v;
							if($lingua != $lingue_def) $elems[] = $descrizione;
							break;
						case "tipologia_id":
							if(!empty($tipologie[$v])) $elems[] = $tipologie[$v];
							break;
						case "condizione_id":
							if(!empty($condizioni[$v])) $elems[] = $condizioni[$v];
							break;
						case "categoria_id":
							if(!empty($categorie[$v])) $elems[] = $categorie[$v];
							break;
						case "riscaldamento_id":
							if(!empty($riscaldamenti[$v])) $elems[] = $riscaldamenti[$v];
							break;
						case "num_vani":
							if($v == 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_ROOM");
							}else if($v > 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_ROOMS");
							}
							break;
						case "mq":
							if($v != 0) $elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_SQM");
							break;
						case "airco":
							if($v != 0) $elems[] = $trad->_translate("COM_CCIMMOBILI_AIRCO");
							break;
						case "piano":
							if($v != 0) $elems[] = $v."° ".$trad->_translate("COM_CCIMMOBILI_WORD_LEVEL");
							break;
						case "ascensore":
							if($v == 1) $elems[] = $trad->_translate("COM_CCIMMOBILI_ELEVATOR");
							break;
						case "balconi_nr":
							if($v == 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_BALCONY");
							}else if($v > 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_BALCONIES");
							}
							break;
						case "terrazzi_nr":
							if($v == 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_TERRACE");
							}else if($v > 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_TERRACES");
							}
							break;
						case "cantine_nr":
							if($v == 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_CELLAR");
							}else if($v > 1){
								$elems[] = $v." ".$trad->_translate("COM_CCIMMOBILI_WORD_CELLARS");
							}
							break;
						case "giardino_mq":
							if($v != 0) $elems[] = $trad->_translate("COM_CCIMMOBILI_WORD_GARDEN");
							break;
						case "posto_auto":
							if($v != 0) $elems[] = $trad->_translate("COM_CCIMMOBILI_WORD_CARPARKING");
							break;
						case "posto_moto":
							if($v != 0) $elems[] = $trad->_translate("COM_CCIMMOBILI_WORD_MOTORPARKING");
							break;
						case "garage_nr":
							if($v != 0) $elems[] = $trad->_translate("COM_CCIMMOBILI_WORD_GARAGE");
							break;
						case "esposizione_solare":
							if(!empty($v)){
								$esp = explode("-", $v);
								foreach($esp as $sol) {
									$elems[] = $trad->_translate("COM_CCIMMOBILI_WORD_EXPOSURE")." ".$sol;
								}								
							}
							break;						
						default:
							$elems[] = $v;
							break;
					}
				}
				
				$stringa = implode(" ", $elems);
				$stringa = trim($stringa);
				
				if($azione == "insert" or !$check){
					
					$qry_ins = "INSERT INTO ".TABELLA_RICERCA_LIBERA." (immobile, meta, lingua) VALUES (\"".$immobile."\", \"".$stringa."\", \"".$lingua."\")";
				}else if($azione == "update"){
					$qry_ins = "UPDATE ".TABELLA_RICERCA_LIBERA." SET meta = \"".$stringa."\" WHERE immobile='".$immobile."' AND lingua='".$lingua."'";
				}else{
					die("ERR:Operazione aggregazione non consentita o contemplata");
				}
				if(!$db->execute_query($qry_ins)){
					$err[] = "<br><strong>Errore: ".$db->getError("msg").":<br>\n".$db->getQuery()."</strong><br><br>";
				}
			}
			
		}else{
			// do nothing
			
			return mysql_error()."<br>\n".$qry;
		}
	} // end foreach $lingue
	if(empty($err)){
		return true;
	}else{
		die(implode("\n", $err));
	}
}




?>