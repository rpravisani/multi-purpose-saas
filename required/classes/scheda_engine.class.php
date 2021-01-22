<?php

class scheda_engine{
	
	private $record = false, $implementabile = array(), $implementabileMulti = array();
	private $errorClass = "error", $markObbligatorio = true, $segnoObbligatorio = "*";
	
	
	public function __construct($record = false, $implementabile = array()){
		$this->record = $record;
		$this->implementabile = (is_array($implementabile)) ? $implementabile : array( $implementabile );
	}
	
	/*** FUNZIONI DI CONFIG ***/
	public function evidenziaObbligatorio($set = true){
		$this->markObbligatorio = $set;
	}

	public function segnoObbligatorio($segno){
		$this->segnoObbligatorio = $segno;
	}
	
	/*** FUNZIONI DI LAYOUT ***/
	
	// crea dom li : html contenuto, evntuale id e eventuale classe
	public function li($html = array(), $id = false, $class = false,  $visible = true){
		if(!is_array($html)) $html = array($html);
		$html = implode("\n", $html);
		$out = "<li";
		if($id) $out .= " id='li_".$id."'";
		if($class) $out .= " class='".$class."'";
		if(!$visible) $out .= " style='display: none'";
		$out .= ">\n";
		$out .= $html;
		$out .= "\n</li>\n";
		return $out;
	}
	
	private function block( $fields = false, $side = "leftside", $help = false, $close = false, $width = "fifty", $id = false, $extra_class = ""){
		if(!$fields) return "<span class='".$this->errorClass."'>Nessun campo passato!</span>\n";
		if(!is_array($fields)) $fields = array($fields); // mi assicuro che sia un array
		$out = "";
		if($id){
			$firstId = $secondId = "id=\"";
			$firstId .= $id."\"";
			$secondId .= $id."Holder\"";
		}
		$out .= "  <div ".$firstId." class=\"sx ".$width." ".$extra_class."\">\n";
		$out .= "    <div ".$secondId." class=\" ".$side;
		if($help) $out .= " gothelp";
		$out .= "\">\n";
		foreach($fields as $field){
			$out .= "<div class='sx'>\n";
			$out .= $field."\n";
			$out .= "</div>\n";
		}
		$out .= "<br class='clear' />\n";
		if($help) $out .= "      <p class=\"guidelines\" ><small>".$help."</small></p>\n";
		$out .= "    </div>\n";
		$out .= "  </div>\n";
		if($close) $out .= "  <br class=\"clear\" />\n";

		return $out;
	}
	
	// produce blocco con grafica per lato sx 
	public function leftSide( $fields = false, $help = false, $width = "fifty", $id = false, $extra_class = ""){
		return $this->block( $fields, "leftside", $help, false, $width, $id, $extra_class);
	}

	// produce blocco con grafica per lato dx -- chiude in automatico
	public function rightSide( $fields = false, $help = false, $width = "fifty", $id = false, $extra_class = ""){
		return $this->block( $fields, "rightside", $help, true, $width, $id, $extra_class);
	}
	
	// crea blocco unico - input : $fields = false, $help = false, $id = false, $extra_class = ""
	public function oneSide( $fields = false, $help = false, $id = false, $extra_class = ""){
		if(!$fields) return "<span class='".$this->errorClass."'>Nessun campo passato!</span>\n";
		if(!is_array($fields)) $fields = array($fields); // mi assicuro che sia un array
		$out = "";
		if($id){
			$firstId = $secondId = "id=\"";
			$firstId .= $id."\"";
			$secondId .= $id."Holder\"";
		}
		$out .= "  <div ".$firstId." >\n";
		$out .= "    <div ".$secondId." class=\"oneside\">\n";
		
		foreach($fields as $field){
			$out .= "      <div class=\"sx ";
			if($help) $out .= "gothelp ";			
			$out .= $extra_class;
			$out .= "\">\n";
			
			$out .= $field."\n";
			if($help) $out .= "      <p class=\"guidelines\" ><small>".$help."</small></p>\n";
			$out .= "    </div>\n"; // sx
		}
		$out .= "  <br class=\"clear\" />\n";
		$out .= "    </div>\n";
		$out .= "  </div>\n";

		return $out;
		
	}
	
	// hack per chiudere form ed aggiungere altro html come p.e. tabella
	public function closeForm(){
		return "</ul>\n</form>\n";
	}
	
	
	/*** CAMPI ***/
	
