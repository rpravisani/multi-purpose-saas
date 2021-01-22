<?php
session_start();
$debug = false;

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';
include_once '../required/classes/table_engine.class.php';
include_once '../required/classes/cc_user.class.php';

$db = new cc_dbconnect(DB_NAME);

if(!empty($_GET['pf'])){
	$param = unserialize($_GET['pf']);
}else{
	$param = array();
}

$utente = new cc_user($_SESSION['login_id'], $_SESSION['login_type'], $_SESSION['rifid'], $db);
$table = new table_engine($_GET['mod'], $_GET['f'], $param, $db, $utente);

$qry = $table->getQry(true); // true: template

$aAliases = $table->getAliases();
$aColumns = $table->getFields(); 

$_id = array_shift($aAliases); // tolgo il primo valore (di norma id) e lo attribuisco a $_id
$sIndexColumn = "id";

$cqry =  $table->getCQry();
if(stripos($qry, "GROUP")){
	$fff = $db->fetch_array($cqry, MYSQLI_NUM);
	$iTotal = $iFilteredTotal = count($fff);
}else{
	$iTotal = $iFilteredTotal = $table->getTotRecs();
}
//$iFilteredTotal = $_GET['iDisplayLength'];

	
/* 
 * Paging OK
 */

if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ){
	$from = intval( $_GET['iDisplayStart'] );
	$to   = intval( $_GET['iDisplayLength'] );
	
}else{
	$from = 0;
	$to   = false;
}

/*
 * Ordering
 */

$sOrder = "";
if ( isset( $_GET['iSortCol_0'] ) ){ // è la colonna per cui ordinare
	$sOrder = " ORDER BY  ";
	for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ){ // numero di colonne per cui ordinare , di norma = 1
		if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ){ // controllo se la colonna cliccate è ordinabile
			$colonnaOrdine = $aAliases[ intval( $_GET['iSortCol_'.$i] )]; // aAliases = array con alias
			//if($colonnaOrdine == "Località") $colonnaOrdine = "Paese";
			$_ordcol = "`".$colonnaOrdine."`";
			
			if(stripos($_ordcol, "+")){
				$_oc = array();
				$_splitted = explode("+", $_ordcol);
				foreach($_splitted as $_splitelem){
					$_splitelem = trim($_splitelem);
					if (preg_match("/^[a-zA-Z0-9._-]+/", $_splitelem)) {
						$_tt = $_splitelem;
						if(stripos($_tt, ".")){
							$_splitted2 = explode(".", $_tt);
							$_tt = $_splitted2[1];
						}
						$_oc[] = $_tt;
					}
				}
				if(!empty($_oc)) $_ordcol = implode(",", $_oc);
			}
		
			$sOrder .= $_ordcol." ".($_GET['sSortDir_'.$i]==='asc' ? 'ASC' : 'DESC') .", ";
		}
	}
	
	
	$sOrder = substr_replace( $sOrder, "", -2 );
	if ( $sOrder == " ORDER BY" ){
		$sOrder = "";
	}
}


/* 
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 * Doppio % nei LIKE x far sì che il sprintf() vada a buon fine
 */


$sWhere = "";
if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ){
	$sWhere = (stripos($qry, "WHERE")) ? " AND (" : " WHERE(";
	
	for ( $i=0 ; $i<count($aColumns) ; $i++ ){
		$_exp = explode(".", $aColumns[$i]);
		if($_exp[1] != "id"){
			$_safe = $db->make_data_safe($_GET['sSearch']);
			$sWhere .= $aColumns[$i]." LIKE '%%".$_safe."%%' OR ";
		}
		//if($aColumns[$i] == "Località") $aColumns[$i] = "Paese";
		//$sWhere .= "[".$aColumns[$i]."] LIKE '%%". $_GET['sSearch']."%%' OR ";
	}
	$sWhere = substr_replace( $sWhere, "", -3 );
	$sWhere .= ')';
}

/* Individual column filtering - nel mio caso prima lettera della colonna, infatti nello snipet che crea la query ho tolto le prime %% */
for ( $i=0 ; $i<count($aColumns) ; $i++ ){
	if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ){
		if ( $sWhere == "" and !stripos($qry, "WHERE")){
			$sWhere = "WHERE ";
		}else{
			$sWhere .= " AND ";
		}
		$_safes = $db->make_data_safe($_GET['sSearch_'.$i]);
		$_colindex = $i+1; // poiché c'è sempre una prima colonna (id) invisibile in tabella
		$sWhere .= $aColumns[$_colindex]." LIKE '".$_safes."%%' ";
	}
}



/*
$cQry = $qry;
if($sWhere != ""){
	$cQry .= $sWhere;
}

$cQry .= $sLimit;
if($debug) echo "cQry : ".$cQry."<br>\n";

$table->setQry($cQry, true, true);
$iFilteredTotal = $table->getTotRecs();

if($debug) echo "iFilteredTotal #2 : ".$iFilteredTotal."<br>\n";
*/

// se ho effettuato una ricerca devo spostare eventuale GROUP BY dopo la ricerca
$extract = "";
if(!empty($sWhere) and stripos($qry, "GROUP")){

	$start = stripos($qry, "GROUP");
	$end = stripos($qry, "ORDER");
	if(empty($end)) $end = stripos($qry, "LIMIT");
	if(empty($end)) $end = strlen($qry);
	$leng = $end-$start;

	$extract = substr($qry, $start, $leng);
	$qry = substr_replace($qry, '', $start, $leng);
	
}


$qry .= $sWhere. " ".$extract;

// get filtered totale num of rows
$qrycount = sprintf ($qry, "COUNT(*)");
$count = $db->fetch_array($qrycount, MYSQLI_NUM);
if(stripos($qrycount, "GROUP")){
	$iFilteredTotal = count($count);
}else{
	$iFilteredTotal = $count[0];
}

$newQry = $qry.$sOrder;

if($to !== false){
	$newQry .= " LIMIT ".$from.", ".$to;
}

$table->setQry($newQry, true, false); // sostituisco la query template senza fare backup

$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"aaData" => array(), 
		"qry" => $newQry, 
		"dbg" => var_export($_GET, true), 
		"iTotalDisplayRecords" => $iFilteredTotal
	);
	
if(!$table->execQry()){
	// si può assumere che non è stato trovato alcun record?
	if(empty($table->error['sqlstatus'])){
		echo json_encode( $output );
		die();
	}else{
		die("err:".$table->error['debug_msg']);
	}
}

if($_GET['sSearch'] != ""){
	$fullarray= $table->getFormattedData("fullarray", $_safe); // per evidenziare chiave di ricerca
}else{
	$fullarray= $table->getFormattedData("fullarray");
}

$output['aaData'] = $fullarray;
//$output['qry'] = $table->getQry(false);

if(!$debug) echo json_encode( $output );
?>
