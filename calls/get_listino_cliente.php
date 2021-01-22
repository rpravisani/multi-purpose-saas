<?php
/*****************************************************
 * recupera eventuale listino cliente    
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$cliente = (int) $db->make_data_safe($_POST['cliente']);

$listino = $db->get1row("data_listini", "WHERE cliente = '".$cliente."' AND active = '1' ORDER BY ts DESC");

$output['listino'] = (empty($listino)) ? 0 : $listino['id'];
	
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";

echo json_encode($output);

	
?>