	public function text($id = false, $label = false, $value, $width="medium", $obbligatorio = false, $dato = true, $maxlength = '255', $didascalia = false, $readonly = false, $disabled = false){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$out = "";
		$ds = (stripos($width, "number")) ? "data-sys=\"number\"" : "";
		$ds = (stripos($width, "float")) ? "data-sys=\"float\"" : "";
		if($label){
			if($this->markObbligatorio and $obbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
		}
		//$out .= "<div>\n";
		$out .= "  <input ".$ds." type=\"text\" id=\"".$id."\" name=\"".$id."\" class=\"element text ".$width;
		if($dato) $out .= " dato";
		if($obbligatorio) $out .= " obbligatorio";
		$out .= "\" value=\""; // chiudo classe, apro value
		//if ($this->record) $out .= $value;
		$out .= $value;
		$out .= "\""; // chiude value=
		if($readonly) $out .= " readonly=\"readonly\"";
		if($disabled) $out .= " disabled=\"disabled\"";
		$out .= " maxlength=\"".$maxlength."\" />";
		if($didascalia) $out .= "&nbsp<small>".$didascalia."</small>";
		//$out .= "\n</div>\n";
		return $out;
	}

	public function checkbox($id = false, $label = false, $checked = false, $didascalia = false, $disabled = false, $value = '1', $dato = true){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$out = "";
		if($label){
			if($this->markObbligatorio and $obbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
		}
		$out .= "<div>\n";
		if($dato) $out .= "  <input type=\"hidden\" id=\"".$id."\" name=\"".$id."\" class=\"dato\" value=\"0\">\n";
		$out .= "  <input type=\"checkbox\" id=\"".$id."\" name=\"".$id."\" class=\"element chck ";
		if($dato) $out .= " dato";
		$out .= "\" value=\"".$value."\""; 
		if($disabled) $out .= " disabled=\"disabled\"";
		if($checked) $out .= "checked=\"checked\"";
		$out .= "/>";
		if($didascalia) $out .= "&nbsp;<small>".$didascalia."</small>";
		$out .= "\n</div>\n";
		return $out;
	}

	public function datum($id = false, $label = false, $ts = false, $obbligatorio = false, $input = "ts", $readonly = false, $disabled = false, $format = "d/m/Y", $dato = true){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		if($ts){
			switch($input){
				case "yyyy-mm-dd": 
					if($ts == "0000-00-00" or empty($ts)){
						$ts = 0;
					}else{
						$ts = strtotime($ts);
					}
					break;
				case "dd-mm-yyyy": 
					if($ts == "00-00-0000"){
						$ts = 0;
					}else{
						$e = explode("-", $ts);
						$ts = $e[2]."-".$e[1]."-".$e[0];
						$ts = strtotime($ts);
					}
					break;
				case "yyyymmdd": 
					$e[2] = substr($ts, 0, 4);
					$e[1] = substr($ts, 2, 2);
					$e[0] = substr($ts, 4, 2);
					$ts = $e[2]."-".$e[1]."-".$e[0];
					$ts = strtotime($ts);
					break;
				default:
					$ts = $ts;
					break;
			}
			$data = date($format, $ts);
			
		}else{
			$ts = '0';
			$data = "";
		}
		$id_df = "df_".$id;
		$out = "";
		if($label){
			if($this->markObbligatorio and $obbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id_df."\">".$label."</label>\n";
		}
		$out .= "<div>\n";
		$out .= "  <input type=\"text\" class=\"element text datum date-size";
		if($obbligatorio) $out .= " obbligatorio";
		$out .= "\" value=\"".$data."\"";
		if($readonly) $out .= " readonly=\"readonly\"";
		if($disabled) $out .= " disabled=\"disabled\"";
		$out .= " id=\"".$id_df."\"";
		$out .= " >\n"; // chiudo tag
		$d = ($dato) ? "dato" : "";
		$out .= "  <input type=\"hidden\" id=\"".$id."\" name=\"".$id."\"  class=\"ts ".$d."\" value=\"".$ts."\">\n";
		$out .= "</div>\n";
		return $out;
	}

	public function hidden($id = false, $value, $dato = true){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$d = ($dato) ? "class=\"dato\"" : "";
		$out = "<input type=\"hidden\" id=\"".$id."\" name=\"".$id."\"  ".$d." value=\"".$value."\">\n";
		return $out;
	}

	// campo <select> -- $id = false (int), $label = false (text), $options (html), $obbligatorio = false, $chosen = true, $multi = false, $placeholder = false, $dato = true, $readonly = false, $disabled = false
	public function select($id = false, $label = false, $options, $obbligatorio = false, $chosen = true, $placeholder = "Seleziona opzione", $tabella = false, $extra_implement = "", $width = false, $didascalia = false, $splitsearch = true, $multi = false, $dato = true, $readonly = false, $disabled = false){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$out = "";
		if($label){
			if($this->markObbligatorio and $obbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
		}
		$_w = ($width) ? "style=\"width: ".$width."px;\"" : "";
		$out .= "<div ".$_w.">\n";
		
		if($options){
			$out .= "<select id=\"".$id."\" name=\"".$id."\" class=\"";
			if($dato) $out .= "dato ";
			if($obbligatorio) $out .= "obbligatorio ";
			if($chosen){
				$out .= ($obbligatorio) ? "ccselect_obb" : ($splitsearch) ? "ccselect_ext" : "ccselect ";
			}
			$out .= "\" ";
			if($placeholder) $out .= " data-placeholder=\"".$placeholder."\" ";
			if($multi) $out .= "multiple ";
			if($readonly) $out .= "readonly=\"readonly\" ";
			if($disabled) $out .= "disabled=\"disabled\" ";
			$out .= ">\n"; // chiudo select
			$out .= $options."\n";
			$out .= "</select>\n";
			if(in_array($id, $this->implementabile)){
				$out .= "&nbsp;<div class=\"implement\" data-extra='".$extra_implement."' data-table=\"".$tabella."\" data-field=\"".$id."\">Nuovo</div>\n";
			}else if(in_array($id, array_keys($this->implementabileMulti))){
				$out .= $this->implementabileMulti[$id];
			}
			
		}else{
			$out .= "<p class=\"error\">Nessun dato trovato</p>\n";
		} // fine if options
		
		$out .= "</div>\n";
		return $out;
	}

	// campo <textarea> -- valori per width e height: small, small-fixed, medium, medium-fixed, large, large-fixed (aggiunge -width e -height in automatico)
	public function textarea($id = false, $label = false, $text, $obbligatorio = false, $width = "medium", $height= "medium", $extra_class = "", $dato = true, $readonly = false, $disabled = false){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$out = "";
		if($label){
			if($this->markObbligatorio and $obbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
		}
		$out .= "<div>\n";
		
		$out .= "  <textarea id=\"".$id."\" name=\"".$id."\" class=\"element text ";
		if($dato) $out .= "dato ";
		if($obbligatorio) $out .= "obbligatorio ";
		$out .= $width."-width ";
		$out .= $height."-height ";
		$out .= $extra_class." ";
		$out .= "\" "; // chiudo class
		if($disabled) $out .= "disabled=\"disabled\" ";
		$out .= ">"; // chiudo tag
		//if ($this->record) $out .= $text;
		$out .= trim($text);
		$out .= "</textarea>\n"; // chiudo tag
		
		$out .= "</div>\n";
		return $out;
	}

	// prende un'array e crea insieme di radiobox
	public function radio($id=false, $label = false, $values = array(), $checked, $senso="hor", $extra_class="", $dato = true, $readonly = false, $disabled = false){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		if(empty($values)) return false;
		$radios = $out = "";
		foreach($values as $key=>$value){
			if($key==$checked){
				$selected = "checked=\"checked\" ";
			}else{
				$selected = "";
			}
			$classe = "class=\"dato ".$extra_class."\" ";
			
			$dis = ($disabled) ? " disabled=\"disabled\"" : "";
			
			$radios .= "<input type=\"radio\" name=\"".$id."\" id=\"".$id."-".strtolower($value)."\" value=\"".$key."\" ".$selected." ".$classe." ".$dis."/>&nbsp;".$value;
			if($senso == "hor"){
				$radios .= "&nbsp;&nbsp";
			}else{
				$radios .= "<br />\n";
			}
		}
		if($label){
			if($this->markObbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
		}
		$out .= "<div>\n";
		$out .= $radios;
		$out .= "</div>\n";
		
		return $out;
	}

	public function valuta($id = false, $label = false, $value, $width="medium", $obbligatorio = false, $decimal = 2, $symbol = "€", $dato = true, $maxlength = '255', $readonly = false, $disabled = false){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$out = "";
		$value = (float) $value;
		$value = number_format($value, $decimal, ",", ".");
		if($label){
			if($this->markObbligatorio and $obbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
		}
		$out .= "<div>\n";
		$out .= "  ".$symbol."&nbsp;<input type=\"text\" data-sys=\"float\" id=\"".$id."\" name=\"".$id."\" class=\"element valuta text ".$width;
		if($dato) $out .= " dato";
		if($obbligatorio) $out .= " obbligatorio";
		$out .= "\" value=\"";
		//if ($this->record) $out .= $value;
		$out .= $value;
		$out .= "\""; // chiude value=
		if($readonly) $out .= " readonly=\"readonly\"";
		if($disabled) $out .= " disabled=\"disabled\"";
		$out .= " maxlength=\"".$maxlength."\" />\n";
		$out .= "</div>\n";
		return $out;
	}

	// prende come valore array serializzato e crea un campo per ogni valore con stellina e x per elimnare
	public function multiRow($id = false, $label = false, $serialized, $size = "medium", $readonly = false, $disabled = false){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$rows = unserialize($serialized);
		if(!$rows) $rows[0] = "";
		$out = "";
		if($label){
			if($this->markObbligatorio and $obbligatorio) $label .= $this->segnoObbligatorio;
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
			$out .= "<input type=\"hidden\" class=\"dato\" name=\"".$id."_num\" id=\"".$id."_num\" autocomplete=\"off\" value=\"0\">";
		}
		$out .= "<div>\n";
		
		//loop
		foreach($rows as $ne => $dato){
			if($ne == '0'){
				$stella = "<span title=\"Email predefinita\" ref=\"".$id."_num\" indice=\"".$ne."\" stato=\"on\" class=\"stella stella-on\"></span>\n";
			}else{
				$stella = "<span title=\"Imposta come email predefinita\" ref=\"".$id."_num\" indice=\"".$ne."\" stato=\"off\" class=\"stella\"></span>\n";
			}
			if($readonly or $disabled) $stella = "";
			
			$out .= "<div class=\"multi-holder\">\n";
			$out .= "<input id=\"".$id."_".$ne."\" name=\"".$id."_".$ne."\" class=\"multi-input element text ".$size." dato\" type=\"text\" maxlength=\"".$maxlength."\" ";
			$out .= "value=\"".$dato."\" ";			
			if($readonly) $out .= "readonly ";
			if($disabled) $out .= "disabled ";
			$out .= "/>";
			$out .= "<span class=\"iks\">X</span>".$stella;
			$out .= "</div>\n";
			
		} // fine foreach
		
		$out .= "<br id=\"fine_".$id."\" class=\"clear\">\n";
		$out .= "</div>\n";
		
		if(!$readonly and !$disabled) $out .= "<div ref=\"".$id."\" class=\"addFieldBtn\" title=\"Aggiungi una riga\">+</div>\n";

		return $out;
	}

	
	public function stelline($id=false, $label = false, $selected=0, $tot = 5, $dato = true, $disabled = false){
		if(!$id) return "<span class='".$this->errorClass."'>Nessun id campo passato!</span>\n";
		$out = "";
		$tot = (int) $tot;
		$selected = (int) $selected;
		if($selected > $tot) $selected = $tot;
		$unselected = (int) $tot - $selected;
		$c = 1;
		$class = "starholder";
		if($disabled) $class .= "-inactive";
		$o = "<div class='".$class."'>\n";
		for($s=0;$s<$selected;$s++){
			$o .= "<div data-val='".$c."' data-id='".$id."' data-stato='on' class='sx stella stella-on'></div>\n";
			$c++;		
		}
		for($s=0;$s<$unselected;$s++){
			$o .= "<div data-val='".$c."' data-id='".$id."' data-stato='off' class='sx stella'></div>\n";
			$c++;		
		}
		$o .= "<br class='clear'></div>\n";
		
	
		if($label){
			$out .= "<label class=\"description\">".$label."</label>\n";
		}
		if($dato){
			$out .= "<input type='hidden' name='".$id."' id='".$id."' value='".$selected."' class='dato'>\n";
		}
		$out .= $o;
		
		return $out;
	}
		
	public function translate($rel = false, $label = false, $value, $sigla, $lang, $width="medium", $maxlength = '255', $readonly = false, $disabled = false){
		if(!$rel) return "<span class='".$this->errorClass."'>Nessun rel campo passato!</span>\n";
		$out = "";
		$id = $rel."_".$sigla;
		if($label){
			$out .= "<label class=\"description\" for=\"".$id."\">".$label."</label>\n";
		}
		$out .= "<div>\n";
		$out .= "  <input type=\"text\" id=\"".$id."\" name=\"".$id."\" data-rel=\"".$rel."\" data-lang=\"".$lang."\" class=\"element text tradotto ".$width;
		$out .= "\" value=\"";
		//if ($this->record) $out .= $value;
		$out .= $value;
		$out .= "\""; // chiude value=
		if($readonly) $out .= " readonly=\"readonly\"";
		if($disabled) $out .= " disabled=\"disabled\"";
		$out .= " maxlength=\"".$maxlength."\" />\n";
		$out .= "</div>\n";
		return $out;
	}
	
	public function implementMulti($mod = false, $field = false, $title = "", $return_option = false, $return_value = "id", $label = "Nuovo", $type="fancybox.iframe"){
		$out = "";
		if(!$mod) 	$out .= "<span class='".$this->errorClass."'>Nessun modulo settato!</span>\n";
		if(!$field) 	$out .= "<span class='".$this->errorClass."'>Nessun campo settato!</span>\n";
		if(!is_array($return_option)) $return_option = array($return_option);
		$return_options = serialize($return_option);
		if(empty($out)){
			$title = urlencode($title);
			$out = "<div>\n<a href='newbox.php?mod=".$mod."&return_value=".$return_value."&return_option=".$return_options."&field=".$field."&title=".$title."' class=\"newbox ".$type."\">".$label."</a>\n</div>\n";
		}
		$this->implementabileMulti[$field] = $out;
		return true;
	}
	

}

?>