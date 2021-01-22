<?php
/*****************************************************
 * get_html_nuovo_modello                            *
 * Restituisce options con marche che hanno modelli  *
 * con determinate dimensioni                        *
 * IN: dim                                           *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

$dimensione = $db->make_data_safe($_POST['dimensione']);
$marca = $db->make_data_safe($_POST['marca']);

$options_marche = getSelectOptions("id", "marca", DBTABLE_GOMME_MARCHE, $marca, false, "", true);
$select_marche = "
    <select class='form-control' name='tuttelemarche' id='tuttelemarche' required>
    ".$options_marche."
    </select>
";

$text_modello 		= "<input type='text'  class='form-control' name='nuovomodello' id='nuovomodello' required value=''>";
$text_dimensione 	= "<input type='text'  class='form-control' name='nuovadimensione' id='nuovadimensione' readonly value='".$dimensione."'>";

$html = "
<form class='nosend'>
	<div class='row'>                
		<div class='col-md-12'>
			<div class='form-group'>
			  <label>Dimensione</label>
			  ".$text_dimensione."
			</div>
		</div>
	</div>
	<div class='row'>                
		<div class='col-md-12'>
			<div class='form-group'>
			  <label>Marca</label>
			  ".$select_marche."
			</div>
		</div>
	</div>
	<div class='row'>                
		<div class='col-md-12'>
			<div class='form-group'>
			  <label>Modello</label>
			  ".$text_modello."
			</div>
		</div>
	</div>
";
$html .= "<button id='saveNewModel' class='btn btn-success'><i class='fa fa-check'></i>&nbsp;&nbsp;".$_t->get('save')."</button>&nbsp;";
$html .= "<div id='resetNewModel' class='btn btn-danger'><i class='fa fa-times'></i>&nbsp;&nbsp;".$_t->get('cancel')."</div>";

// close form
$html .= "\n</form>";


// output
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['html'] = $html;
$output['title'] = "Inserisci nuovo modello pneumatico";

echo json_encode($output);
	
?>
