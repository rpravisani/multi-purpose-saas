<?php 

class phpbootstrap{
	
	private $datePicker     = false;
	private $colorPicker    = false;
	private $timePicker     = false;
	private $gotSeconds     = false;
	private $minuteStep     = 5;
	private $requiredSign   = "*";
	private $add_permission = false, $edit_permission = false, $show_permission = false, $delete_permission = false, $copy_permission = false, $activate_permission = false;
	
    private $CustomColorPickerFunc = false;
	
	private function strip($html){
		return strip_tags($html, "<br><br/><strong><b><em><i><a><button><hr><img><ul><ol><li><small><span>");
	}

	public function alert($title, $message, $type = "danger", $dismissable = true){
		switch($type){
			case "success":
				$c = "success";
				$icon = "fa-check";
				break;
			case "warning":
				$c = "warning";
				$icon = "fa-warning";
				break;
			case "white":
				$c = "white";
				$icon = "fa-info";
				break;
			case "info":
				$c = "info";
				$icon = "fa-info";
				break;
			default:
				$c = "danger";
				$icon = "fa-ban";
				break;
		}
		$class = "alert alert-".$c;
		if($dismissable) $class .= " alert-dismissable";
		
		$title = $this->strip($title);
		$message = $this->strip($message);
		
		$output = "<div class=\"".$class."\">\n";
		if($dismissable) $output .= "<button aria-hidden=\"true\" data-dismiss=\"alert\" class=\"close\" type=\"button\">×</button>\n";
        if(!empty($title)) $output .= "<h4><i class=\"icon fa ".$icon."\"></i>".$title."</h4>\n";
        if(!empty($message)) $output .= "<p>".$message."</p>\n";
        $output .= "</div>\n";		
		
		return $output;
		
	}

	public function callout($title, $message, $type = "danger"){
		switch($type){
			case "success":
				$c = "success";
				break;
			case "warning":
				$c = "warning";
				break;
			case "info":
				$c = "info";
				break;
			default:
				$c = "danger";
				break;
		}
		$class = "callout callout-".$c;
		
		$title = $this->strip($title);
		$message = $this->strip($message);
		
		$output = "<div class=\"".$class."\">\n";
        if(!empty($title)) $output .= "<h4>".$title."</h4>\n";
        if(!empty($message)) $output .= "<p>".$message."</p>\n";
        $output .= "</div>\n";		
		
		return $output;
		
	}
    
    public function toggle($args, $tabindex = 0){
        
        $toggle = "";
            
        $name       = $args['name'];
        $value      = (int)  $args['value'];
        $switch     = (empty($args['value'])) ? "off" : "on";
        $label      = (empty($args['label'])) ? "" : trim($args['label']);
        $id         = (empty($args['id'])) ? $name : $args['id'];
        $extraclass = (empty($args['extraclass'])) ? "" : (string) $args['extraclass']; "id='".$args['id']."'";
        $class      = (empty($args['disabled'])) ? "" : "toggle-disabled";
        $inline     = (empty($args['inline'])) ? "" : "inline-toggle-switch";
        
        $tabindex   = (empty($tabindex)) ? "" : "tabindex='".$tabindex."'";
        
        if( empty($inline) and !empty($label) ){
            $toggle .= "<label>".$label."</label><br>";
        }
        
        $extraclass_input = (empty($args['disabled'])) ? $extraclass."_input" : "";
        
        if(!empty($inline)) $toggle .= "<span class='".$inline."'>";
        if(empty($args['disabled'])) $toggle .= "<input type='hidden' name='".$name."' id='".$id."' value='".$value."' class='".$extraclass_input."'>";
		$toggle .= "<i id='toggle_".$id."' data-onoff=\"".$switch."\" class=\"fa fa-toggle-".$switch." ".$extraclass." toggle-switch ".$class."\" ".$tabindex."></i>";
        if(!empty($inline) and !empty($label)) $toggle .= " ".$label;
        if(!empty($inline)) $toggle .= "</span>";
        
        return $toggle;
        
    }
    
    public function toggleFaux($onoff = 0, $label = '', $id = false, $extraclass = ''){
        $switch = (empty($onoff)) ? "off" : "on";
        $id = ($id) ? "id='".$id."'" : "";
		$toggle = "<i ".$id." data-onoff=\"".$switch."\" class=\"fa fa-toggle-".$switch." ".$extraclass." toggle-switch\"></i>";
        if(!empty($label)) $toggle .= " ".$label;
        return $toggle;
    }
	
	
	/*---- FOR MODULE (BOX AND FORM) -------------------------------------------------------------------------*/	
	
	// output the opening html of the content box of a module, includes call to openForm() function
	public function moduleBoxHeader( $activate_copy = false, $boxtitle = "", $copied_label = "", $_pagename = "", $_record = 0, $pid = 0, $_action = "", $canadd = false, $canedit = false, $candelete = false){
		
		// get globals if local vars are not set - only $copybtn is not global
		if(empty($boxtitle)) 		global $boxtitle;
		if(empty($copied_label)) 	global $copied_label;
		if(empty($_pagename)) 		global $_pagename;
		if($_record === 0) 			global $_record;
		if($pid === 0) 				global $pid;
		if(empty($_action)) 		global $_action;
		if($canwrite !== 1) 		global $canwrite; // legacy

		global $_t; // for translations
		
		// Can i copy switch - local value must be true even if page permissions is true
		$cancopy = (!$activate_copy) ? false : $this->canCopy();
		
		// setting up box header dom
		$out = "
        <div class=\"box-header with-border\">\n";
		
		if(!empty($boxtitle)){
		$out .= "
	        <i class=\"fa fa-edit\"></i>\n
        	<h3 class=\"box-title\">".$boxtitle."</h3>\n";
		}
		
		// add copied label if any
		if(!empty($copied_label)){
			$out .= "&nbsp;&nbsp;<small class=\"label bg-teal\"><em>".$copied_label."</em></small>\n";
		}
		
		// add new and copy buttons, only if editing
		if(!empty($_record) and $this->canAdd()){
			$out .= "
				<!-- new and copy buttons (only if edit) -->\n
                <div class=\"pull-right\">\n
                    <button id=\"newRecordBtn\" class=\"btn btn-primary btn-sm\"><i class=\"fa fa-plus\"></i>&nbsp;&nbsp;".$_t->get('new')."</button>\n";
			// add copybutton only if explicitly set to true
			if($cancopy){
				$out .= "<button id=\"copyRecordBtn\" class=\"btn btn-info btn-sm\"><i class=\"fa fa-copy\"></i>&nbsp;&nbsp;".$_t->get('copy')."</button>\n";
			}
                                    
            $out .= "</div>\n";
		}
		
		// close box header
		$out .= "</div> <!--box-header end-->\n";
		
		// get opening part of form - passing on the variables
		$out .= $this->openForm($_pagename, $_record, $pid, $action);
		
		return $out;
		
	}
	
