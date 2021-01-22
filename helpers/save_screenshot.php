<?php
// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '../calls/_head.php';

/*** SAVE PNG / JPG FILE WITH THE SCREENSHOT ***/
if($_POST['data']){
	$data = $_POST['data'];
	if($_POST['rnd']){
		$file = md5(uniqid());
	}else{
		$pagename = SCREENSHOT_PAGENAME;
		if(!empty($_POST['pid'])){
			$pid = (int) $_POST['pid'];
			$pn = $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'");
			if($pn) $pagename = $pn;
		}
		$file = date("YmdHis", time()). "-". $pagename;
	}
	$file .= ".".SCREENSHOT_EXT;
	
	$uri =  substr($data, strpos($data,",")+1);
	file_put_contents(SCREENSHOT_PATH.$file, base64_decode($uri));
	echo $file;
	exit();
}

/*** OUTPUT THE SCREENSHOT ***/
if($_GET['file']){
	$file = SCREENSHOT_PATH.$_GET['file'];
	$delfile = (int) (isset($_GET['del'])) ? $_GET['del'] : 1;
	
	if (file_exists($file)){
		header('Content-Description: File Transfer');
		header('Content-Type: image/'.SCREENSHOT_EXT);
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		readfile($file);
		if(!empty($delfile)) unlink($file);
	}
	exit;
}

?>