<?php
class text_fields_table extends table_engine{
	
	
	// formatXxx functions must have visibility set 'protected' to work
	protected function formatLabelName($value, $params){
		
		list($label, $name) = explode("|", $value);
		
		$out['value'] = $label."<br><small class='text-muted'><em>".$name."</em></small>";
		$out['attr']  = "align='left'";
		
		return $out;
	}

}
?>