	// Creates the form opening dom and adds standard hidden values - action and method are fixed
	public function openForm($_pagename = "", $_record = 0, $pid = 0, $action = ""){
		// get globals if local vars are not set 
		if(empty($_pagename)) global $_pagename;
		if(empty($_record)) global $_record;
		if(empty($pid)) global $pid;
		if(empty($_action)) global $_action;
		
		// if user cannot write records return empty
		if(!$this->canEdit()) return "";
		
		$out = "		
        <form id=\"".$_pagename."-form\" action=\"required/write2db.php\" method=\"post\">\n
        	<input type=\"hidden\" name=\"pid\" id=\"pid\" value=\"".$pid."\" />\n
        	<input type=\"hidden\" name=\"action\" id=\"action\" value=\"".$_action."\" />\n
        	<input type=\"hidden\" name=\"record\" id=\"record\" value=\"".$_record."\" />\n
		"; 
		
		return $out;       
		
	}

	// closes box (box-footer), adds save buttons if user is permitted to save and closes form
	public function moduleBoxFooter($_action = "", $canwrite = false, $stay = true, $close = true, $new = true, $cancel = true){
		
		// get globals if local vars are not set 
		if(empty($_action)) 	global $_action;
		if(empty($canwrite)) 	global $canwrite; // LEGACY
		
		global $_t; // for translations
		
		// open box-footer
		$out = "<div class=\"box-footer\">\n";
		
		// if action is insert or update and user has writing permissions add save buttons
        if(($_action == "insert" or $_action == "update") and $this->canEdit() ){
			$out .= ($stay)  ? "<button name=\"save\" value=\"stay\" data-after=\"stay\"  class=\"saveRecordBtn btn btn-success\"><i class=\"fa fa-check mr-2\"></i>".$_t->get('save')."</button>" : "";
			$out .= ($close) ? "<button name=\"save\" value=\"close\" data-after=\"close\" class=\"saveRecordBtn btn btn-success\"><i class=\"fa fa-times mr-2\"></i>".$_t->get('save-close')."</button>" : "";
			$out .= ($new and $this->canAdd()) ? "<button name=\"save\" value=\"new\" data-after=\"new\" class=\"saveRecordBtn btn btn-success\"><i class=\"fa fa-plus mr-2\"></i>".$_t->get('save-new')."</button>" : "";
            $cancelBtnText = $_t->get('cancel');
        }else{
            $cancelBtnText = $_t->get('close');
        }
		 // add cancel button
         $out .= ($cancel) ? "<button id=\"cancelRecordBtn\" class=\"btn btn-danger\"><i class=\"fa fa-times mr-2\"></i>".$cancelBtnText."</button>\n" : ""; 
         
		 // close box-footer
		 $out .= "</div> <!--box-footer end-->\n";
		 
		 // close form (if it was opened)
		 if($this->canEdit()) $out .= "</form>\n";
		
		return $out;       
		
	}
	

	/*---- FOR TABLES ---------------------------------------------------------------------------------------*/	

	// create dataTable: get params, adds "datatable" to the types array and calls the table function
	public function dataTable($thead = array(), $tbody = array(), $types = array(), $id, $configs){
		if(!is_array($types)) $types = array($types);
		$types[] = "datatable";
		return $this->table($thead, $tbody, $types, $id, $configs);
	}

	public function table($thead = array(), $tbody = array(), $types = array(), $id = false, $configs = false){
		$class = "table";
		if(!is_array($types)) $types = array($types);
		foreach($types as $type){
			// switch to avoid non permettide values
			switch($type){
				case "bordered":
					$c = "bordered";
					break;
				case "condensed":
					$c = "condensed";
					break;
				case "striped":
					$c = "striped";
					break;
				case "hover":
					$c = "hover";
					break;
				default:
					$c = "";
					break;
			}
			if(!empty($c)) $class .= " table-".$c;
			if($type == "datatable") $class .= " dt";
		}
		
		$output = "<table ";
		if(!empty($id)) $output .= "id=\"".$id."\"";
		$output .= "class='".$class."'>\n";
		
		if(!empty($thead)){
			$output .= "<thead>\n<tr>\n";
			foreach($thead as $th) $output .= "<th>".$th."</th>";
			$output .= "\n</tr>\n</thead>\n";
		}
		if(!empty($tbody)){
			$output .= "<tbody>\n";
			foreach($tbody as $tr){
				$n = 0;
				$output .= "<tr>\n";
				foreach($tr as $td){
					$n++;
					$ctd = "";
					if(!empty($configs)){
						$config = $configs[$n];
						if(!empty($config)){
							foreach($config as $k=>$v){								
								$ctd .= " ".$k."=\"".$v."\""; 
							}
						}
					}
					$output .= "<td";
					$output .= $ctd;
					$output .= ">".$td."</td>";
				}
				$output .= "</tr>\n";
			}
			$output .= "</tbody>\n";
		}
		
		$output .= "</table>\n";
		
		return $output;
		
	}
	
	public function newButton($id, $pid, $text, $icon = "plus", $action = "insert", $view = "html"){	
		global $_t;
		if(!$this->canAdd()) return false;
		$txt = $_t->get($text);
		return "<button id='".$id."' data-pid='".$pid."' data-action='".$action."' data-view='".$view."' class='btn btn-primary goto'><i class='fa fa-".$icon."'></i>&nbsp;".$txt."</button>";
	}
	
