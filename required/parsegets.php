<?php
/*** TODO: TRANSFOM IN CLASS ***/
/*** Parse and sanitize get values. Also set default values for certain sistem variables like $pid, $_record and $_view ***/

// default values of system get values
$pid = 0;
$_record = 0;
$_action = "show"; // can also be insert, update, readonly. 
$_view = "html"; // can also be pdf, print or xml
$_token = ""; // token handling
$overwrite_gb2 = false; // the system uses standard page id for go back

$_js_gets = "";
$_js_gets_array = array();

// pass default values to js array
$_js_gets_array['pid'] .= $pid;
$_js_gets_array['r'] .= $_record;
$_js_gets_array['a'] .= $_action;
$_js_gets_array['v'] .= $_view;

// permitted actions (except html which is already default)
$_permitted_actions = array("insert", "update", "show", "readonly", "upload");

// permitted views (except html which is already default)
$_permitted_views = array("pdf", "print", "xml", "csv", "fullscreen");


if(!empty($_GET)){
	
	foreach($_GET as $_gkey=>$_gval){
		unset($_GET[$_gkey]); // not necessary anymore unset this get variable
		$_gkey = strtolower($db->make_data_safe($_gkey)); // sanitize key, always lowercase
		$_gval = trim($db->make_data_safe($_gval)); // sanitize value
		
		$_js_gets_array[$_gkey] = $_gval; // overwrite default value or add new value for js
		
		// logic for filter_
		if(substr($_gkey, 0, 7) == "filter_"){			
			$_gkey = substr($_gkey, 7);
			$_filter[$_gkey] = $_gval;			
		}else{
			// get internal variables to sanitized value
			switch($_gkey){
				case "pid": // page id
					$pid = (int) $_gval;
					break;
				case "r": // record
					//$_record = (int) $_gval;
					$_record = $_gval; // TODO parametro in config che forza id ad essere int oppure in tab pagine mettere se id è numerico o meno
					break;
				case "a": // action
					$_action = (in_array($_gval, $_permitted_actions)) ? $_gval : $_action;
					break;
				case "v": // view
					// set view, if no valid view is provided set default view (html)
					$_view = (in_array($_gval, $_permitted_views)) ? $_gval : $_view;
					break;
				case "t":
					// token
					$_token = (empty($_gval)) ? "" : $_gval;
					break;
				case "gb":
					// overwrite standard goback page id value
					$gb2 = (empty($_gval)) ? "" : $_gval;
					$overwrite_gb2 = true;
					break;
				default:
					$_GET[$_gkey] = $_gval; // leave get value, maybe dangerous
					break;
			}
			// all other get values are only sanitized, but pass...
			
		}
	}
}

unset($_POST); // no posts allowed by the way...

// create string to put in js script block
foreach($_js_gets_array as $_js_gets_key => $_js_gets_value){
	$_js_gets .= $_js_gets_key." = \"".$_js_gets_value."\";\n";
}



?>