<?php
defined('_CCCMS') or die;

$tipo_uscite 	= $db->key_value("id", "nome", DBTABLE_TIPO_USCITE, "WHERE active = '1'");

$qry_automezzi = "SELECT id, CONCAT(numero_radio, '/', targa) AS codice, strade_strette FROM ".DBTABLE_AUTOMEZZI." WHERE active='1'";
$automezzi = $db->fetch_array($qry_automezzi);

$autisti 		= $db->key_value("id", "nome", DBTABLE_MILITI, "WHERE autista = '1' AND active = '1'");
$caposquadra 	= $db->key_value("id", "nome", DBTABLE_MILITI, "WHERE caposquadra = '1' AND active = '1'");
$militi 			= $db->key_value("id", "nome", DBTABLE_MILITI, "WHERE esce = '1' AND autista = '0' AND caposquadra = '0' AND active = '1'");
?>