	public function button($text, $color = "default", $size = "", $id = false, $class = "", $icon = "", $disabled = false, $block = true){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($text)) extract($text);
		
		$text = (string) $text;
		$size = (string) $size;
		$class = (string) $class;
		if(!empty($size)) $size = "btn-".$size;
		$block = ($block) ? "btn-block" : "";
		if(empty($color)) $color = "default";
		$color = "btn-".$color;
		$id = (empty($id)) ? "" : "id='".$id."'";
		$icon = (empty($icon)) ? "" : "<i class=\"fa mr-2 fa-".$icon."\"></i>";
		$disabled = (empty($disabled)) ? "" : " disabled=\"disabled\"";
		
		$out = "<button ".$id." class=\"btn ".$block." ".$color." ".$size." ".$class."\"".$disabled.">".$icon.$text."</button>";
		return $out;
	}
	
	public function tabFilter($name, $label, $values, $selected = false, $label_empty = "All", $value_empty = '0'){
		$label = trim($label);
		$html = "<label>".$label."</label>&nbsp;";
		$html .= "<select name=\"filter_".$name."\" id=\"filter_".$name."\">\n";
		$html .= "<option value=\"".$value_empty."\">".$label_empty."</option>\n";
		foreach($values as $k => $v){
			$select = ($k == $selected) ? "selected" : "";
			$html .= "<option ".$select." value=\"".$k."\">".$v."</option>\n";
			
		}
		$html .= "</select>\n&nbsp;&nbsp;\n";
		return $html;
	}

	/*---- INPUT ELEMENTS ---------------------------------------------------------------------------------------*/	
    
    /**
     * Generic method for outputting a form field.
     * Args must contain a 'type' entry wich specifies the type of field to output. some aliases cas be used (see switch)
     * if a wrong o inexisting type is passed it wil fallback to a text input.
     * Tabindex is externalised and if not empty will overwrite any tabindex entry in the arg array. This is so we can assign 
     * the tabindex in the view where it makes more sense it should be. 
     */
    public function field($args, $tabindex = 0){
        
        if(empty($args)) $args['type'] = "text";
        
        $type = trim($args['type']);
        if(empty($type)) $type = "text";
        
        $tabindex = (int) $tabindex;
        if(!empty($tabindex)) $args['tabindex'] = $tabindex;
        
        switch($type){
            case 'wysiwyg':
            case 'rte':
            case 'editor':
                $type = "_wysiwyg";
                break;
            case 'text':
            case 'number':
            case 'password':
            case 'email':
            case 'url':
                $type = "input".ucfirst($args['type']);
                break;
            case 'date':
            case 'datepicker':
                $type = "datepicker";
                break;
            case 'time':
            case 'timepicker':
                $type = "timepicker";
                break;
            case 'color':
            case 'colorpicker':
                $type = "colorpicker";
                break;
            case 'money':
            case 'currency':
                $type = "currency";
                break;
        }
        
        if( !method_exists($this, $type) or empty($type) ){
            $args['type'] = "text";
            $type = "input";
        }
        
        if(count($args) == 1 or isset($args['--dbg'])){
            
            $r = new ReflectionMethod($this, $type);
            $params = $r->getParameters();
            $output = "Type: <strong>".$type."</strong><br>\n";
            $p = array();
            foreach ($params as $param) {
                $default = ($param->isOptional()) ? $this->getRealValue($param->getDefaultValue()) : '';
                $badgeClass = ($param->isOptional()) ? "bg-green" : "bg-light-blue";
                $pName = "<span class='badge ".$badgeClass."'>";
                $pName .= $param->getName();
                if($param->isOptional()) $pName .= ": <em>".$default."</em>";
                $pName .= "</span>";
                $p[] = $pName; 
            }
            $output .= implode(" ", $p);
            return $output;
            
        }else{
            
            if(isset($args['name'])){
                if( $dot  = stripos($args['name'], ".") !== false and empty($args['id']) ){
                    $args['id'] = substr($args['name'], $dot+1);
                }             
            }
            
            return $this->$type($args);            
        }
        
        
    }
    
    private function getRealValue($val){
        $type = gettype($val);
        
        switch($type){
            case 'boolean':
                $val = ($val === true) ? 'true' : 'false';
                break;
            case 'NULL':
                $val = 'null';
                break;
            case 'array':
            case 'object':
                $val = (empty($val)) ? "" : implode(",", (array) $val);
                $val = "array(".$val.")";
                break;
            case 'string':
                $val = "'".$val."'";
                break;
            case 'integer':
            case 'double':
                break;
            default:
                $val = "";
                break;
        }
        
        return $val;
        
    }
	
	// create select2 html complete with bootstrap wrapper and label. 
	public function select2($label, $name = "", $options = "", $required = false, $tabindex = false, $multi = false, $disabled = false, $id = false, $class = ""){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
				
		return $this->select($label, $name, $options, $required, $tabindex, $multi, "select2 ".$class, $disabled, $id);
	}

	// create select2 html complete with bootstrap wrapper and label. 
	public function select2a($label, $name = "", $options = "", $required = false, $tabindex = false, $multi = false, $disabled = false, $id = false, $class = ""){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
				
		return $this->select($label, $name, $options, $required, $tabindex, $multi, "select2a ".$class, $disabled, $id);
	}


	// create select html complete with bootstrap wrapper and label. 
	public function select($label, $name = '', $options = "", $required = false, $tabindex = false, $multi = false, $class = "", $disabled = false, $id = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);		
		
		if($multi) $name .= "[]"; 
		
		if(!$this->canEdit()){
			$eclass = "readonly";
			$disabled = true;			
		}
		
		$p = $this->setInputParams($label, $name, $required, $tabindex, $id, "", false, $disabled);
		if(empty($options)) $options = "<option></option>";
		$multiple = ($multi) ? "multiple" : "";
		$class = (string) trim($class);
		
		$out  = "<div class=\"form-group\">\n";
		if($label) $out .= "	<label>".$p['label']."</label>\n";
		$out .= "
				  <select class=\"form-control ".$class."\" ".$p['id']." ".$p['name']." ".$p['required']." ".$p['tabindex']." ".$p['disabled']." ".$multiple.">\n
				  ".$options."
				  </select>\n
				</div>\n
		";
		
		return $out;
	}
	
	public function textarea($label, $name = "", $value = "", $placeholder = "", $rows= '3', $required = false, $tabindex = false, $readonly = false, $disabled = false, $id = false, $class = ""){
		
		$wysiwyg = false;
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		if(!$this->canEdit()){
			$class .= " readonly";
			$readonly = true;			
		}
		
		if($wysiwyg){
			$class .= " wysiwyg";
			global $css_assets, $js_assets;
			//$css_assets[] = "plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css";
			//$js_assets[]  = "plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js";
			$css_assets[] = "plugins/richtext/richtext.min.css";
			$js_assets[]  = "plugins/richtext/jquery.richtext.min.js";

		}
				    
		$p = $this->setInputParams($label, $name, $required, $tabindex, $id, $placeholder, $readonly, $disabled);
		$rows = (empty($rows)) ? 3 : (int) $rows;
		
		$out = "
				<div class=\"form-group\">\n
				  <label>".$p['label']."</label>\n
				  <textarea class=\"form-control ".$class." \" rows=\"".$rows."\" ".$p['name']." ".$p['id']." ".$p['placeholder']." ".$p['tabindex']." ".$p['readonly']." ".$p['disabled'].">".$value."</textarea>\n
				</div>\n
		";
		
		return $out;		
	}
	
	public function _wysiwyg($args){
		
		$defaults = array();
		$defaults['id'] = "editor";
		$defaults['name'] = "editor";
		$defaults['class'] = "wysiwyg";
		$defaults['placeholder'] = "Text...";
		$defaults['usediv'] = true;
		$defaults['showtoolbar'] = false;
		$defaults['toolbar'] = array();
		
		$args = array_merge($defaults, $args);
		
		$toolbar = ($args['toolbar']) ? $this->wysiwyg_toolbar($args['id'], $args['commands']) : "";
		
		if($args['usediv']){
			$html = "<div id=\"".$args['id']."\" data-placeholder=\"".$args['placeholder']."\"></div>\n";			
		}else{
			$html = "<textarea name=\"".$args['name']."\" id=\"".$args['id']."\" placeholder=\"".$args['placeholder']."\"></textarea>\n";
		}
		
		$script = "
			<script>
			  var editor = new wysihtml5.Editor('".$args['id']."');
			</script>		
		";
		
		
	}
	

	// create input type text element complete with bootstrap wrapper and label. $addon = text/html of add on (p.e. '€' or '<i class="fa fa-envelope"></i>')
	public function inputText($label, $name = "", $value = false, $placeholder = "", $required = false, $tabindex = false, $readonly = false, $disabled = false, $maxleng = 0, $id = false, $addon = "", $addonEnd = false, $class = "", $data = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);		
		
		return $this->input("text", $label, $name, $value, $placeholder, $required, $tabindex, $readonly, $disabled, $maxleng, 0, 0, 0, $id, $addon, $addonEnd, $class, $data, $help);
	}

	// create input type number element complete with bootstrap wrapper and label. $addon = text/html of add on (p.e. '€' or '<i class="fa fa-envelope"></i>')
	public function inputNumber($label, $name = "", $value = false, $placeholder = "", $required = false, $tabindex = false, $readonly = false, $disabled = false, $min = 0, $max = 0, $step = 0, $id = false, $addon = "", $addonEnd = false, $class = "", $data = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		return $this->input("number", $label, $name, $value, $placeholder, $required, $tabindex, $readonly, $disabled, 0, $min, $max, $step, $id, $addon, $addonEnd, $class, $data, $help);
	}

	// create input type password element complete with bootstrap wrapper and label. $addon = text/html of add on (p.e. '€' or '<i class="fa fa-envelope"></i>')
	public function inputPassword($label, $name = "", $value = false, $placeholder = "", $required = false, $tabindex = false, $readonly = false, $disabled = false, $maxleng = 0, $id = false, $addon = "", $addonEnd = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		return $this->input("password", $label, $name, $value, $placeholder, $required, $tabindex, $readonly, $disabled, $maxleng, 0, 0, 0, $id, $addon, $addonEnd, $class, $data, $help);
	}

	// create input type email element complete with bootstrap wrapper and label. Addon <i class="fa fa-envelope"></i>' default = on
	public function inputEmail($label, $name = "", $value = false, $placeholder = "", $required = false, $tabindex = false, $readonly = false, $disabled = false, $maxleng = 0, $id = false, $addon = true, $addonEnd = false, $class = "", $data = false, $help){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		$addon = ($addon) ? "<i class=\"fa fa-envelope\"></i>" : "";
		return $this->input("email", $label, $name, $value, $placeholder, $required, $tabindex, $readonly, $disabled, $maxleng, 0, 0, 0, $id, $addon, $addonEnd, $class, $help);
	}

	// create input type password element complete with bootstrap wrapper and label. Addon <i class="fa fa-envelope"></i>' default = on
	public function inputUrl($label, $name = "", $value = false, $placeholder = "", $required = false, $tabindex = false, $readonly = false, $disabled = false, $maxleng = 0, $id = false, $addon = "", $addonEnd = false, $class = "", $data = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		return $this->input("url", $label, $name, $value, $placeholder, $required, $tabindex, $readonly, $disabled, $maxleng, 0, 0, 0, $id, $addon, $addonEnd, $class, $help);
	}
	
	// general input generator - limited to type = text, password, email, number, url (for now)
	public function input($type = 'text', $label = '', $name = '', $value = false, $placeholder = "", $required = false, $tabindex = false, $readonly = false, $disabled = false, $maxleng = 0, $min = 0, $max = 0, $step = 1, $id = false, $addon = "", $addonEnd = false, $class = "", $data = false, $help = ""){
		
		global $_wrongfields;

		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($type)) extract($type);		
		
		$eclass = (string) trim($class);
        
		// if cannot edit (neither canadd flag or canedit flag is true) set readonly flag to true
		if(!$this->canEdit()){
			$readonly = true;			
		}
		if($readonly){
			$eclass = "readonly";
		}
		
		$form_group_class = "form-group"; 
        if(isset($_wrongfields)){
		  if( in_array($name, $_wrongfields) ) $form_group_class .= " has-error";            
        }
		
		// get input params
		$p = $this->setInputParams($label, $name, $required, $tabindex, $id, $placeholder, $readonly, $disabled, $maxleng, $min, $max, $step, $data);

		$out  = "<div class=\"".$form_group_class."\">\n";
		if(!empty($p['label']))           $out .= "  <label>".$p['label']."</label>\n";
        
        // addon start
		if(!empty($addon))                $out .= "  <div class=\"input-group\">\n  ";
		if(!empty($addon) and !$addonEnd) $out .= "    <span class=\"input-group-addon\">".$addon."</i></span>\n  ";
        
		$out .= "  <input type=\"".$type."\" ".$p['name']." ".$p['id']." value=\"".$value."\" ".$p['placeholder']." class=\"form-control ".$eclass."\" ".$p['data']." ".$p['required']." ".$p['tabindex']." ".$p['readonly']." ".$p['disabled']." ".$p['maxleng']." ".$p['min']." ".$p['max']." ".$p['step'].">\n";
		
        // addon end
        if(!empty($addon) and $addonEnd)  $out .= "    <span class=\"input-group-addon\">".$addon."</i></span>\n";
		if(!empty($addon))                $out .= "  </div>\n  "; // chiudi input-group
		
        $out .= "<span class=\"help-block\">".$help."</span>"; // fine $form_group_class
        $out .= "</div>\n"; // fine $form_group_class
		
		return $out;
        
	}
	
	public function checkbox($name, $checked = false, $comment = "", $before = false, $disabled = false, $id = false, $class = false){	
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($name)) extract($name);
		
		
		$disabled = ($disabled) ? "disabled=\"disabled\"" : "";
		$disabled = ($this->canEdit()) ? $disabled : "disabled=\"disabled\"";

        if(strpos($comment, "|") ){
            list($comment, $tooltip) = explode("|", $comment);
        }
        
        if(!empty($tooltip)){            
            $tt = "<i style='margin-left: 5px;' data-toggle=\"tooltip\" title='".$tooltip."' class='fa fa-question-circle hint'></i>";
            $comment .= $tt;
        }		
		
		$class = (empty($class)) ? "" : "class=\"".$class."\"";

		$c = ($checked) ? "checked" : "";
		$id = (empty($id)) ? $name : $id;
		$out  = "<div class=\"checkbox icheck \" style='padding-top: 26px;'>\n";
		if($before) $out .= "	".$comment."&nbsp;&nbsp;\n";
		$out .= "	<input ".$disabled." type=\"checkbox\" name=\"".$name."\" ".$class." id=\"".$id."\" ".$c." >\n";
		if(!$before) $out .= "	&nbsp;&nbsp;".$comment."\n";
		$out .= "</div>\n";

		return $out;
	}

	
	public function radioboxes($label, $name = "", $values = array(), $checked = false, $horizontal = true, $disabled = array(), $class = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);		
		
		if(!is_array($values)) $values = array($values);
		$class = (string) $class;
		$out = "<label>".$label."</label>\n";
		$out .= "<div class=\"form-group icheck radios\">\n";
		foreach($values as $label => $value){
			$c = ($value == $checked) ? "checked" : "";
			$d = (in_array($value, $disabled) or !$this->canEdit()) ? "disabled=\"disabled\"" : "";
			$out .= "<label>\n";
			$id = str_replace(" ", "-", $value);
			$id = $name."-".$id;
			$out .= "<input type=\"radio\" name=\"".$name."\" id=\"".$id."\" value=\"".$value."\" class=\" ".$class."\" ".$c." ".$d.">\n";
			if(!is_int($label)) $out .= "<span>".$label."</span>\n";
			$out .= "</label>\n";
			if(!$horizontal) $out .= "<br>\n";
		}
		$out .= "</div>\n";

		return $out;
	}
	
	
	public function multifield($type = "text", $label, $name, $values = array(), $deltitle = "", $btn_id = false, $btn_text = "Add", $eclass = "", $readonly = false, $disabled = false){	
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($type)) extract($type);
				
		$formgroup_class = ($this->canEdit()) ? "" : "-disabled";
		if(!is_array($values)) $values = array($values);
		
		$name = ( !empty($name) ) ? (string) $name : "";
		$id = ( !empty($id) ) ?  (string) $id : $name;
		
		$eclass = ($this->canEdit()) ? (string) " ".$eclass : (string) " ".$eclass." readonly";
		$readonly = (!$this->canEdit()) ? true : $readonly;

		$readonly = ($readonly) ? "readonly=\"readonly\"" : "";
		$disabled = ($disabled) ? "disabled=\"disabled\"" : "";
		
		$out  = "<div class=\"form-group multifield".$formgroup_class."\">\n";
		$out .= "  <label>".$label."</label>\n";
		
		if(empty($values)){
			
			$out .= "  <div class=\"input-group\">\n";
			$out .= "    <input type=\"".$type."\" name=\"".$name."[1]\" id=\"".$id."-1\" value=\"\" class=\"form-control".$eclass."\" ".$readonly." ".$disabled.">\n";
			$out .= "    <span class=\"input-group-addon\" title=\"".$deltitle."\"><i class=\"fa fa-times\"></i></span>\n";
			$out .= "  </div>\n";
			
		}else{
			
			foreach($values as $c => $value){
				
				$out .= "  <div class=\"input-group\">\n";
				$out .= "    <input type=\"".$type."\" name=\"".$name."[".$c."]\" id=\"".$id."-".$c."\" value=\"".$value."\" class=\"form-control".$eclass."\" ".$readonly." ".$disabled.">\n";
				$out .= "    <span class=\"input-group-addon\" title=\"".$deltitle."\"><i class=\"fa fa-times\"></i></span>\n";
				$out .= "  </div>\n";

			}

		}
		
		$out  .= "</div>\n";
		
		if($this->canEdit()){ // ok canedit perché anceh se non posso aggiumgere record posso aggiumgere entri in multi campo
			$out  .= "<div class=\"btn btn-primary btn-xs addmulti\" data-fieldname=\"".$name."\" id=\"".$btn_id."\"><i class=\"fa fa-plus\"></i>&nbsp;&nbsp;".$btn_text."</div>\n";
						                          
		}
		
		return $out;

	}

	public function datepicker($label, $name = "", $value = false, $id = false, $required = false, $tabindex = false, $icon = true, $class = "", $show_today_on_empty = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		if($required){
			$required = "required=\"required\"";
			$label = (string) $label.$this->requiredSign;
		}else{
			$label = (string) $label;
			$required = "";
		}
		
		$d = ($disabled) ? "disabled=\"disabled\"" : "";
		
		$tabindex = ($tabindex) ? "tabindex=\"".$tabindex."\"" : "";
		
		if(!empty($name)){
			$name = (string) $name;
			$id = ($id) ? "id=\"".$id."\"" : "id=\"".$name."\"";
			$name = "name=\"".$name."\"";
		}else{
			$name = "";
			$id = ($id) ? "id=\"".$id."\"" : "";
		}
		
		$dateformat = (DATE_FORMAT) ? DATE_FORMAT : DEFAULT_DATE_FORMAT;
		
		if(empty($value)){
			$value = ($show_today_on_empty) ? date($dateformat) : "";
		}else{
			$regexp = $this->getRegExp($dateformat);
			if( !preg_match("/".$regexp."/", $value) ){			
				$value = date($dateformat, time());	
			}			
		}
		
		$output  = "<div class=\"bootstrap-timepicker\">\n";
		$output .= "  <div class=\"form-group\">\n";
		if(!empty($label)) $output .= "    <label>".$label."</label>\n";
		if($icon) $output .= "    <div class=\"input-group\">\n";
		$output .= "      <input ".$d." type=\"text\" ".$name." ".$id."  value=\"".$value."\" class=\"datepicker ".$class." form-control\" ".$required." ".$tabindex.">\n";
		if($icon){
			$output .= "      <div class=\"input-group-addon\">\n";
			$output .= "        <i class=\"fa fa-calendar\"></i>\n";
			$output .= "      </div>\n";
			$output .= "    </div>\n";
		}
		$output .= "  </div>\n";
		$output .= "</div>\n";
		
		// set datePicker flag to true so that cpanel loads css and js assets
		$this->datePicker = true;

		return $output;
	}

	public function multidatepicker($label, $name, $value = false, $id = false, $required = false, $tabindex = false, $icon = true, $class = "", $show_today_on_empty = false){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		if($required){
			$required = "required=\"required\"";
			$label = (string) $label.$this->requiredSign;
		}else{
			$label = (string) $label;
			$required = "";
		}
		$tabindex = ($tabindex) ? "tabindex=\"".$tabindex."\"" : "";
		
		if(!empty($name)){
			$name = (string) $name;
			$id = ($id) ? "id=\"".$id."\"" : "id=\"".$name."\"";
			$name = "name=\"".$name."\"";
		}else{
			$name = "";
			$id = ($id) ? "id=\"".$id."\"" : "";
		}
		
		$dateformat = (DATE_FORMAT) ? DATE_FORMAT : DEFAULT_DATE_FORMAT;
		if(empty($value)) $value = ($show_today_on_empty) ? date($dateformat) : "";		
				
		$output  = "<div class=\"bootstrap-timepicker\">\n";
		$output .= "  <div class=\"form-group\">\n";
		if(!empty($label)) $output .= "    <label>".$label."</label>\n";
		if($icon) $output .= "    <div class=\"input-group\">\n";
		$output .= "      <input type=\"text\" ".$name." ".$id."  value=\"".$value."\" class=\"multidate ".$class." form-control\" ".$required." ".$tabindex.">\n";
		if($icon){
			$output .= "      <div class=\"input-group-addon\">\n";
			$output .= "        <i class=\"fa fa-calendar\"></i>\n";
			$output .= "      </div>\n";
			$output .= "    </div>\n";
		}
		$output .= "  </div>\n";
		$output .= "</div>\n";
		
		// set datePicker flag to true so that cpanel loads css and js assets
		$this->datePicker = true;

		return $output;
	}

	public function timepicker($label, $name = "", $value = false, $secs = false, $id = false, $required = false, $tabindex = false, $icon = true, $class = "", $step = '15'){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		if($label !== false) $label = (string) $label;
		if($required){
			$required = "required=\"required\"";
			$label = ($label === false) ? false : $label.$this->requiredSign;
		}else{
			$required = "";
		}
		
		$d = ($disabled) ? "disabled=\"disabled\"" : "";	
		
		$step = (int) $step;
		$s = "data-minute-step = '".$step."'";
		
		$tabindex = ($tabindex) ? "tabindex=\"".$tabindex."\"" : "";
		
		if(!empty($name)){
			$name = (string) $name;
			$id = ($id) ? "id=\"".$id."\"" : "id=\"".$name."\"";
			$name = "name=\"".$name."\"";
		}else{
			$name = "";
			$id = ($id) ? "id=\"".$id."\"" : "";
		}
		
		if( !preg_match("/^(0[0-9]|1[0-9]|2[01234]):([0-5][0-9])(:[0-5][0-9])?$/", $value) ){
			$timeformat = "H:i";
			if($secs) $timeformat .= ":s";
			$value = date($timeformat, time());	
		}
		
		$output  = "<div class=\"bootstrap-timepicker\">\n";
		$output .= "  <div class=\"form-group\">\n";
		if($label !== false) $output .= "    <label>".$label."</label>\n";
		if($icon) $output .= "    <div class=\"input-group\">\n";
		$output .= "      <input ".$d." ".$s." type=\"text\" ".$name." ".$id."  value=\"".$value."\" class=\"timepicker ".$class." form-control\" ".$required." ".$tabindex.">\n";
		if($icon){
			$output .= "      <div class=\"input-group-addon\">\n";
			$output .= "        <i class=\"fa fa-clock-o\"></i>\n";
			$output .= "      </div>\n";
			$output .= "    </div>\n";
		}
		$output .= "  </div>\n";
		$output .= "</div>\n";
		
		// set timePicker flag to true so that cpanel loads css and js assets
		$this->timePicker = true;
		$this->gotSeconds = (bool) $secs;

		return $output;
	}
	
	// NOTE: using 'text' as input type becuase type 'number' can't handle the comma as decimal point
	public function currency($label = false, $value = false, $name = false, $symbol = false, $required = false, $placeholder = "", $tabindex = 0, $id = false, $class = ""){
		
		// This allows the use of an array with arguments and maintain backward compatibility
		if(is_array($label)) extract($label);
		
		if(empty($name)){
			$output   = "<div class=\"form-group\">\n";
			$output  .= "  <label>Please set a name for this field!</label>\n";
			$output  .= "</div>\n";
			return $output;			
		}
		
		if(!$this->canEdit()){
			$class .= " readonly";
			$readonly = " readonly='readonly'";			
		}else{
			$readonly = "";			
			
		}
		
		
		if($value === false){
			$value = $rawvalue = "";
		}else{
			$value = $rawvalue = (float) $value;
			$value = $this->format_money('%!n', $value);
			
		}
		if(!$symbol) $symbol = "€"; // TODO usare symbolo valuta in base a cliente
		
		if($required){
			$required = "required=\"required\"";
			$label = ($label === false) ? false : $label.$this->requiredSign;
		}else{
			$required = "";
		}
		
		$tabindex = (int) $tabindex;
		$tabindex = (empty($tabindex)) ? "" : "tabindex=\"".$tabindex."\"";
		$placeholder = (empty($placeholder)) ? "" : "placeholder=\"".$placeholder."\"";
		
		$id = (empty($id)) ? $name : $id;
				
		$output  = "<div class=\"form-group\">\n";
		if($label !== false) $output .= "  <label>".$label."</label>\n";
		$output .= "  <div class=\"input-group\">\n";
		$output .= "    <input type=\"text\" ".$readonly." name=\"".$name."\" id=\"".$id."\" step=\"0.01\" min=\"0\" data-value=\"".$rawvalue."\" value=\"".$value."\" ".$required." ".$placeholder." class=\"form-control currency ".$class."\" ".$tabindex.">";
		$output .= "    <span class=\"input-group-addon\">".$symbol."</span>\n";
		$output .= "  </div>\n";
		$output .= "</div>\n";
		return $output;
			
	}
	
	public function loadTimePicker(){
		if(!$this->timePicker) return false;
		
		$secs = ($this->gotSeconds) ? "true" : "false";
		
		$out = "
		//Timepicker
		$('.timepicker').timepicker({
			minuteStep: ".$this->minuteStep.",
			showSeconds: ".$secs.",
			showMeridian: false,
			defaultTime: false,
			showInputs: false
		});		
		";
		
		return $out;

	}
		

	public function colorpicker($args){
                
        $label    = (isset($args['label'])) ? trim($args['label']) : "";
        $name     = (isset($args['name']))  ? trim($args['name']) : "";
        $value    = (isset($args['value'])) ? trim($args['value']) : ""; // altra sanificazione
        $class    = (isset($args['class'])) ? trim($args['class']) : ""; 
        $id       = (isset($args['id'])) ? trim($args['id']) : $name;
        $required = (isset($args['required'])) ? boolval($args['required']) : false;
        $disabled = (isset($args['disabled'])) ? boolval($args['disabled']) : false;
        $addon    = (isset($args['addon'])) ? boolval($args['addon']) : true;
        $tabindex = (isset($args['tabindex'])) ? (int) $args['tabindex'] : "";
        
		
		if($required){
			$required = "required=\"required\"";
			$label = (string) $label.$this->requiredSign;
		}else{
			$required = "";
		}
        
        if(!empty($label)){
            $for = (empty($id)) ? "" : " for=\"".$id."\"";
            $label  = "<label".$for.">".$label."</label>";
        }
        
		$disabled = ($disabled) ? "disabled=\"disabled\"" : "";
		$tabindex = ($tabindex) ? "tabindex=\"".$tabindex."\"" : "";
		$name = ($name) ? "name=\"".$name."\"" : "";
        $id = (empty($id)) ? "id=\"".$id."\"" : "";
        
        $output = $label;
        $output .= "
            <div class=\"input-group cc-colorpicker ".$class."\">
                <input type=\"text\" ".$name." ".$id." ".$tabindex." ".$disabled." ".$required." class=\"form-control\" value=\"".$value."\">
        ";
               
        $output .= ($addon) ? "<div class=\"input-group-addon\"><i></i></div>" : "";
               
        $output .= "</div>";
        
		
		// set colorPicker flag to true so that cpanel loads css and js assets NON UTILIZZATO PER ORA
		$this->colorPicker = true;
        $this->customColorPickerFunc = (isset($args['custom_func'])) ? boolval($args['custom_func']) : false;

		return $output;
	}
    
    
