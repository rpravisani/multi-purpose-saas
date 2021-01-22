<?php
/*** Classe creata ad hoc quando non c'è controllo di accesso - in realtà non fa nulla se non passare alcuni valori default ***/
class cc_user{
	
	private $db, $preferences = array("skin" => "skin-blue");

	function __construct($db){
		$this->db = $db;
	}
		
	/*** GET RESOLVED DATA ***/
	public function getName(){
		return "Username";
	}

	public function getEmail(){
		return "noreply@domain.com";
	}

	public function getLanguage(){
		return "en";
	}

	public function getNation($resolved = true){
		if($resolved){
			return '109';
		}else{
			return "Italy";
		}
	}

	public function getCity(){
		return "Loano";
	}

	public function getPoBox(){
		return "17025";
	}

	public function getAddress($which = 0){
		return "Borgata Case 19";
	}

	public function getFullAddress(){
		$address = $this->getAddress(0)."<br>";
		$address .= $this->getPoBox()." ".$this->getCity()."<br>";
		$address .= $this->getNation();
		return $address;
	}
	
	public function getGMT(){
		return "+2";
	}
	
	public function getPhonePrefix(){
		return "+39";
	}

	public function getPhone($getprefix = true){
		$prefix = "";
		if($getprefix){
			$prefix = $this->getPhonePrefix();
			$prefix .= " ";
		}
		return $prefix."338 1999 202";
	}
	
	public function getVatNumber($nation = true){
		if($nation){
			$prefix = "IT";
		}else{
			$prefix = "";
		}
		return $prefix."01511620096";
	}

	public function getSubscriptionType($name = true){
		$row = $this->db->fetch_array_row("SELECT * FROM `subscription_types` WHERE `level` = (SELECT MAX(`level`) FROM subscription_types");
		if(!$row) return false;
		if($name){
			return $row['name'];
		}else{
			return $row['id'];
		}
	}

	public function getSubscriptionDate(){
		return "19/08/2015";
	}

	public function getLastRenewDate(){
		return "19/08/2015";
	}

	public function getExpiryDate(){
		return "19/08/2035";
	}

	public function getPaymentMethod($resolved = true){
		if($resolved){
			return "Infinite Credit";
		}else{
			return '0';
		}
	}

	public function isChecked(){
		return true;
	}

	public function isActive(){
		return true;
	}

	public function getPreferences($what = false){
		if(!$what){
			return $this->preferences;
		}else{
			if(array_key_exists($what, $this->preferences)){
				return $this->preferences[$what];
			}else{
				return false;
			}
		}
	}
	
	public function getDefaultPage(){
		return $this->db->fetch_array_row("SELECT id FROM `".DBTABLE_PAGES." WHERE parent = '0' AND `order` = (SELECT MIN(`order`) FROM ".DBTABLE_PAGES.")");
	}


	
}


?>