<?php

include_once '_head.php';

$safevalues = $db->make_data_safe($_POST['values']);
if(empty($safevalues)){
    echo json_encode($output);
    die();
}

$db->update(LOGIN_TABLE, $safevalues, "WHERE id = '".$_SESSION['login_id']."'");

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";	


echo json_encode($output);