/********************************* HELPERS **********************************************/
	
	public function getSelectOptions($array, $sel, $firstEmpty = true){
		if(!is_array($array) or empty($array)) return false;
		$output = ($firstEmpty) ? "<option value=\"\"></option>\n" : "";
		foreach($array as $value=>$option){
			$selected = ($value == $sel) ? "selected=\"selected\"" : "";
			$output .= "<option ".$selected." value=\"".$value."\">".$option."</option>\n";
		}
		
		return $output;
	}
					 
	private function setInputParams($label, $name, $required, $tabindex, $id, $placeholder, $readonly = false, $disabled = false, $maxleng = 0, $min = 0, $max = 0, $step = 0, $data = false){
		
        $tooltip = false;
        
        if(strpos($label, "|") ){
            list($label, $tooltip) = explode("|", $label);
        }
        
		if($required){
			$required = "required=\"required\"";
			$label = (string) $label.$this->requiredSign;
		}else{
			$label = (string) $label;
			$required = "";
		}
		
        if($tooltip){            
            $tt = "<i data-toggle=\"tooltip\" title='".$tooltip."' class='fa fa-question-circle'></i>";
            $label .= $tt;
        }
		
		
		if(!empty($name)){
			$name = (string) $name;
            
            if( $dot  = stripos($name, ".") !== false and empty($id)){
                $id = substr($name, $dot+1);
            }             

			$id = ($id) ? "id=\"".$id."\"" : "id=\"".$name."\"";
			$name = "name=\"".$name."\"";
            
		}else{
			$name = "";
			$id = ($id) ? "id=\"".$id."\"" : "";
		}
		
		$data_elems = "";
		if(!empty($data)){
			if(is_array($data)){				
				foreach($data as $k=>$v){
					$data_elems .= (is_numeric($k)) ? "" : "data-".$k."='".$v."' ";
				}
			}
		}
		
		
		$tabindex 		= ($tabindex) ? "tabindex=\"".$tabindex."\"" : "";
		$placeholder 	= ($placeholder) ? "placeholder=\"".$placeholder."\"" : "";
		$readonly 		= ($readonly) ? "readonly=\"readonly\"" : "";
		$disabled 		= ($disabled) ? "disabled=\"disabled\"" : "";
		
		$maxleng = (int) $maxleng;
		$maxleng = (!empty($maxleng)) ? "maxlength=\"".$maxleng."\"" : "";
		$min = (int) $min;
		$min = (!empty($min)) ? "min=\"".$min."\"" : "";
		$max = (int) $max;
		$max = (!empty($max)) ? "max=\"".$max."\"" : "";
		$step = (float) $step;
		$step = (!empty($step)) ? "step=\"".$step."\"" : "";
		
		
		
		$output['label'] 		= $label; 
		$output['required'] 	= $required; 
		$output['tabindex'] 	= $tabindex; 
		$output['name'] 		= $name; 
		$output['id'] 			= $id; 
		$output['placeholder'] 	= $placeholder; 
		$output['readonly'] 	= $readonly; 
		$output['disabled'] 	= $disabled; 
		$output['maxleng'] 		= $maxleng; 
		$output['min'] 			= $min; 
		$output['max'] 			= $max; 
		$output['step'] 		= $step; 
		$output['data'] 		= $data_elems; 
		
		return $output;
	}
	
	// Get regexp string to test date based on php date() function format
	function getRegExp($format){
            $regexp = "";
            $elems = preg_split('/-|\.|\/| /', $format);
            if($elems){
				array_splice($elems, 3);
                $r = array();
                foreach($elems as $elem){
                    switch($elem){
                        case "d":
                            $r [] = "(0[1-9]|[12][0-9]|3[01])";
                            break;
                        case "m":
                            $r [] = "(0[1-9]|1[012])";
                            break;
                        case "Y":
                            $r [] = "(19|20|21)\d\d";
                            break;
                        case "y":
                            $r [] = "\d\d";
                            break;
                    }
                }
                $regexp = implode("(\/|-|.| )", $r);
                $regexp = "^".$regexp."$";
                
            
            }
            return $regexp;
        }
	
	private function format_money($format, $value){
		if(function_exists('money_format')){
			return money_format($format, $value);
		}else{
			return number_format($value, 2, ",", ".");
		}
	}
	
	/*** USER PERMISSIONS ***/
	public function setUser($user = false){
		if(is_a($user, "cc_user")) $this->user = $user;
	}
	
	private function getUser(){
		$user = $this->user;
		if(!is_a($user, "cc_user")) global $user;
		if(!is_a($user, "cc_user")){
			// if still no user try with $_user (defined in _head.php)
			global $_user;
			$user = $_user;
		}
		if(!is_a($user, "cc_user")){
			return false;
		}else{
			$this->user = $user;
		}
		return $user;		
	}
	
	private function canShow(){
		$user = $this->getUser();
		if(!$user){
			return $this->show_permission;
		}else{
			return $user->canShow();
		}
	}
	private function canAdd(){
		$user = $this->getUser();
		if(!$user){
			return $this->add_permission;
		}else{
			return $user->canAdd();
		}
	}
	private function canEdit(){
		$user = $this->getUser();
		if(!$user){
			return $this->edit_permission;
		}else{
			return $user->canEdit();
		}
	}
	private function canCopy(){
		$user = $this->getUser();
		if(!$user){
			return $this->copy_permission;
		}else{
			return $user->canCopy();
		}
	}
	private function canDelete(){
		$user = $this->getUser();
		if(!$user){
			return $this->delete_permission;
		}else{
			return $user->canDelete();
		}
	}
	private function canActivate(){
		$user = $this->getUser();
		if(!$user){
			return $this->activate_permission;
		}else{
			return $user->canActivate();
		}
	}
	
	// create html toolbar for wysiwyg
	private function wysiwyg_toolbar($id = "", $commands = array()){
		
		// set the defaults
		$defaults = array();
		$defaults['bold'] = "bold";
		$defaults['italic'] = "italic";
		$defaults['formatBlock'] = array(
			"h1" => "H1", 
			"h2" => "H2", 
			"h3" => "H3", 
			"h4" => "H4", 
			"p" => "P"
		);
		
		// overwrite defaults
		$commands = array_merge($defaults, $commands);
		
		// create id of toolbar - always starts with "toolbar"
		if(!empty($id)) $id = "-".$id;
		$id = "toolbar".$id;
		
		// create html of toolbar
		$html = "<div id=\"".$id."\">\n";
		
		foreach($commands as $command => $value){
			$command = (string) trim($command);
			if(is_array($value)){
				// multipli values
				foreach($values as $k => $v){
					$html .= "<a data-wysihtml5-command=\"".$command."\"  data-wysihtml5-command-value=\"".$k."\">".$v."</a>\n";
				}
				
			}else{
				// single value
				$value = (string) trim($value);
				$html .= "<a data-wysihtml5-command=\"".$command."\">".$value."</a>\n";
			}
		
			
		}
		
		$html .= "</div>\n";
		
		return $html;
		
	}



}
	
