<?php
/****************************************************
 * SAVE TO DATABASE USING SWITCH SCRIPT AND         *
 * SUBMISSION OF FROM USING POST (NON-AJAX METHOD)  *
 * TODO: RESGISTRAZIONE UTENTE INSERT E UPDATE      *
 ****************************************************/
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE);

//if(DEBUG) ini_set("display_errors", "1");

include_once 'variables.php';
include_once 'functions.php';
include_once 'classes/cc_mysqli.class.php';
include_once 'classes/cc_translations.class.php';
include_once 'classes/cc_user.class.php';
include_once 'classes/user_cookie.class.php';
include_once 'classes/cc_errorhandler.class.php';

// db connection
$db = new cc_dbconnect(DB_NAME);

// set error object
error_reporting(E_ALL ^ E_NOTICE);
$_errorhandler = new cc_errorhandler();
set_error_handler(array($_errorhandler, 'regError'), E_ALL ^ E_NOTICE);

// load configs from DB
$_configs = $db->key_value("param", "value", DBTABLE_CONFIG);

if(!empty($_configs['debug'])) ini_set("display_errors", "1");

// Set constants based on config values
define ('MAINTENANCE', $_configs['maintenance_mode']);
if($_configs['debug'] == '0'){ define ('DEBUG', false); }else{ define ('DEBUG', true); } 
if($_configs['isdemo'] == '0'){ define ('ISDEMO', false); }else{ define ('ISDEMO', true); } 


// User and translation. 
$_user = new cc_user($_SESSION['login_id'], $db);
$_t = new cc_translate($db, "write2db", $_user->getLanguage()); // create instance of translate class
//setlocale(LC_ALL, $_user->getLanguageCode()); not used anymore because setlocale is set in user class



// Default variables
$reorder_data = false; // if true, a ordinal number will be set in db table; the field _order must exist
$neworder = 0; // new order value (for insert)
$tabrif = array();
$recordid = 0;
$quantity = '1'; // for bulk insert
$access_control = false; // if true the main table must have "_user" field in which to write the id of the user that inserts the record

$switch_categoria = 0;
$multival = array();
$aggregatore = false;
$first = true; // first table of loop...
$extra_url_query_params = array();
$ajax = false;
$output = array();
$skip_default = false; // if set to true (in first switchg file) all the standard insert/update procedure is skipped
$mute_message = false; // if set to true no success message will be send back to page

// detect if ajax call or not
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$output['result'] 		= false;
	$output['error'] 		= $_t->get('nopost'); // title of modal box
	$output['msg'] 			= $_t->get('nopost_message'); // message inside modal box
	$output['errorlevel'] 	= "danger"; // color of modal box
	$output['qry'] 			= "";
	$output['dbg'] 			= "";
	$ajax = true;
	$return_values = array();
}

// if no post is sent (probably called directly) send user to cpanel.php default
if(empty($_POST)){
	errorOutput($_t->get("nopost"), $_t->get("nopost_message"), HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL);
}

/********************************************************
 * INTERCEPT NON-FORM DATA (page, action, record, view) *
 ********************************************************/

// if no pid, set error and send user back to cpanel.php 
if(empty($_POST['pid'])){
	errorOutput($_t->get("nopage"), $_t->get("nopage_message"), $_SERVER['HTTP_REFERER']);
}else{
	// sanitize pid and delete it from post array
	$pid = (int) $_POST['pid'];
	unset($_POST['pid']);
}

// if no action, set error and send user back to cpanel.php 
if(empty($_POST['action'])){
	errorOutput($_t->get("write2db"), $_t->get("noaction"), $_SERVER['HTTP_REFERER']);
}else{
	// if action is ether insert or update pass on and delete it from post array
	if(strtolower($_POST['action']) == "insert" or strtolower($_POST['action']) == "update"){
		$action = strtolower($_POST['action']);
		unset($_POST['action']);
	}else{
		// action not permitted, set error and send user back		
		errorOutput($_t->get("write2db"), sprintf($_t->get('wrongaction'), $_POST['action']), $_SERVER['HTTP_REFERER']);
	}
}

// set quantity for insert
if(!empty($_POST['_qta'])){
	$quantity = (int) ($action == "insert") ? $_POST['_qta'] : 1;
	unset($_POST['_qta']);
}


