<?php
/***
Class that handles the errors. The class is istanced in required.php and 
set_error_handler in there calls the regError function, 
which create a string and saves it in the public variable $errors. 
Errors are recallable with the getErrors() function 
***/

include_once 'cc_phpbootstrap.class.php'; // in case it wasn't included before (use some if it's functions in this class)

class cc_errorhandler{
	
	public $errors = array(), $bootstrap;
	
	public function __construct(){
		$this->bootstrap = new phpbootstrap();
	}
	
	// Fetches the error php throws out and creates string which is saved in the $errors var. 
	public function regError($errno, $errstr, $errfile, $errline){
		
		global $db;
		
		switch($errno){
			case E_ERROR:               $severity = "E_ERROR"; break;
			case E_WARNING:             $severity = "E_WARNING"; break;
			case E_PARSE:               $severity = "E_PARSE"; break;
			case E_NOTICE:              $severity = "E_NOTICE"; break;
			case E_CORE_ERROR:          $severity = "E_CORE_ERROR"; break;
			case E_CORE_WARNING:        $severity = "E_CORE_WARNING"; break;
			case E_COMPILE_ERROR:       $severity = "E_COMPILE_ERROR"; break;
			case E_COMPILE_WARNING:     $severity = "E_COMPILE_WARNING"; break;
			case E_USER_ERROR:          $severity = "E_USER_ERROR"; break;
			case E_USER_WARNING:        $severity = "E_USER_WARNING"; break;
			case E_USER_NOTICE:         $severity = "E_USER_NOTICE"; break;
			case E_STRICT:              $severity = "E_STRICT"; break;
			case E_RECOVERABLE_ERROR:   $severity = "E_RECOVERABLE_ERROR"; break;
			case E_DEPRECATED:          $severity = "E_DEPRECATED"; break;
			case E_USER_DEPRECATED:     $severity = "E_USER_DEPRECATED"; break;
		}
		
		$string = $severity." : <strong>".$errstr."</strong> IN <em>".$errfile."</em> ON LINE ".$errline;
		$this->errors['danger'][] = $string;
		if(LOG_ERRORS_DB and !empty($db)){
			$datetime = date("Y-m-d H:i:s", time());
			$errstr = $db->make_data_safe($errstr);
			$db->insert(DBTABLE_ERROR_LOGS, array($datetime, $severity, $errstr, $errfile, $errline, $_SESSION['login_id']), array("datetime", "severity", "description", "file", "line", "user"));
		}
		return true; // Don't execute PHP internal error handler
	}
	
	// returns html of callout for each error recorded subdivided by type of error
	public function getErrors(){
		$output = "";
		foreach($this->errors as $type => $e){
			foreach($e as $error){
				$output .= $this->bootstrap->callout("", $error, $type);
			}
		}
		return $output;
	}

	// set custom error. IN: $message =  the full error message, $level =  the level of error (danger or warning)
	public function setError($message, $level = "danger"){
		global $db;
		
		$this->errors[$level][] = $message;

		if(LOG_ERRORS_DB and !empty($db)){
			$datetime = date("Y-m-d H:i:s", time());
			$message = $db->make_data_safe($message);
			
			if(!$db->insert(DBTABLE_ERROR_LOGS, array($datetime, $level, $message, $_SESSION['login_id']), array("datetime", "severity", "description", "user"))){
				
				echo $db->getError("msg")."<br>\n".$db->getQuery();
				die();
			}
			
		}

		return true;
	}
	
	public function dbgArray($array){
		echo "<pre>\n";
		print_r($array);
		echo "\n</pre>\n";
	}
	
}

?>