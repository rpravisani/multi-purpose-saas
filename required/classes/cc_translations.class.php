<?php

class cc_translate{
	
	private $db, $language, $section, $translations = array(), $_dbg; 
	
	function __construct($db, $section, $language = false){
		$this->db = $db;
		$this->section = $section;
		$this->setLanguage($language, false);
		$this->setTranslations($section);
	}
	
	/*** PUBLIC FUNCTIONS ***/	

	public function setTranslations($section, $add = false){
		$translations = $this->db->key_value("string", "translation", DBTABLE_TRANSLATIONS, 
							"WHERE (section = '".$section."' or section = '') 
							AND language = '".$this->language."' 
							AND active = '1'");
		$this->_dbg = $this->db->getQuery();
		if(!$translations) $translations = array();
		$this->translations = ($add) ? array_merge($this->translations, $translations) : $translations;
	}
	
	public function get($string){
		// get translation
		if(empty($string)) return "";
		if (array_key_exists($string, $this->translations)){
			return $this->translations[$string];	
		}else{
			$this->translationLost($string);
			return "<em>*".$string."*</em>";
		}
	}
	public function debug(){
		return $this->_dbg;
	}

	public function getTranslationArray(){
		return $this->translations;
	}
	
	// set the var $language (2-letter code), if $lang = false get langcode from browser
	public function setLanguage($lang = false, $reload = true){
		if($lang){
			$this->language = $lang;
		}else{
			// get a list of all availabe and active languages from the language table
			$available_languages = $this->db->col_value("code", DBTABLE_LANGUAGES, "WHERE active = '1'");
			// if none is active use english
			if(empty($available_languages)) $available_languages = array("en");
			// get prefered languages from browser -- see prefered_language function
			$this->language = $this->prefered_language ($available_languages);
			// if for some reason the browser didn't give back a language use default lang
			if(empty($this->language)) $this->language = $this->db->get1value("code", DBTABLE_LANGUAGES, "WHERE id = '".DEFAULT_LANG."'");;
		}
		if($reload and !empty($this->translations)) $this->setTranslations($this->section);
	}
	
	public function getLanguage(){
		return $this->language;
	}

	public function setSection($section, $add = false, $reload = true){
		$this->section = $section;
		if($reload and !empty($this->translations)) $this->setTranslations($this->section, $add);
	}

	/*** PRIVATE FUNCTIONS ***/	
	
	private function prefered_language ($available_languages, $http_accept_language = "auto") { 
		// if $http_accept_language was left out, read it from the HTTP-Header 
		if ($http_accept_language == "auto") $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : ''; 
	
		// standard  for HTTP_ACCEPT_LANGUAGE is defined under 
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4 
		// pattern to find is therefore something like this: 
		//    1#( language-range [ ";" "q" "=" qvalue ] ) 
		// where: 
		//    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" ) 
		//    qvalue         = ( "0" [ "." 0*3DIGIT ] ) 
		//            | ( "1" [ "." 0*3("0") ] ) 
		preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" . 
					   "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i", 
					   $http_accept_language, $hits, PREG_SET_ORDER); 
	
		// default language (in case of no hits) is the first in the array 
		$bestlang = $available_languages[0]; 
		$bestqval = 0; 
	
		foreach ($hits as $arr) { 
			// read data from the array of this hit 
			$langprefix = strtolower ($arr[1]); 
			if (!empty($arr[3])) { 
				$langrange = strtolower ($arr[3]); 
				$language = $langprefix . "-" . $langrange; 
			} 
			else $language = $langprefix; 
			$qvalue = 1.0; 
			if (!empty($arr[5])) $qvalue = floatval($arr[5]); 
	
			// find q-maximal language  
			if (in_array($language,$available_languages) && ($qvalue > $bestqval)) { 
				$bestlang = $language; 
				$bestqval = $qvalue; 
			} 
			// if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does) 
			else if (in_array($langprefix,$available_languages) && (($qvalue*0.9) > $bestqval)) { 
				$bestlang = $langprefix; 
				$bestqval = $qvalue*0.9; 
			} 
		} 
		return $bestlang; 
	} 	
	
	private function translationLost($string){
		if(DBTABLE_TRANSLATIONS_LOST){
			if( !$this->db->get1value("id", DBTABLE_TRANSLATIONS_LOST, "WHERE string='".$string."' AND lang = '".$this->language."' AND file = '".$this->section."'") ){
				$url = $_SERVER["SCRIPT_FILENAME"];
				$exp = explode("/", $url);
				$file = end( $exp );
				$file = $this->section;
				$this->db->insert(DBTABLE_TRANSLATIONS_LOST, array($string, $file, $this->language), array("string", "file", "lang") );
			}
		}
		return true;
	}
	

}


?>