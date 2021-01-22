<?php

/*********************************************************************************************************
 * PRE-AUTHENTICATION LANGUAGE HANDLER                                                                   *
 * LANGUAGE IS ONLY TEMPORARILY SET THROUGH COOKIE, BROWSER OR USING DEFAULT VALUE                       *
 * ACTUAL COOKIE WILL BE WRITTEN IN ACCESS CONTROL SCRIPT AFTER AUTHENTICATION                           *
 * Structure of cookie: $_COOKIE[COOKIE_NS] = "XX:NN:Continent/City:md5(user_agent)"                     *
 * where XX = 2-letter lang code, NNN = id of nations table                                              *
 * ex: $_COOKIE['EvertechPHPBootstrapFramework'] = "it:109:Europe/Rome:717b34b810e6c7742c46bb55863e8367" *                         *
 * ===================================================================================================== *
 * Definition of $db is assumed                                                                          *
 *********************************************************************************************************/

// INCLUDE IF NOT ALREADY INCLUDED
include_once 'classes/cc_translations.class.php';
include_once 'classes/user_cookie.class.php';

 // create cookie instance to get language code for user (if any) 
$usercookie = new user_cookie();

 // get 2-letter code from cookie - if no cookie is set, this value will be false
$lang_code = $usercookie->getLang();

// Pass lang code and section (LOGIN) to translation class. 
// If lang_code = false, the class will get lang code from browser or in some cases it will use default language
$_t = new cc_translate($db, "LOGIN", $lang_code); 

if(empty($lang_code)) $lang_code = $_t->getLanguage();

?>