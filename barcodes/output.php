<?php

$supported_types = array("ean13", "code128", "code128b", "code128a", "code39", "code25", "codabar");
$save_img = ""; // holds the path and hased name of cache image if it doesn't already exists
$print =  true; // flag for printing the code under the bars

$type = (string) strtolower(trim($_GET['type']));
if(empty($type) or !in_array($type, $supported_types)) $type = "ean13";

if(!isset($_GET['cache'])) $_GET['cache'] = '1';
$cache = (empty($_GET['cache'])) ? false : true;
$cache = false;

$code  = urldecode($_GET['code']);
$size = (int) $_GET['size'];
if(empty($size)) $size = 2;

if($cache){
	$cached_img = md5($code.$type).".png";
	if(file_exists("../cache/".$cached_img)){
		$type = 'image/png';
		header('Content-Type:'.$type);
		readfile("../cache/".$cached_img);
		die();					
	}else{
		// queue to save
		$save_img = "../cache/".$cached_img;
	}	
	
}

switch($type){
		
	case "ean13":
		include 'barcode-ean13.php';
		$barcode = new Barcode($code, $size, $print);
		
		break;
	default:
		include 'barcode.php';
		
		$size="300"; // altezza
		$orientation = "horizontal";		
		$sizefactor = 4; // larghezza / spazio tra le barre

		$barcode = new Barcode($code, $size, $orientation, $type, $print, $sizefactor);
		break;		
}

if(!empty($save_img) and $cache) $barcode->save($save_img); // save img to disc if cache is on and file doesn't yet exist
$barcode->display(); //sets php header and outputs image generated by php 		





?>