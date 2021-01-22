<?php 

class cc_menu{

	private $db, 
			 $modulo_attivo = 0, 
			 $active_page = "", 
			 $parent_pages = false, 
			 $child_pages = false, 
			 $all_pages = false, 
			 $page_relations = false, 
			 $page_permissions = false, 
			 $menu = array(), 
			 $active_menu_item = array();
	 
 
	// da passare : db (oggetto), modulo attivo (int), moduli permessi (array)
	function __construct($active_page=false, $page_permissions=false, $db=false, $extra = false){
		// verifico connessione db
		if(!$db or !is_object($db)) die("No DB object defined!");
		
		// verifico moduli permessi
		if(!$page_permissions) die("No page permitted!"); // todo bettere error handeling!
		
		if($page_permissions != -1){
			// creating csv with permitted pages
			if(is_array($page_permissions)) $page_permissions = implode(",", $page_permissions);
			// getting only permitted pages 
			$parent_pages = $db->select_all_indexed ('id', DBTABLE_PAGES, "WHERE id IN (".$page_permissions.") AND active='1' AND parent = '0' ORDER BY `order`"); 
			$child_pages = $db->select_all_indexed ('id', DBTABLE_PAGES, "WHERE id IN (".$page_permissions.") AND active='1' AND parent != '0' ORDER BY `parent`, `order` ASC"); 
			$all_pages = $db->select_all_indexed ('id', DBTABLE_PAGES, "WHERE id IN (".$page_permissions.") AND active='1'"); 
			$page_relations = $db->key_value ('id', 'parent', DBTABLE_PAGES, "WHERE id IN (".$page_permissions.") AND active='1' ORDER BY `id`"); 
		}else{
			// getting all pages
			$parent_pages = $db->select_all_indexed ('id', DBTABLE_PAGES, "WHERE active='1' AND parent = '0' ORDER BY `order`"); 
			$child_pages = $db->select_all_indexed ('id', DBTABLE_PAGES, "WHERE active='1' AND parent != '0' ORDER BY `parent`, 	`order`"); 
			$all_pages = $db->select_all_indexed ('id', DBTABLE_PAGES, "WHERE active='1'"); 
			$page_relations = $db->key_value ('id', 'parent', DBTABLE_PAGES, "WHERE active='1' ORDER BY `id`"); 
		}
		if(!$all_pages) die("No page found!"); // TODO better error handling
						
		// Save variables to internal vars
		$this->db = $db;
		$this->parent_pages = $parent_pages;
		$this->child_pages = $child_pages;
		$this->all_pages = $all_pages; 
		$this->active_page = (int) $active_page;
		$this->page_relations = $page_relations;
		$this->page_permissions = $page_permissions;
	}
	
