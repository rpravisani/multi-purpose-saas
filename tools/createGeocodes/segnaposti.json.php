<?php
/*** JOOMLA STUFF ***/
$tmpdir = dirname(__FILE__);
$fds = DIRECTORY_SEPARATOR;


$tmpdir_sections = explode($fds, $tmpdir);
$tmpdir_sections = array_slice($tmpdir_sections, 0, -3);
$directory = implode($fds, $tmpdir_sections).$fds;

define( '_JEXEC', 1 );
define( 'JPATH_BASE', $directory);
define( 'DS', $fds );

require_once ( JPATH_BASE .DS . 'configuration.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'factory.php' );
$mainframe = JFactory::getApplication('site');
$mainframe->initialise(); 

$db = JFactory::getDBO();

$config = JFactory::getConfig(); // oggetto configurazioni generiche Joomla

JFactory::getLanguage()->load('mod_ecmaps');  // carica il file lingua
/*** FINE JOOMLA STUFF ***/

$contatore = 0;
$mapid = (int) $_GET['map'];

$query = "SELECT id, nome, indirizzo, cap, localita, prov, nazione, email, website, tel, fax, descrizione, image
		FROM  #__ecmaps_segnaposti 
		WHERE module_id='".$mapid."'";

/*$query = "SELECT adress AS title, lon AS lng, lat 
			FROM #__ecmaps_geocodes";
*/

$db->setQuery($query);
$segnaposti = $db->loadAssocList();

$return_errors = (!empty($_GET['errors'])) ? true : false; 

if($segnaposti){
	foreach($segnaposti as $segnaposto){
		$indirizzo = strtolower($segnaposto['indirizzo']." ".$segnaposto['cap']." ".$segnaposto['localita']." ".$segnaposto['prov']." ".$segnaposto['nazione']);
		$indirizzo = trim($indirizzo);
		$title = $tooltip = $segnaposto['nome'];
		// layout della scheda
		$scheda  = "<h3 class='ec_marker_title'>".ucwords(strtolower($segnaposto['nome']))."</h3>\n";
		$scheda .= "<p class='ec_marker_adress'>";
		// img
		if(!empty($segnaposto['image'])){
			
			$img = $img_url.$segnaposto['image'];
			if(file_exists($img_dir.$segnaposto['image'])){ $scheda .= "<img class='ec_profile_photo' src='".$img."' />"; }
		}
		$scheda .= ucwords(strtolower($segnaposto['indirizzo']))."<br>\n";
		$scheda .= $segnaposto['cap']." ".ucwords(strtolower($segnaposto['localita']));
		if(!empty($segnaposto['prov'])) $scheda .= " (".strtoupper($segnaposto['prov']).")";
		//if(!empty($segnaposto['nazione'])) $scheda .= " - ".$segnaposto['nazione'];
		if(!empty($segnaposto['tel']) or !empty($segnaposto['fax']) ) $scheda .= "<br>";
		if(!empty($segnaposto['tel'])){
			$scheda .= JText::_( 'MOD_ECMAPS_TEL_LABEL', true )." ".$segnaposto['tel']; // tradurre
			if(!empty($segnaposto['fax'])) $scheda .= "<br>";
		}
		
		if(!empty($segnaposto['fax'])) $scheda .= JText::_( 'MOD_ECMAPS_FAX_LABEL' )." ".$segnaposto['fax']; // tradurre
		if(!empty($segnaposto['email'])) $scheda .= "<br><a href='mailto:".$segnaposto['email']."'>".$segnaposto['email']."</a>";
		if(!empty($segnaposto['website'])){ 
			$scheda .= "<br><a target='_blank' href='".$segnaposto['website']."'>".$segnaposto['website']."</a>"; // aggiungere controllo se ha http
		}
		
		$scheda .= "<br class='ec_clear'></p>";
		if(!empty($segnaposto['descrizione'])) $scheda .= "<p class='ec_marker_desc'>".$segnaposto['descrizione']."</p>"; // aggiungere controllo se ha http
		
		// Cerca geopos in tabella
		$query = "SELECT lon AS lng, lat 
			FROM #__ecmaps_geocodes WHERE adress = \"".$indirizzo."\"";
		
		$db->setQuery($query);
		$row = $db->loadAssoc();
		
		if($row){
			$contatore++;
			$row['title'] = $title;
			$row['content'] = $scheda;
			if(!$return_errors) $json[] = $row;

		}else{
			if($return_errors) $json['notfound'][] = $indirizzo;
		}

	}

}
if($return_errors){
	echo "<pre>";
	print_r($json);
	echo "</pre>";

}else{
	echo json_encode($json);	
}


?>