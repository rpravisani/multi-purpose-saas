<?php
defined('_CCCMS') or die;
/*****************************************
 *** MODEL                             ***
 *** filename: user-edit.php           ***
 *** Inserisci e modifica utenti       ***
 *****************************************/

// get data if $_record is not empty
if(!empty($_record)){
	// date the data from the table
	$_data = $db->get1row(LOGIN_TABLE, "WHERE id='".$_record."'");
	
	unset($_data['password']);
	$stid 	= (int) $_data['subscription_type'];
	$pwd_mandatory = false;
	$pwd_title = "Lasciare vuoto per mantenere password vecchia";
	
	$boxtitle = "Modifica utente";
	
	$didascalia_tipo_utente =  $db->get1value("description", "subscription_types", "WHERE id = '".$stid."'");
	
}else{
	
	$stid = '0';
	$pwd_mandatory = true;
	$pwd_title = "Inserisci password...";
	$boxtitle = "Inserisci un nuovo utente";
	$didascalia_tipo_utente = "";
}


/*** CONFIG CAMPI ***/
$arg_nome = array(
	"label" => "Nome", 
	"name" => "name", 
	"value" => $_data['name'], 
	"required" => true, 
	"tabindex" => 1
);

$arg_cognome = array(
	"label" => "Cognome", 
	"name" => "surname", 
	"value" => $_data['surname'], 
	"required" => false, 
	"tabindex" => 2
);


// get list of subscription types
//$subscription_types = getSelectOptions("id", "name", DBTABLE_SUBSCRIPTION_TYPES, $stid, false, "WHERE active = '1' AND id != '1'", true);

$subtypes = $db->select_all("subscription_types", "WHERE active = '1' AND id > '1'");
$subscription_types = "";
foreach($subtypes as $subtype){
	$selected = ($subtype['id'] == $stid) ? "selected='selected'" : "";
	
	$subscription_types .= "<option data-description='".$subtype['description']."' value='".$subtype['id']."'>".$subtype['name']."</option>\n";
}


$arg_ruolo = array(
	"label" => "Ruolo", 
	"name" => "subscription_type", 
	"options" => $subscription_types, 
	"required" => true, 
	"tabindex" => 5
);

$arg_email = array(
	"label" => "Email", 
	"name" => "email", 
	"value" => $_data['email'], 
	"required" => true, 
	"tabindex" => 6
);

$arg_tel = array(
	"label" => "Telefono", 
	"name" => "telephone", 
	"value" => $_data['telephone'], 
	"required" => false, 
	"tabindex" => 7
);

$arg_username = array(
	"label" => "Nome utente", 
	"name" => "username", 
	"value" => $_data['username'], 
	"required" => true, 
	"tabindex" => 3
);

$arg_password = array(
	"label" => "Password", 
	"name" => "password", 
	"value" => $pwd, 
	"required" => $pwd_mandatory, 
	"placeholder" => $pwd_title, 
	"tabindex" => 4
);




/*** AVATARS ***/
$avatar_path = FILEROOT."avatars/";
$avatar_options = "";
$files_in_avatar_folder = scandir($avatar_path);
if(!empty($files_in_avatar_folder)){
	foreach($files_in_avatar_folder as $file_in_avatar_folder){
		if($file_in_avatar_folder == "." or $file_in_avatar_folder == ".." ) continue;
		$fiaf = explode(".", $file_in_avatar_folder);
		$ext = end($fiaf);
		if($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg'){
			$selected = ($_data['avatar'] == $file_in_avatar_folder) ? "selected" : "";
			$avatar_options .= "<option ".$selected." value='".$file_in_avatar_folder."'>".$file_in_avatar_folder."</option>\n";
		}
	}
}




?>