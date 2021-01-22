<?php
/* Attribuisco ad ogni articolo tutte le varianti del gruppo con prezzo standard di vendita come prezzo variante */
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

// absolute (system) path to where the images are
$import_path = "/Applications/XAMPP/xamppfiles/htdocs/cc/framework/_sources/collanine/";
$output_path = FILEROOT.PATH_PHOTO;
$completed = 0;
$msg = "";

// get all the files in a folder
$dir = new DirectoryIterator($import_path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
		$filename = $fileinfo->getFilename();
		$filesize = $fileinfo->getSize();
		
		// get the file extension
		$exp = explode(".", $filename);
		$extension = end($exp);
		// get name without extension
		array_pop($exp);
		$name = implode(".", $exp);
		// hashed name
		$hashed = sha1($filename.time()).".".$extension;

		if($db->insert(DBTABLE_MEDIA, 
					   array($name, $hashed, $filesize, '0', '0', '1'), 
					   array("name", "file", "size", "page", "record", "uploadedby")
					  )
		  ){
			// get insert id 
			$insert_id = $db->get_insert_id();

			if(!rename($import_path.$filename, $output_path.$hashed)){
				/*
				$output['error'] = $_t->get('move_media');
				$output['msg'] = $_t->get('move_media_message');
				echo json_encode($output);
				die();
				*/
				$msg .= "Errore durante copia immagine ".$filename." - DB entry ".$insert_id."<br><small>".$import_path.$filename." > ".$output_path.$hashed."</small><br>\n<br>\n";
			}else{
				$completed++;
			}
		}else{
			/*
			$output['error'] = $_t->get('record_media');
			$output['msg'] = $_t->get('record_media_message');
			echo json_encode($output);
			*/
			echo "Error durante inserimento in tab media:<br>\n".$db->getError("msg")."<br>\n".$db->getQuery();
			die();
		}
		
		
    } // end if file is not dot
	
} // end foreach

echo "Importate ".$completed." immagini.";

if(!empty($msg)){
	echo "<hr>\n";
	echo "Rilevati errori!<br>\n";
	echo $msg;
}



?>