	// prepares array with all the menu items and html
	public function prepareMenu(){
		if(empty($this->parent_pages)) die("no pages"); // TODO better error handling
		foreach($this->parent_pages as $i=>$page){
			//$parentUrl = "cpanel.php?pid=".$page['id'];
			$parentUrl = $this->setUrl($page['id']);
			$parentClass = false;
			$parentItems[$i] = "<li id='menu-item-".$page['id']."'";
			$parentItem = false;
			
			// set class of parent item
			$parentIcon = (empty($page['icon'])) ? "chevron-right" : $page['icon'];
			if(!empty($page['icon_class'])) $parentIcon .= " ".$page['icon_class'];
			
			// get children pages if any
			$id_in = (empty($this->page_permissions)) ? "" : "id IN (".$this->page_permissions.") AND ";
			$children = $this->db->select_all(DBTABLE_PAGES, "WHERE ".$id_in."parent = '".$page['id']."' AND active = '1' ORDER by `parent`, `order` ASC");
			if($children){ // ok got children
				$parentUrl = "#"; // change utl to # for this item
				//$suffix = "<i class=\"fa fa-angle-left pull-right\"></i>";
				$parentClass = "treeview"; // set class for parent item
				$childItems = array();
				foreach($children as $c=>$child){ // loop children
					//$childUrl = "cpanel.php?pid=".$child['id']; // set url for child item
					$childUrl = $this->setUrl($child['id']);
					$childClass = false; // set class for child item
					// set child item class to active if current page corriponds to child item
					if($child['id'] == $this->active_page){
						$parentClass .= " active"; // class of the parent item
						$childClass = "active"; // class of the child item
					}
					// child icon
					$childicon = (empty($child['icon'])) ? "circle-o" : $child['icon']; // if not defined set default icon
					if(!empty($child['icon_class'])) $childicon .= " ".$child['icon_class']; // add extra icon class if defined
					// create child item html
					$childItem = "<li id='menu-item-".$child['id']."'";
					if($childClass) $childItem .= " class='".$childClass."'";
					$childItem .= ">";
					// child item html
					$childItems[$c] = $childItem."<a href=\"".$childUrl."\"><i class=\"fa fa-".$childicon."\"></i> <span>".$child['name']."</span></a></li>\n";
				}  // end foreach
				
				// create parent item html
				$parentItem  = "  <a href='".$parentUrl."'>\n";
				$parentItem .= "    <i class=\"fa fa-".$parentIcon."\"></i> <span>".$page['name']."</span> <i class=\"fa fa-angle-left pull-right\"></i>\n";
				$parentItem .= "  </a>\n";
				$parentItem .= "  <ul class='treeview-menu'>\n";
				$parentItem .= implode("\n", $childItems); // add children
				$parentItem .= "  </ul>\n";
				
			}else{
				// no children... check only if current page and set if necessary the parent class
				if($page['id'] == $this->active_page) $parentClass = "active";
			} // end if children
			
			// create menu item...
			if($parentClass) $parentItems[$i] .= " class='".$parentClass."'"; // add class if necessary
			$parentItems[$i] .= ">\n"; // close menu item html tag
			if($parentItem){ // if parent html is already set (created in the got children subroutine), add it to the array of the menu item
				$parentItems[$i] .= $parentItem;
			}else{ // ...else create html for menu item
				// html for menu item without children
				$parentItems[$i] .= "  <a href='".$parentUrl."'>\n"; // url
				$parentItems[$i] .= "    <i class=\"fa fa-".$page['icon']."\"></i> <span>".$page['name']."</span>"; // icon + name
				if(!empty($page['tag'])) $parentItems[$i] .= "<small class=\"label pull-right ".$page['tag_class']."\">".$page['tag']."</small>\n"; // tag
				$parentItems[$i] .= "  </a>\n"; // close link tag
				
				$parentItems[$i] .= $parentItem; // add to array
			}
			$parentItems[$i] .= "</li>\n"; // close menu item tag
		} // end foreach
		$this->menu = $parentItems; // add items to menu variable
		
	} // end function
	
	public function outputMenu(){
		foreach($this->menu as $item){
			echo $item;
		}
	}
	
	private function setBreadcrumb($div = false){
		if($this->page_relations[$this->active_page] == '0'){
			// is parent
			$page = $this->parent_pages[$this->active_page];
			$breadcrumb = array( $page['id'] => $page['title'] );
		}else{
			// is child
			$page = $this->child_pages[$this->active_page];
			$pid = $this->page_relations[$this->active_page];
			$parent = $this->parent_pages[$pid];
			$breadcrumb = array( $parent['id'] => $parent['title'], $page['id'] => $page['title']);
		}
		if($div){
			$breadcrumb = implode($div, $breadcrumb);
		}
		return $breadcrumb;
		
	}
	
	public function getBreadcrumb($div = false){
		$breadcrumb = $this->setBreadcrumb($div);
		if(empty($breadcrumb)) return false;
		$output = "";
		$c = 0;
		$l = count($breadcrumb);
		foreach($breadcrumb as $pid=>$item){
			$c++;
			if($c == $l){
				$output .= "<li class=\"active\">".$item."</li>\n";
			}else{
				$output .= "<li><a href=\"".$this->setUrl($pid)."\">".$item."</a></li>\n";
			}
		}
		return $output;
	}
	
	/*** HELP FUNCTIONS ***/
	
	// create url based on page id (for both parents and children)
	private function setUrl($pid){
		if(empty($pid) ) die("No page id found in setUrl() function of cc_menu_class.php");
		$page_data = $this->all_pages[$pid];
		if(empty($page_data) ) die("No page data found in setUrl() function of cc_menu_class.php");
		$url = "cpanel.php?pid=".$pid; // page id
		if(!empty($page_data['view'])) $url .= "&v=".$page_data['view']; // add view if any (html, pdf, xml, csv etc)
		//if(!empty($page_data['type'])) $url .= "&pt=".$page_data['type']; // add page type if any (table, module, grid or custom)
		if(!empty($page_data['action'])) $url .= "&a=".$page_data['action']; // add action if any defined (insert, update or delete)
		return $url;
	}


}
	
