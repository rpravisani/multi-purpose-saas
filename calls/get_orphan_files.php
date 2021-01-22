<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$file_list = array();
$files_found = 0;


// get list of active files in pages table
$active_files = $db->col_value("file_name", "pages", "ORDER BY file_name");

// get list of files in model dir
$dir = FILEROOT."models";
$files = scandir($dir);

if($files){
	
	foreach($files as $file){
		$file_name = substr($file, 0, -4);
		$path = $dir."/".$file;
		if(in_array($file_name, $active_files) or $file == "." or $file == ".." or $file[0] == '.' or is_dir($path)) continue;
		$models[] = $file;
	}
}

// get list of files in model dir
$dir = FILEROOT."views";
$files = scandir($dir);
if($files){
	foreach($files as $file){
		$file_name = substr($file, 0, -4);
		$path = $dir."/".$file;
		if(in_array($file_name, $active_files) or $file == "." or $file == ".." or $file[0] == '.' or is_dir($path)) continue;
		$views[] = $file;
	}
}

// get list of files in css/pages dir
$dir = FILEROOT."css/pages";
$files = scandir($dir);
if($files){
	foreach($files as $file){
		$file_name = substr($file, 0, -4);
		$path = $dir."/".$file;
		if(in_array($file_name, $active_files) or $file == "." or $file == ".." or $file[0] == '.' or is_dir($path)) continue;
		$css[] = $file;
	}
}

// get list of files in js/pages dir
$dir = FILEROOT."js/pages";
$files = scandir($dir);
if($files){
	foreach($files as $file){
		$file_name = substr($file, 0, -3);
		$path = $dir."/".$file;
		if(in_array($file_name, $active_files) or $file == "." or $file == ".." or $file[0] == '.' or is_dir($path)) continue;
		$js[] = $file;
	}
}

$nfiles['models'] = count($models);
$nfiles['views']  = count($views);
$nfiles['css']    = count($css);
$nfiles['js']     = count($js);

$rows = max($nfiles);

$html  = "<div id=\"orphan-files\" style=\"overflow: scroll; height: 60vh;\">\n";
$html .= "<table class='table no-margin'>\n";
$html .= " <thead>\n";	
$html .= "   <tr><th>#</th><th>MODELS</th><th><input class='select-column' type='checkbox' data-column='models'></th><th>VIEWS</th><th><input class='select-column' type='checkbox' data-column='views'><th>CSS</th><th><input class='select-column' type='checkbox' data-column='css'><th>JS</th><th><input class='select-column' type='checkbox' data-column='js'></tr>\n";	
$html .= " </thead>\n";	
$html .= " <tbody>\n";

for($q=0; $q<$rows; $q++){
	$model = array_shift($models);
	$view = array_shift($views);
	$c = array_shift($css);
	$j = array_shift($js);
	
	$html .= "   <tr>";
	$html .= "<td>".$q."</td>";
	
	$html .= "<td id='m".$q."'>".$model."</td><td>";
	$html .= (empty($model)) ? "" : "<input class='models orphan' type='checkbox' value='models/".$model."' name='m".$q."'>";
	$html .= "</td>";
	
	$html .= "<td id='v".$q."' >".$view."</td><td>";
	$html .= (empty($view)) ? "" : "<input class='views orphan' type='checkbox' value='views/".$view."' name='v".$q."'>";
	$html .= "</td>";
	
	$html .= "<td id='c".$q."' >".$c."</td><td>";
	$html .= (empty($c)) ? "" : "<input class='css orphan' type='checkbox' value='css/pages/".$c."' name='c".$q."'>";
	$html .= "</td>";
	
	$html .= "<td id='j".$q."' >".$j."</td><td>";
	$html .= (empty($j)) ? "" : "<input class='js orphan' type='checkbox' value='js/pages/".$j."' name='j".$q."'>";
	$html .= "</td>";
	
	$html .= "</tr>\n";	
}

$html .= " </tbody>\n";	
$html .= "</table>\n";
$html .= "</div>\n";

$html .= '<div class="row"><div class="col-md-2">';
$html .= '<button disabled type="button" id="delete-pages" class="btn btn-success" style="margin-top: 7px">Delete</button>';
$html .= '</div>';
$html .= '<div class="col-md-10">';
$html .= '<p id="delmsg"></p>';
$html .= '</div>';
$html .= '</div>';

$output['result'] = true;
$output['html'] = $html;


echo json_encode($output);

	
?>
