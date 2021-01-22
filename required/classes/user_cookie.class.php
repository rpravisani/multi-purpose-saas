<?php
// speciale class to easily get values from cookie for user purposes
require(dirname(__FILE__).'/'.'cc_cookie.class.php'); 

class user_cookie extends cc_cookie{
	
	// get the id of the language table (first param of cookie) or false if cookie is not set
	public function getLang(){
		return parent::getSingleValue('0');
	}

	// get the id of the nation table (second param of cookie) or false if cookie is not set
	public function getNation(){
		return parent::getSingleValue('1');
	}

	// get the timezone (third param of cookie) or false if cookie is not set
	public function getTimezone(){
		return parent::getSingleValue('2');
	}

	// get the rememberme value (fifth param of cookie) or false if cookie is not set
	public function rememberme(){
		return parent::getSingleValue('4');
	}
	
}

?>