// if action is update and no record is set, populate error var and send user back
if($action == "update"){
	if(empty($_POST['record'])){
		errorOutput($_t->get("write2db"), $_t->get("no-update-record"), $_SERVER['HTTP_REFERER']);
	}else{
		// Set recordid for update
		$recordid = $db->make_data_safe($_POST['record']); 
	}
}
unset($_POST['record']);

// if media references are passed memorize them in var
$mediafiles = (empty($_POST['media'])) ? array() : $_POST['media'];
unset($_POST['media']);

// which save button has been pressed
$save = $_POST['save'];
unset($_POST['save']);

unset($_POST['view']); // don't need it
$view = "html"; // always?

unset($_POST['_wysihtml5_mode']); // don't need it



/************************************************************
 * END INTERCEPT NON-FROM DATA (page, action, record, view) *
 ************************************************************/


// get page name
$pd = $db->get1row(DBTABLE_PAGES, "WHERE id = '".$pid."'");



// if page is not found send error
if(!$pd){
	errorOutput($_t->get("write2db"), sprintf($_t->get('pagenotset_message'), $pid), $_SERVER['HTTP_REFERER']);
}else{
	// set filename
	$page = $pd['file_name'].".php";
	$post_page = $pd['file_name']."_post_actions.php"; // for action to perform after insert / update
}

// new 2019-09 custom primary key used for now in returnig to page. primary key is usually id, but now it can be any field
$primary_key = @$pd['primary_key']; // the primary of the main table used for recordid, usually id and numeric, but can be customized
if(empty($primary_key)) $primary_key = 'id'; // if the page doesn't have a primary key set (or the field is absent in table) set to default 'id'
if($primary_key == 'id') $recordid = (int) $recordid; // if primary_key is default id recordid is presumed being int


/**********************
 * SANITISE FORM DATA *
 **********************/
// make remaining posts safe
if($save == "ajax"){
	// if inline data is passed populate safevalues from $_POST['inlinedata'] instread of remaining $_POST
	$safevalues = $db->make_data_safe($_POST['inlinedata']);
	unset($_POST['inlinedata']);
	
}else{
	$safevalues = $db->make_data_safe($_POST); 
}


// save the safevalues array in session var in case i need to send it back (for now only in case of insert error)
$_SESSION['savevalues'] = $safevalues; 


/***********************************************
 * GET SWITCH FILE                             *
 * Switch script defines the array $tables and *
 * all the specific logic of the page          *
************************************************/

// include switchfile if it exists, else send error
if(file_exists("../calls/save/".$page)){
	include_once "../calls/save/".$page;
}else{
	errorOutput($_t->get("write2db"), sprintf ($_t->get('noswitch_file_message'), $page), $_SERVER['HTTP_REFERER']);
}

