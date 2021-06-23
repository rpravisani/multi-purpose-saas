<?php
defined('_CCCMS') or die;
/*********************************************************
 *** MODEL                                             ***
 *** filename: profile.php                             ***
 *** Manage your profile data                          ***
 *********************************************************/

// get data if $_record is not empty
$_data = $db->get1row(LOGIN_TABLE, "WHERE id = '".$_SESSION['login_id']."'");
$subscription = $db->get1value("name", "subscription_types", "WHERE id = '".$_data['subscription_type']."'");

$avatar = (empty($_data['avatar'])) ? "generic-user.png" : $_data['avatar'];

$name_array = array();
if(!empty($_data['name'])) $name_array[] = $_data['name'];
if(!empty($_data['surname'])) $name_array[] = $_data['surname'];
$displayname = implode(" ", $name_array);

// TODO: Cambaire subscription type con user type
$role = ($_data['subscription_type'] == 0) ? "Superadmin" : $db->get1value("name", DBTABLE_SUBSCRIPTION_TYPES, "WHERE id = '".$_data['subscription_type']."'");

// TODO: Cambiare come viene convertito data: in base a apese
$subscription_date = cc_date_us2eu($_data['subscription_date']);

$last_active = $db->get1value('login_datetime', DBTABLE_ACCESS_LOGS, "WHERE user = '".$_SESSION['login_id']."' AND id < ".$_SESSION['access_log_id']." ORDER BY `last_active` DESC");
$last_active_date = cc_date_us2eu(substr($last_active, 0, 10));
$last_active_time = substr($last_active, 11, 5);
$last_active = $last_active_date." ".$last_active_time;

$args_pwd = array(
    "type" => "password", 
	"label" => "Nuova Password", 
	"name" => "password", 
	"value" => "", 
	"required" => true, 
	"readonly" => false,
    "help" => "Almeno 8 caratteri, di cui almeno una maiuscola, un numero ed un simbolo"
);

$args_pwd_repeat = array(
    "type" => "password", 
	"label" => "Ripeti Password", 
	"name" => "repeat-password", 
	"value" => "", 
	"required" => true, 
	"readonly" => false,
    "help" => ""
);




?>