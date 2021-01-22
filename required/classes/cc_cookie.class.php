<?php
class cc_cookie{
	
	private $cookiename = COOKIE_NS, $sep = ":";
	
	// sets cookie name and separator, if none is passed use default values
	function __construct($cookiename = false, $sep = false){
		if(!empty($cookiename)) $this->cookiename = $cookiename;
		if(!empty($sep)) $this->sep = $sep;
	}
	
	// check if cookie exists and returns true or false
	public function exists(){
		if(empty($_COOKIE[$this->cookiename])){
			return false;
		}else{
			return true;
		}
	}
	
	// set cookie. INPUT: content array (will be imploded) and lifespan in seconds. OUT: nothing
	public function set($content = array(), $lifespan = 0){
		if(empty($content)) return false;
		// make sure content is an array
		if(!is_array($content)) $content = array( $content );
		// create csv
		$string = implode($this->sep, $content);
		// get lifespan
		if(empty($lifespan)) $lifespan = COOKIE_LIFESPAN;
		// set expiry date
		$expire = time() + $lifespan;
		// set cookie (name, content, expiry date, namespace)
		setcookie($this->cookiename, $string, $expire, SITEROOT);
	}
	
	// kill the cookie
	public function kill(){
		$t = time()-3600;
		//echo "killing cookie named ".$this->cookiename;
		//echo "setting time to ".date("d-m-Y H:i:s", $t);;
		setcookie($this->cookiename, "", time()-3600, SITEROOT);	
	}

	// Get the value of the cookie. 
	// INPUT: string flag (if true the raw string will be returned otherwise an array). 
	// OUT: string / array or false in case cookie does not exist
	public function get($string = false){
		// return false if cookie does not exist (anymore)
		if( !$this->exists() ) return false;
		// if string flag = true output cookie as is, alse create array
		$content = ($string) ? $_COOKIE[$this->cookiename] : explode($this->sep, $_COOKIE[$this->cookiename]);
		return $content;
	}

	// returns single value of an exploded cookie content
	// IN: index of the array created during explosion
	// OUT: value of index or false in case no value is set or cookie does not exist
	public function getSingleValue($index){
		$data = $this->get();
		if(!$data or empty($data[$index])){
			return false;
		}else{
			return $data[$index];
		}
	}	

	// returns the number of values stored in cookie
	// IN: nothing
	// OUT: number of elements or 0 if cookie is not set
	public function countValues(){
		$data = $this->get();
		if(!$data){
			 return 0;
		}else{
			return (int) count($data);
		}
	}	


}
?>