if(!$skip_default){

	/********************************
	 * PARSE VARS FROM SWITCH FILE  *
	*********************************/
	
	// $tables must always be an array
	if(!is_array($tables)) $tables = array("0" => $tables);
	
	// $safevalues must always be a multi-dim array (ex. $safevalues[0]['name'] = "foe")
	if(!is_array(reset($safevalues))) $safevalues = array("0" => $safevalues);
	
	// if array $rec is not defined in switch script create default one
	//if(empty($recs)) $recs[0]['id'] = $recordid; // if action = insert this will be 0 at this point
	if(empty($recs)) $recs[0][$primary_key] = $recordid; // if action = insert this will be 0 at this point
	
	// if column for order is not defined in switch script create default one
	if(empty($order_by)) $order_by[0] = "ord";
	
	
	/*******************************
	 * START INSERT / UPDATE LOOPS *
	 *******************************/
	
	// loop for multiple insert of same record
	for($qtaloop = 0; $qtaloop < $quantity; $qtaloop++){
		
		// loop tables in which to write 
		foreach($tables as $k=>$tab){
			
			/*** INSERT ***/
			if ($action == "insert"){
				
				
				/* if primary_key is different from id check if key is unique */
				if($primary_key != 'id' and $first){
					
					$check_pk = $db->get1row($tab, "WHERE ".$primary_key." = '".$safevalues[$k][$primary_key]."'");
					if($check_pk){
						// key already exists return with error
						errorOutput("RECORD NON INSERITO: Valore per <em>".$primary_key."</em> esiste già!", "Il valore del campo <strong>".$primary_key."</strong> dev'essere univoco, esiste invece già un record con valore '<strong>".$safevalues[$k][$primary_key]."</strong>'...", HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=".$view."&a=insert");
					}
					
				}
				
				// define array for fields and values
				$dbfields = $dbvalues = array();
				
				if($reorder_data){
					// get max value of order;
					$qry_max = "SELECT MAX(".$order_by[$k].") FROM ".$tab; 
					$result_max = mysql_query($qry_max);
					$output_max = mysql_fetch_row($result_max);
					$maxorder = $output_max[0];
					$neworder = $maxorder+1;
				}
				
				/* used to link multiple tables. tabrif holds name of the tab field that links 
				the secondary tables to the primary
				(p.e. $tabrif[1] = "customer", where 1 is the numeric id of the table and 
				"customer" is the fields that links this table to the main table id) */
				if(!empty($tabrif[$k])){
					$dbfields[0] = $tabrif[$k];
					$dbvalues[0] = $recordid;
				}
				
				/***************************************
				 * Start constructing the insert query *
				 ***************************************/
				
				$i = 1; // progessive counter for field and values array
				// get al values of this table and loop them
				foreach ($safevalues[$k] as $key=>$value){
					
					$dbfields[$i] = $key; // set field name 
					
					// if the multival var is set for this table 
					// Insert multiple rows in a (non-main) table
					if($multival[$k]){ // ex. $multival['r'] = "richiedente"
						
						$n = count($safevalues[$k][$multival[$k]]); // $safevalues[$k]["richiedente"] is an array
						
						if($multival[$k] == $key){ // key is richiedente...
							$a=0;
							// loop array of values
							foreach($value as $vv){
								$mval[$a][$i] = $vv; // p.e. $mval[0][1] = "Peter", $mval[1][1] = "Brian", $mval[2][1] = "Stevie"...
								$a++;
							}
						}else{ // key is not richiedente...
							for($a=0; $a<$n; $a++){
								// if first fields of previous table is not empty
								// set mval for first field = value of first field previous table
								// else this remains empty / null
								if(!empty($dbvalues[0])) $mval[$a][0] = $dbvalues[0]; // actually recordid (see line 214)
								$mval[$a][$i] = $value; // does not override row above because $i starts with 1
							}
						}
					}else{
						$dbvalues[$i] = $value;
					}
					$i++;
				}
							
				
				/*************************************
				 * End constructing the insert query *
				 *************************************/
				
				// if this is the main table and access_control is true the user id will be written
				// in the db table (field ("_user") must exist!)
				if($first and $access_control){
					$dbfields[] = "_user";
					$dbvalues[] = $_SESSION['login_id'];
				}
				
				// add inserted by user record if this field is defined in table
				$tabfields = $db->get_column_names($tab);
				if(in_array(INSERTED_BY_FIELD, $tabfields)){
					$dbfields[] = INSERTED_BY_FIELD;
					$dbvalues[] = $_SESSION['login_id'];
				}
	
				
				// set new order value
				if($reorder_data){
					$dbfields[] = "_order";
					$dbvalues[] = $neworder;
				}
				
				// Insert multiple rows in a (non-main) table
				if($multival[$k]){
					
					// mval structure: $mval[0][1] = "Peter";
					foreach($mval as $dbvalues){ 
						// optimisation: create one multi value insert query instead of this.
						$result = $db->insert($tab, $dbvalues, $dbfields); // $dbvalues = array actually, maybe should use $dbvalues[$i]
					}
					if(!$result){
						$err = $db->getError();
						$msg[] =  __LINE__."Errore durante la scrittura in DB.<br />\nQuery non eseguita:<br />\n".$err['qry']."<br />\n<br />\nError " . $err['num'] . ": " . $err['msg'];;
					}
					
				}else{
					// normal insert
				

					$result = $db->insert($tab, $dbvalues, $dbfields);
					if(!$result){
						// abort script and go back to cpanel dragging along $_SESSION['savevalues'] to repopulate form
						$err = $db->getError(); // Get error from cc_dbconnect class


						errorOutput($_t->get("write2db"), sprintf ($_t->get('error_insert'), $err['msg']), HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=".$view."&a=insert");
					}
				}
				
				// get insert id and set recordid for other tables and for feedback to JS 
				if($first){
					if($result){
						$recordid = ($primary_key == 'id') ? $db->get_insert_id() : $safevalues[$k][$primary_key];
					}else{
						$recordid = 0;
					}
				}
			
			/*** UPDATE ***/	
			}else if ($action == "update"){
	
				$r = $recs[$k]; // es. $recs['r']['id'] = $_POST['record'];
				$rid = key($r); // es. 'id'--------^^
				$rval = $r[$rid]; // es. $_POST['record']-----------^^
				
				if(is_array($rval)){
					foreach($rval as $rrval){
						$result = $db->update($tab, $safevalues[$k], "WHERE ".$rid."=\"".$rrval."\"");
						if(!$result){
							$err = $db->getError();
							errorOutput($_t->get("write2db"), sprintf($_t->get('error_update'), $err['msg']), HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=".$view."&a=insert", false);
							
						}
					}
					
				}else{
	
					// add updated by user record if this field is defined in table
					$tabfields = $db->get_column_names($tab);
					if(in_array(UPDATED_BY_FIELD, $tabfields)){
						$safevalues[$k][UPDATED_BY_FIELD] = $_SESSION['login_id'];
					}
					
					
					$result = $db->update($tab, $safevalues[$k], "WHERE ".$rid."=\"".$rval."\"");

	
					if(!$result){
						$err = $db->getError();
						errorOutput($_t->get("write2db"), sprintf($_t->get('error_update'), $err['msg']), HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=".$pid."&v=".$view."&a=insert");
					}
				}
				
						
				//if($first) $recordid = ($result) ? $recs[$k][$rid] : 0;
							
			} // end if ($action)
			
			$first = false; // finished working on main table
			
		} // end foreach
	}// end for qtaloop
}// end if skip_default

if(file_exists("../calls/save/".$post_page)){
	include_once "../calls/save/".$post_page;
}

// update media records if any
if(!empty($mediafiles)){
	$order = $db->get_max_row("order", DBTABLE_MEDIA, "WHERE page = '".$pid."' AND record = '".$recordid."'");
	if(empty($order)) $order = 0;
	foreach($mediafiles as $mediafile){
		$order++;
		$db->update(DBTABLE_MEDIA, array("record" => $recordid, "order" => $order), "WHERE id = '".$mediafile."'");
	}
}


// ok finished let's see where to send user.
switch($save){
	case "close":
		$goto = PANEL."?pid=".$pd['modify_page']."&v=".$view; // url of table
		if(!empty($extra_url_query_params)){
			foreach($extra_url_query_params as $euqp_key => $euqp_val){
				$goto .= "&".$euqp_key."=".$euqp_val;
			}
		}
		break;
	case "new":
		$goto = PANEL."?pid=".$pid."&v=".$view."&a=insert"; // url of module for new insert
		break;
	default:
		$goto = PANEL."?pid=".$pid."&v=".$view."&a=update&r=".$recordid; // url of module with record
		break;
}

if(empty($_SESSION['error_message']) and !$mute_message){
	$_SESSION['success_title'] 		= ($action == "insert") ? $_t->get("insert_ok_title") : $_t->get("update_ok_title");
	$_SESSION['success_message'] 	= ($action == "insert") ? $_t->get("insert_ok_message") : $_t->get("update_ok_message");
}

unset($_SESSION['savevalues']); // don't need it...

// if ajax return result, else redirect
if($save == "ajax"){
	$output['result'] = true;
	$output['msg'] = $_SESSION['success_message'];
	$output['error'] = "";
	$output['values'] = $return_values;
	unset($_SESSION['success_title']);
	unset($_SESSION['success_message']);
	echo json_encode($output);

}else{
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.$goto);
	exit;
}

/*** END OF PROCEDURE ***/

/*** FUNCTIONS ***/
function errorOutput($title, $message, $url, $die = true){
	global $ajax, $output;
	if($ajax){
		$output['error'] 		= $title; // title of modal box
		$output['msg'] 			= $message; // message inside modal box
		$output['errorlevel'] 	= (empty($url) or ($url != "danger" and $url != "warning")) ? "danger" : $url; // color of modal box
		if($die){
			echo json_encode($output);
			die();
		}
	}else{
		$_SESSION['error_title'][] = $title; 
		$_SESSION['error_message'][] = $message; 
		if($die){
			header('location: '.$url);
			exit;
		}
	}
}

function splitValues($safevalues){
	// split values for the two tables.
	foreach($safevalues as $k => $v){
		$split = explode("|", $k);
		$sv[$split[0]][$split[1]] = $v;
	}
	return $sv;
	
}
?>
