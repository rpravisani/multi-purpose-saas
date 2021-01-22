<?php

class elenco_utenti_interni_table extends table_engine{
		
	// formatXxx functions must have visibility set 'protected' to work
	protected function formatTipoUtente($value, $params){
		
		$split = explode("|", $value);
		
		$nome = trim(strtolower($split[0]));
		
		switch($nome){
			case 'operatore':
				$class = "yellow";
				break;
			case 'corriere':
				$class = "light-blue";
				break;
			case 'acquisti':
				$class = "green";
				break;
			default:
				$class = "gray";
				break;				
		}

		$out['value'] = "<span data-toggle='tooltip' title='".$split[1]."' class='badge bg-".$class."'>".$split[0]."</span>";		

		$out['attr']  = "align='center'";
		
		return $out;
	}


}
?>