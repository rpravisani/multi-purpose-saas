<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         ***
 *** filename:backup-db.php         ***
 *** system-page            ***
 ***                                               ***
 *****************************************************/

$backup_path = FILEROOT."backup/";
$tbody = "<tbody>\n";

$dir = new DirectoryIterator($backup_path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
		$filesize = $fileinfo->getSize();
		if($filesize >= 1048576){ 
			$filesize = round($filesize/1048576,0); 
			$filesize .= " MB"; 
		}else if($filesize > 1024){ 
			$filesize = round($filesize/1024,0); 
			$filesize .= " KB"; 
		}else{
			$filesize .= " Byte";
		}
			
		$mtime = date("Y-m-d H:i:s", $fileinfo->getMTime());
		
		$tbody .= "<tr><td align='center'><i class='fa fa-download text-primary puls download-backup' title='Download file'></i></td><td>".$mtime."</td><td class='backup-filename'>".$fileinfo->getFilename()."</td><td>".$filesize."</td><td align='center'><i class=\"puls text-red delete-backup fa fa-fw fa-trash\"></i></td></tr>\n";
       
    }
}

$tbody .= "</tbody>\n";

$_table->forcejs = true;

// aggiungo assets js e css aggiuntivi (in questo caso lightbox)
/*
$js_assets[] = "plugins/datatables/jquery.dataTables.min.js";
$js_assets[] = "plugins/datatables/dataTables.bootstrap.min.js";
$js_assets[] = "plugins/datatables/dataTables.dateUkTypeDetect.js";
$js_assets[] = "plugins/datatables/dataTables.dateUk.js";
$css_assets[] = "plugins/datatables/dataTables.bootstrap.css";
*/


?>