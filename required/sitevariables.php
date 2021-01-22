<?php
/*
  Definizione variabili percosi del sito 
*/
define( 'DS', DIRECTORY_SEPARATOR );

if (file_exists("c:\\xampp-portable7\\htdocs\\islocalhost")){
	define ('LOCALHOST', true);
}else{
	define ('LOCALHOST', false);
}


/**************************************
*  Definizione variabili di sistema   *
***************************************/

/* Definizione variabili di sistema */

define ('HOSTROOT', $_SERVER['SERVER_NAME']);

if (LOCALHOST){
	
	define ('HTTP_PROTOCOL', 'http://');
	define ('SITEROOT', '/cc/multi-purpose-saas/');
	define ('PUBLIC_DIR', '');

	define ('DB_NAME', 'multi-purpose-saas'); 
	define ('DB_HOST', 'localhost');
	define ('DB_USER', 'root');
	define ('DB_PWD', '');
	
	define ('FILEROOT', '/xampp-portable7/htdocs'.SITEROOT);
	
	define ('TABLE_PREFIX', '');
   
}else{
	
	define ('HTTP_PROTOCOL', 'https://');
    define ('SITEROOT', '/');
	define ('PUBLIC_DIR', '');
	
	define ('DB_NAME', 'jt36jl4b_taskreporter');
	define ('DB_HOST', 'localhost');
	define ('DB_USER', 'jt36jl4b_ccadmin');
	define ('DB_PWD', 'Z2=2pi;_E+k~');
	
	define ('FILEROOT', '/home/jt36jl4b/taskreporter.online'.SITEROOT);

	define ('TABLE_PREFIX', ''); 
	
}

define ('ADMINSECTION', ''); 

/******************************
*  Definizione path cartelle  *
*******************************/

define ('PATH_REQUIRED', 'required/');
define ('PATH_CSS', 'css/');
define ('PATH_CSS_PAGES', PATH_CSS.'pages/');
define ('PATH_JS', 'js/');
define ('PATH_JS_PAGES', PATH_JS.'pages/');
define ('PATH_CSV', 'csv/');
define ('PATH_PDF_TEMP', 'tmp/');
define ('PATH_IMMAGINI', 'images/');
define ('PATH_PHOTO', 'photo/');
define ('PATH_UPLOAD_MODULE', 'swfupload/'); // old
define ('PATH_PLUGINS', 'plugins/');
define ('PATH_DATATABLE_JS', PATH_PLUGINS.'datatables/');
define ('PATH_CACHE_FILES', FILEROOT.'cache/');
define ('PATH_BACKUP_DB', FILEROOT.'backup/');


?>
