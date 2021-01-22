<?php
/*************************************************************
  EXPORT RECORDS TO CSV FILE - USES SWITCH FILE IN csv FOLDER 
 *************************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("export2csv");


// Check if page id is passed - $pid is defined in _head.php
if(empty($pid)){
	$output['error'] = $_t->get('nopage'); // translation in general section 
	$output['msg'] = $_t->get('nopage_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// get file name
$page = $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'");

// if page is not set: send error
if(!$page){
	$output['error'] = $_t->get('pagenotset'); // translation in general section 
	$output['msg'] = sprintf ($_t->get('pagenotset_message'), $pid); // translation in general section 
	echo json_encode($output);
	die();
}

// get translations specific for switch file 
$_t->setSection($page, true); // second param is true so the translations of the section will be added to the existing ones

// set filename
$page = $page.".php";

/************************************* INIT VARS AND SET DEFAULTS *************************************/

$separator = ";"; // default column separator symbol, can be overwritten in switch files
$file_list_li = ""; // list of path+files generated used for UI feedback
$file_list = array();
$clean_old_ones = false;

// list of records to be extracted - can be used in switch file or can be ignored
$records = (empty($_POST['recs'])) ? array() : $_POST['recs']; 

// filename template. Can be overwritten by ajax call or switch file Fields are : filename, ts (see date format below) and custom
$filename_tmpl = (empty($_POST['tmpl'])) ? "{{filename}}_{{ts}}" : $_POST['tmpl'];

// date format to be used in filename, can be overwritten by ajax call or switch file
$date_format = (empty($_POST['date_format'])) ? "Ymd_His" : $_POST['date_format'];

// custom part to be used in filename, can be overwritten by ajax call or switch file
$custom_name = (empty($_POST['custom_name'])) ? "" : $_POST['custom_name'];

/********************************* END INIT VARS AND SET DEFAULTS *********************************/


// include switchfile if it exists, else send error
if(file_exists("csv/".$page)){
	include_once "csv/".$page;
}else{
	$output['error'] = $_t->get('noswitch_file'); // translation in general section 
	$output['msg'] = sprintf($_t->get('noswitch_file_message'), $page); // translation in general section 
	echo json_encode($output);
	die();
}

/************************************************************************************************************ 
  SWITCH FILE RETURNS:
   - $separator [string] (usually , or ;)
   - $data - 3 dim array : array( 
 								  "file_name" => array( 
										0 => array("header1" => "value1", "header2" => "value2"), 
										1 => array("header1" => "value1", "header2" => "value2") 
								  )  
								)
************************************************************************************************************/

/*** NO DATA EXTRACTED ***/
if(empty($data)){
	$output['error'] = $_t->get('csv_nodata'); // translation in general section 
	$output['msg'] = $_t->get('csv_nodata_message'); // translation in general section 
	echo json_encode($output);
	die();
}


/*** DELETE OLD FILES IF $clean_old_ones IS TRUE AND A PREFIX FOR THE FILENAME HAS BEEN GIVEN ***/
if($clean_old_ones and !empty($prefix_name)){
	$search = FILEROOT.PATH_CSV.$prefix_name."*";	
	array_map('unlink', glob($search)); // glob gets all the filenames that match search
}


/*** CREATE FILE FROM $data ARRAY ***/

// 1st loop: files
foreach($data as $filename => $rows){
	
	// get column headers
	$header = array_keys($rows[0]);
	
	// flatten-out headers
	$content = implode($separator, $header)."\n";
	
	// use template to determine filename
	$file = namefile( $filename_tmpl, $filename, $date_format, $custom_name );
	
	// for output / feedback
	$file_list[] = PATH_CSV.$file;
	$file_list_li .= "<li><strong>".PATH_CSV.$file."</strong></li>\n";
	
	// 2nd loop: rows with data. Adds flattend array values one row at the time to the contetn variable
	foreach($rows as $row){
		
		$content .= implode($separator, $row)."\n";
		
	}
	
	// calls function in functions.php that simply saves the file in the appropriate folder
	createAttachment($content, $file);
		
}

// OUTPUT
$output['result'] = true;
$output['error'] = $_t->get('csv_ok');  // ok message
$output['msg'] = sprintf ($_t->get('csv_ok_message'), $file_list_li); // file list
$output['file_list'] = $file_list;
$output['errorlevel'] = "";
echo json_encode($output);



function namefile( $template, $filename, $format, $custom ){
	
	$date = date($format);
	if(!empty($filename)) $template = str_replace("{{filename}}",$filename, $template);
	if(!empty($custom)) $template = str_replace("{{custom}}",$custom, $template);
	$template = str_replace("{{ts}}",$date, $template);
	return $template.".csv";
}

?>
