<?php
$tables = LOGIN_TABLE;

$dt = new DateTime();




if($action == 'insert'){

	if(empty($safevalues['password'])) die("no password!"); // TODO: error handling
	if(empty($safevalues['subscription_type'])) die("no ruolo!"); // TODO: error handling
	
	$safevalues['password'] = $_user->hashPassword($safevalues['password']);
	//$safevalues['password'] = sha1($safevalues['password']);
	
	/*** valori default (se insert) ***/
	$dt->modify("+5 years");
	$safevalues['language'] = 'it';
	$safevalues['nation'] = '109';
	$safevalues['city'] = 'BSS';
	$safevalues['timezone'] = 'Europe/Rome';
	$safevalues['avatar'] = 'essepi.png';
	$safevalues['subscription_date'] = date("Y-m-d");
	$safevalues['last_renew'] = date("Y-m-d");
	$safevalues['expiry_date'] = $dt->format("Y-m-d");
	$safevalues['payment_method'] = '1';
	$safevalues['checked'] = '1';
	
	
}else{
	if(empty($safevalues['password'])){
		unset($safevalues['password']);
	}else{
		$safevalues['password'] = $_user->hashPassword($safevalues['password']);		
	}
	$safevalues['last_renew'] = $dt->format("Y-m-d");
	$dt->modify("+5 years");
	$safevalues['expiry_date'] = $dt->format("Y-m-d");	
}



?>