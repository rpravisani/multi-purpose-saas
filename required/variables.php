<?php
/*******************************************************
*  Definizioni di variabili usate in tutto il progetto *
********************************************************/

/*** Site Variables ***/

define ('NOME_DITTA', "Task Reporter");
define ('NOME_DITTA_LOGO_L', "<b>Task</b> Reporter");
define ('NOME_DITTA_LOGO_S', "<b>T</b>R");

include 'sitevariables.php';

define ('DEFAULT_TIMEZONE', 'Europe/Rome');
date_default_timezone_set(DEFAULT_TIMEZONE);
//setlocale(LC_ALL, 'it_IT'); // per ora disabilitato, se poi serve lo attivo :: attenzione a scheda interevento calcolo prezzi.

/*** Login handling ***/
define ('RESTRICTED_ACCESS', true); // se false non viene fatto controllo accesso
define ('LOGIN_FORM', 'login.php');
define ('LOGIN_CONTROL', 'verificautente.php');
define ('PANEL', 'cpanel.php');
define ('LOGOUT_SCRIPT', 'logout.php');
define ('RESET_PASSWORD_URL', 'reset-password.php'); // the url of the reset password page
define ('REGISTER_URL', 'register.php'); // the url of the register new user page

define ('DB_LOGIN', true);
define ('MULTI_LOGIN', false); // per permettere l'utilizzo dello stesso servizio da parte di diversi clienti (vedi referti)
define ('MULTI_LOGIN_FIELD', "lab"); // nome della variabile ($_GET e $_SESSION) e della colonna della tabella login che identifica il gruppo nel multi login
define ('REMEMBER_SESSION_IN_COOKIE', false); // false or seconds of lifespan
define ('REGISTER_SESSIONS', true); // Remember user in session var
define ('REGISTER_TABLE', false); // SE NON SETTATO MEMORIZZA IN VARIABILE SESSION
define ('SESSION_LENGHT', 100); // In Minutes
define ('LOG_ACCESS', true); // if true log every access in access_logs table
define ('FIRST_ACCESS_PAGE', false); // Name of script to launch at first access (eg. confirm email) or false (no check)
define ('MODULE_CHECK', false);  // DA FARE (provviene da condomini) se true interoga tabella moduli_permessi per vedere se l'utente ha accesso ad un modulo o meno, se false non verifica
define ('SA_NAME', 'Creative Chaos'); // name of super admin
define ('SA_EMAIL', 'rpravisani@gmail.com'); // name of super admin

// login options
define ('REMEMBER_ME', 0); // number days to remember. If different than false will prinout the remember me checkbox in login screen
define ('FORGOT_PASSWORD', true); // If true will printout the I lost my password link in login page
define ('FORGOT_PASSWORD_HOUR_LIMIT', 48); //the hours the token for reseting password will be valid
define ('SHOW_REGISTER', false); // If true will printout the Register user link in login page
$social_auth_links = array(); //array("facebook", "google"); // Print-out social link for registratio - for now ammited values are "facebook", "google"


define ('PRE_ACCESS_SCRIPT', false); // Se diverso da false script da lanciare all'avvio / login p.e. define ('SCRIPT_AD_ACCESSO', "scripts/xmlparser.php")
define ('ENCODE', true); // encode password
define ('COOKIE_NS', str_replace(" ", "", NOME_DITTA)); // for timezone and language pref
define ('COOKIE_LIFESPAN', 60*60*24*120); // in seconds - used in cookie for timezone and lang


// only for subscription plans
define ('SUBSCRIPTION_PLANS', true); // if true a subuscription plan is used
define ('DAYS_SUBSCRIPTION_ALERT', 20); // how many days before subscription-end to show alert
define ('DAYS_SUBSCRIPTION_MARGIN', 30); // how many days you're alowed to use the system after subscription ends
define ('RENEWAL_PAGE_ID', 8); // id of the subscription renewal page
define ('DEFAULT_SUBSCRIPTION_PARAMS', 'a:3:{s:12:"num_projects";s:1:"1";s:15:"num_frequencies";s:1:"1";s:11:"extra_tools";b:0;}'); // Serialized array of default params TODO: insert in config table
define ('DEFAULT_USER_PREFS', 'a:1:{s:4:"skin";s:9:"skin-blue";}'); // Serialized array of default params TODO: insert in config table


if (DB_LOGIN){
   define ('LOGIN_TABLE', TABLE_PREFIX.'users');
}else{
   define ('USERNAME', 'sa');
   define ('PASSWORD', 'merlino');
   define ('ADMIN_NAME', 'Superadmin');
}


/*** MULTILANGUAGE ***/
define ('MULTI_LANG', false); // se true usa le traduzioni, se no monolingua. Se true assicurarsi che la tabella lingue sia compilata e che le lingue siano attive
define ('DEFAULT_LANG', 73); // id languages table - in this case italian
define ('DEFAULT_NATION', 109); // id nations table - in this case Italy

/*** ERROR LOGGING ***/
define ('CREATE_ERROR_LOG', false); // TODO se true crea file log con gli errori.
define ('LOG_ERRORS_DB', true);

/*** SEND EMAIL VARIABLES - TODO MOVE TO CONFIG TABLE ***/
define ('EMAIL_ADMIN', "rpravisani@gmail.com"); // used for communications to the admin of the 
define ('NO_REPLY', "noreply@taskreporter.com"); // used in send-email.php as sender email
define ('NO_REPLY_NAME', "Portale Task Reporter"); // used in send-email.php as sender name
define ('DEFAULT_REPLYTO_ADRESS', "rpravisani@gmail.com"); // user as destination email of admin order notifications

// IMAP SETTINGS
define ('MAILSERVER_TYPE', 'IMAP'); // POP3 o IMAP
define ('MAILSERVER_PORT', '993'); // La porta. Da cambiare insiemea a MAILSERVER_TYPE. Normalmente 110 per POP3 e 143 per IMAP
define ('MAILSERVER', 'imap.mail.yahoo.com');
define ('MAIL_USERNAME', 'antealdi@yahoo.it');
define ('MAIL_PASSWORD', 'the3rdeye');

/***  DATATABLE VARIABLES ***/
define ('NUMERO_DEFAULT_RIGHE_TABELLA', 20);
define ('NUMERO_DEFAULT_RIGHE_TABELLA_DASH', 5);

/*** CSV VARIABLES ***/
define ('PRIMA_RIGA_INTESTA', false);

/*** SYSTEM DB TABLES DEFINITION ***/
define ('DBTABLE_CONFIG', TABLE_PREFIX.'config');
define ('DBTABLE_LANGUAGES', TABLE_PREFIX.'languages');
define ('DBTABLE_PAGES', TABLE_PREFIX.'pages');
define ('DBTABLE_PAGE_PERMISSIONS', TABLE_PREFIX.'page_permissions');
define ('DBTABLE_PAYMENT_METHODS', TABLE_PREFIX.'payment_methods');
define ('DBTABLE_REPORTS', TABLE_PREFIX.'reports');
define ('DBTABLE_SUBSCRIPTION_TYPES', TABLE_PREFIX.'subscription_types');
define ('DBTABLE_TABLES_ACCESS', TABLE_PREFIX.'tables_access');
define ('DBTABLE_TIMEZONES', TABLE_PREFIX.'timezones');
define ('DBTABLE_TOKENS', TABLE_PREFIX.'tokens');
define ('DBTABLE_TRANSLATIONS', TABLE_PREFIX.'translations');
define ('DBTABLE_TRANSLATIONS_LOST', TABLE_PREFIX.'translations_lost');
define ('DBTABLE_UPLOADS', TABLE_PREFIX.'uploads');
define ('DBTABLE_VERSIONING', TABLE_PREFIX.'versioning');
define ('DBTABLE_MEDIA', TABLE_PREFIX.'media');
define ('DBTABLE_TICKETS', TABLE_PREFIX.'tickets');
define ('DBTABLE_TICKETS_REPLIES', TABLE_PREFIX.'tickets_followups');

/*** LOGS DB TABLES DEFINITION ***/
define ('DBTABLE_ACCESS_LOGS', TABLE_PREFIX.'logs_access'); // da cambiare in db
define ('DBTABLE_EMAIL_LOGS', TABLE_PREFIX.'logs_email'); // da cambiare in db
define ('DBTABLE_ERROR_LOGS', TABLE_PREFIX.'logs_error');
define ('DBTABLE_SUBSCRIPTION_LOGS', TABLE_PREFIX.'logs_subscription');
define ('DBTABLE_TICKET_LOGS', TABLE_PREFIX.'logs_tickets');

/*** HELP DB TABLES DEFINITION ***/
define ('DBTABLE_NATIONS', TABLE_PREFIX.'help_nations');
define ('DBTABLE_REGIONS', TABLE_PREFIX.'help_regions');

/*** DATA DB TABLES DEFINITION ***/
define ('DBTABLE_FORNITORI', TABLE_PREFIX.'data_fornitori');
define ('DBTABLE_PRODOTTI', TABLE_PREFIX.'data_prodotti');
define ('DBTABLE_PREZZI_GIORNO', TABLE_PREFIX.'data_prezzi_giorno');
define ('DBTABLE_CLIENTI', TABLE_PREFIX.'data_clienti');
define ('DBTABLE_LISTINI', TABLE_PREFIX.'data_listini');
define ('DBTABLE_LISTINI_PREZZI', TABLE_PREFIX.'data_listini_prezzi');
define ('DBTABLE_CATEGORIE', TABLE_PREFIX.'data_categorie');



/*** INSERT AND UPDATE BY FILED DEFINITION ***/
define ('INSERTED_BY_FIELD', 'insertedby');
define ('UPDATED_BY_FIELD', 'updatedby');


/***  TABLES_ACCESS VARIABLES ***/
define ('CSV_FEED_SCRIPT', "csv/csv_feed.php"); 
// Array with the tables that have to be ignored and can't be accessed by excel
$ignore = array("access_logs", "config", "email_logs", "error_logs", "languages", "nations", "pages", "page_permissions", "payment_methods", "regions",
"subscription_logs", "subscription_types", "tables_access", "timezones", "tokens", "translations", "uploads", "users", "versioning");

/***  OTHER VARIABLES ***/
define ('DEFAULT_DATE_FORMAT', "Y-m-d"); 
define ('DEFAULT_CHECKBTN_TH', "Sel."); // TODO : verrà eliminato, verrà usato quello dell lingua...
define ('RECORDSET', false); 
define ('SEARCH_IN_SIDEBAR', false); 
define ('DAYS_TOKEN', 10); // Validità dei token dalla data della prima visita alla pagina
define ('DEFAULT_MAX_KM_ASSE', 50000); // Max km di default dopo la quale verificare asse
define ('MAX_CONTROLLO_MESI', 2); // Max n di mesi tra un controllo e l'altro
define ('FONT_AWESOME_URL', "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"); // Url del CDN
define ('ENCODE_BYTES', 32); // the length of the bytes used to create encoded strings (length of string will be twice this value)

/*** SCREENSHOT AND HELP RELATED ***/
define ('SCREENSHOT_EXT', 'png'); 
define ('SCREENSHOT_PAGENAME', 'Ticket_Catalogo_Quota47'); // TODO param 
define ('SCREENSHOT_PATH', '../screenshots/'); 
define ('SCREENSHOT_EMAIL_TEXT', "Segnalazione di errore o problema da ".NOME_DITTA.".<br><br>"); 
define ('SCREENSHOT_EMAIL_SUBJECT', "Segnalazione Errore da ".NOME_DITTA." del %s"); 
define ('SUPERVISOR_EMAIL', "info@quota47.com"); // Email to send to when user one is empty 

/*** DIVERSE ***/
// mesi
$mese_anno[1] = "gennaio";
$mese_anno[2] = "febbraio";
$mese_anno[3] = "marzo";
$mese_anno[4] = "aprile";
$mese_anno[5] = "maggio";
$mese_anno[6] = "giugno";
$mese_anno[7] = "luglio";
$mese_anno[8] = "agosto";
$mese_anno[9] = "settembre";
$mese_anno[10] = "ottobre";
$mese_anno[11] = "novembre";
$mese_anno[12] = "dicembre";

// giorni
$giorno_settimana[0] = "domenica";
$giorno_settimana[1] = "luned&igrave;";
$giorno_settimana[2] = "marted&igrave;";
$giorno_settimana[3] = "mercoled&igrave;";
$giorno_settimana[4] = "gioved&igrave;";
$giorno_settimana[5] = "venerd&igrave;";
$giorno_settimana[6] = "sabato";

$gg_settimana[0] = "dom";
$gg_settimana[1] = "lun";
$gg_settimana[2] = "mar";
$gg_settimana[3] = "mer";
$gg_settimana[4] = "gio";
$gg_settimana[5] = "ven";
$gg_settimana[6] = "sab";

$lettere = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
$parti_giorno[0] = "Buona Nottata";
$parti_giorno[1] = "Buongiorno";
$parti_giorno[2] = "Buon Pomeriggio";
$parti_giorno[3] = "Buonasera";

$lancode ['it'] = "ita";
$lancode ['en'] = "eng";
$lancode ['de'] = "ger";
$lancode ['fr'] = "fra";

// Nazioni in lingua italiana
$nazioni = array("it" => "Italia", "af" => "Afghanistan", "ax" => "Isole &Aring;land", "al" => "Albania", "dz" => "Algeria", "as" => "Samoa Americane", "ad" => "Andorra", "ao" => "Angola", "ai" => "Anguilla", "aq" => "Antartide", "ag" => "Antigua e Barbuda", "ar" => "Argentina", "am" => "Armenia", "aw" => "Aruba", "au" => "Australia", "at" => "Austria", "az" => "Azerbaigian", "bs" => "Bahamas", "bh" => "Bahrein", "bd" => "Bangladesh", "bb" => "Barbados", "by" => "Bielorussia", "be" => "Belgio", "bz" => "Belize", "bj" => "Benin", "bm" => "Bermuda", "bt" => "Bhutan", "bo" => "Bolivia", "ba" => "Bosnia Erzegovina", "bw" => "Botswana", "bv" => "Isola Bouvet", "br" => "Brasile", "io" => "Territorio Britannico dell'Oceano Indiano (BIOT)", "vg" => "Isole Vergini Britanniche", "bn" => "Brunei", "bg" => "Bulgaria", "bf" => "Burkina Faso", "bi" => "Burundi", "kh" => "Cambogia", "cm" => "Camerun", "ca" => "Canada", "cv" => "Capo Verde", "ky" => "Isole Cayman", "cf" => "Repubblica Centrafricana", "td" => "Ciad", "cl" => "Cile", "cn" => "Cina", "cx" => "Isola di Natale", "cc" => "Isole Cocos (Keeling)", "co" => "Colombia", "km" => "Comore", "cg" => "Congo", "ck" => "Isole Cook", "cr" => "Costa Rica", "hr" => "Croazia", "cy" => "Cipro", "cz" => "Repubblica Ceca", "cd" => "Repubblica Democratica del Congo", "dk" => "Danimarca", "xx" => "Territorio conteso", "dj" => "Gibuti", "dm" => "Dominica", "do" => "Repubblica Dominicana", "tl" => "Timor Est", "ec" => "Ecuador", "eg" => "Egitto", "sv" => "El Salvador", "gq" => "Guinea Equatoriale", "er" => "Eritrea", "ee" => "Estonia", "et" => "Etiopia", "fk" => "Isole Falkland", "fo" => "Isole Faroe", "fm" => "Micronesia, Stati Federati della", "fj" => "Isole Figi", "fi" => "Finlandia", "fr" => "Francia", "gf" => "Guiana Francese", "pf" => "Polinesia Francese", "tf" => "Territori Australi Francesi", "ga" => "Gabon", "gm" => "Gambia", "ge" => "Georgia", "de" => "Germania", "gh" => "Ghana", "gi" => "Gibilterra", "gr" => "Grecia", "gl" => "Groenlandia", "gd" => "Grenada", "gp" => "Guadalupa", "gu" => "Guam", "gt" => "Guatemala", "gn" => "Guinea", "gw" => "Guinea-Bissau", "gy" => "Guyana", "ht" => "Haiti", "hm" => "Isole Heard e McDonald", "hn" => "Honduras", "hk" => "Hong Kong", "hu" => "Ungheria", "is" => "Islanda", "in" => "India", "id" => "Indonesia", "iq" => "Iraq", "xe" => "Zona neutra Iraq-Arabia Saudita", "ie" => "Irlanda", "il" => "Israele", "it" => "Italia", "ci" => "Costa d'Avorio", "jm" => "Giamaica", "jp" => "Giappone", "jo" => "Giordania", "kz" => "Kazakistan", "ke" => "Kenya", "ki" => "Kiribati", "kw" => "Kuwait", "kg" => "Kirghizistan", "la" => "Laos", "lv" => "Lettonia", "lb" => "Libano", "ls" => "Lesotho", "lr" => "Liberia", "ly" => "Libia", "li" => "Liechtenstein", "lt" => "Lituania", "lu" => "Lussemburgo", "mo" => "Macau", "mk" => "Macedonia", "mg" => "Madagascar", "mw" => "Malawi", "my" => "Malesia", "mv" => "Maldive", "ml" => "Mali", "mt" => "Malta", "mh" => "Isole Marshall", "mq" => "Martinica", "mr" => "Mauritania", "mu" => "Mauritius", "yt" => "Mayotte", "mx" => "Messico", "md" => "Moldova", "mc" => "Monaco", "mn" => "Mongolia", "ms" => "Montserrat", "ma" => "Marocco", "mz" => "Mozambico", "mm" => "Myanmar (Birmania)", "na" => "Namibia", "nr" => "Nauru", "np" => "Nepal", "nl" => "Paesi Bassi", "an" => "Antille Olandesi", "nc" => "Nuova Caledonia", "nz" => "Nuova Zelanda", "ni" => "Nicaragua", "ne" => "Niger", "ng" => "Nigeria", "nu" => "Niue", "nf" => "Isola Norfolk", "kp" => "Corea del Nord", "mp" => "Isole Marianne Settentrionali", "no" => "Norvegia", "om" => "Oman", "pk" => "Pakistan", "pw" => "Palau", "ps" => "Territori Occupati della Palestina", "pa" => "Panama", "pg" => "Papua Nuova Guinea", "py" => "Paraguay", "pe" => "Peru", "ph" => "Filippine", "pn" => "Isola Pitcairn", "pl" => "Polonia", "pt" => "Portogallo", "pr" => "Puerto Rico", "qa" => "Qatar", "re" => "Reunion", "ro" => "Romania", "ru" => "Russia", "rw" => "Ruanda", "sh" => "Sant'Elena", "kn" => "St. Kitts e Nevis", "lc" => "St. Lucia", "pm" => "St. Pierre e Miquelon", "vc" => "St. Vincent e Grenadine", "ws" => "Samoa", "sm" => "San Marino", "st" => "Sao Tome e Principe", "sa" => "Arabia Saudita", "sn" => "Senegal", "cs" => "Serbia e Montenegro", "sc" => "Seychelles", "sl" => "Sierra Leone", "sg" => "Singapore", "sk" => "Slovacchia", "si" => "Slovenia", "sb" => "Isole Solomon", "so" => "Somalia", "za" => "Sud Africa", "gs" => "Isole Georgia del Sud e Sandwich meridionali", "kr" => "Corea del Sud", "es" => "Spagna", "pi" => "Isole Spratly", "lk" => "Sri Lanka", "sr" => "Suriname", "sj" => "Isole Svalbard e Jan Mayen", "sz" => "Swaziland", "se" => "Svezia", "ch" => "Svizzera", "sy" => "Siria", "tw" => "Taiwan", "tj" => "Tagikistan", "tz" => "Tanzania", "th" => "Thailandia", "tg" => "Togo", "tk" => "Isole Tokelau", "to" => "Tonga", "tt" => "Trinidad e Tobago", "tn" => "Tunisia", "tr" => "Turchia", "tm" => "Turkmenistan", "tc" => "Isole Turks e Caicos", "tv" => "Tuvalu", "ug" => "Uganda", "ua" => "Ucraina", "ae" => "Emirati Arabi Uniti", "uk" => "Regno Unito", "xd" => "Zona neutra ONU", "us" => "Stati Uniti", "um" => "Isole minori degli Stati Uniti", "uy" => "Uruguay", "vi" => "Isole Vergini Statunitensi", "uz" => "Uzbekistan", "vu" => "Vanuatu", "va" => "Citta&agrave; del Vaticano", "ve" => "Venezuela", "vn" => "Vietnam", "wf" => "Isole Wallis e Futuna", "eh" => "Sahara Occidentale", "ye" => "Yemen", "zm" => "Zambia", "zw" => "Zimbabwe");

// Nazioni in lingua inglese
$countries = array("us"=> "United States", "uk"=> "United Kingdom", "af"=> "Afghanistan", "ax"=> "Aland Islands", "al"=> "Albania", "dz"=> "Algeria", "as"=> "American Samoa", "ad"=> "Andorra", "ao"=> "Angola", "ai"=> "Anguilla", "aq"=> "Antarctica", "ag"=> "Antigua and Barbuda", "ar"=> "Argentina", "am"=> "Armenia", "aw"=> "Aruba", "au"=> "Australia", "at"=> "Austria", "az"=> "Azerbaijan", "bs"=> "Bahamas", "bh"=> "Bahrain", "bd"=> "Bangladesh", "bb"=> "Barbados", "by"=> "Belarus", "be"=> "Belgium", "bz"=> "Belize", "bj"=> "Benin", "bm"=> "Bermuda", "bt"=> "Bhutan", "bo"=> "Bolivia", "ba"=> "Bosnia and Herzegovina", "bw"=> "Botswana", "bv"=> "Bouvet Island", "br"=> "Brazil", "io"=> "British Indian Ocean Territory", "vg"=> "British Virgin Islands", "bn"=> "Brunei", "bg"=> "Bulgaria", "bf"=> "Burkina Faso", "bi"=> "Burundi", "kh"=> "Cambodia", "cm"=> "Cameroon", "ca"=> "Canada", "cv"=> "Cape Verde", "ky"=> "Cayman Islands", "cf"=> "Central African Republic", "td"=> "Chad", "cl"=> "Chile", "cn"=> "China", "cx"=> "Christmas Island", "cc"=> "Cocos (Keeling) Islands", "co"=> "Colombia", "km"=> "Comoros", "cg"=> "Congo", "ck"=> "Cook Islands", "cr"=> "Costa Rica", "hr"=> "Croatia", "cy"=> "Cyprus", "cz"=> "Czech Republic", "cd"=> "Democratic Republic of Congo", "dk"=> "Denmark", "xx"=> "Disputed Territory", "dj"=> "Djibouti", "dm"=> "Dominica", "do"=> "Dominican Republic", "tl"=> "East Timor", "ec"=> "Ecuador", "eg"=> "Egypt", "sv"=> "El Salvador", "gq"=> "Equatorial Guinea", "er"=> "Eritrea", "ee"=> "Estonia", "et"=> "Ethiopia", "fk"=> "Falkland Islands", "fo"=> "Faroe Islands", "fm"=> "Federated States of Micronesia", "fj"=> "Fiji", "fi"=> "Finland", "fr"=> "France", "gf"=> "French Guyana", "pf"=> "French Polynesia", "tf"=> "French Southern Territories", "ga"=> "Gabon", "gm"=> "Gambia", "ge"=> "Georgia", "de"=> "Germany", "gh"=> "Ghana", "gi"=> "Gibraltar", "gr"=> "Greece", "gl"=> "Greenland", "gd"=> "Grenada", "gp"=> "Guadeloupe", "gu"=> "Guam", "gt"=> "Guatemala", "gn"=> "Guinea", "gw"=> "Guinea-Bissau", "gy"=> "Guyana", "ht"=> "Haiti", "hm"=> "Heard Island and Mcdonald Islands", "hn"=> "Honduras", "hk"=> "Hong Kong", "hu"=> "Hungary", "is"=> "Iceland", "in"=> "India", "id"=> "Indonesia", "iq"=> "Iraq", "xe"=> "Iraq-Saudi Arabia Neutral Zone", "ie"=> "Ireland", "il"=> "Israel", "it" => "Italy", "ci"=> "Ivory Coast", "jm"=> "Jamaica", "jp"=> "Japan", "jo"=> "Jordan", "kz"=> "Kazakhstan", "ke"=> "Kenya", "ki"=> "Kiribati", "kw"=> "Kuwait", "kg"=> "Kyrgyzstan", "la"=> "Laos", "lv"=> "Latvia", "lb"=> "Lebanon", "ls"=> "Lesotho", "lr"=> "Liberia", "ly"=> "Libya", "li"=> "Liechtenstein", "lt"=> "Lithuania", "lu"=> "Luxembourg", "mo"=> "Macau", "mk"=> "Macedonia", "mg"=> "Madagascar", "mw"=> "Malawi", "my"=> "Malaysia", "mv"=> "Maldives", "ml"=> "Mali", "mt"=> "Malta", "mh"=> "Marshall Islands", "mq"=> "Martinique", "mr"=> "Mauritania", "mu"=> "Mauritius", "yt"=> "Mayotte", "mx"=> "Mexico", "md"=> "Moldova", "mc"=> "Monaco", "mn"=> "Mongolia", "ms"=> "Montserrat", "ma"=> "Morocco", "mz"=> "Mozambique", "mm"=> "Myanmar", "na"=> "Namibia", "nr"=> "Nauru", "np"=> "Nepal", "nl"=> "Netherlands", "an"=> "Netherlands Antilles", "nc"=> "New Caledonia", "nz"=> "New Zealand", "ni"=> "Nicaragua", "ne"=> "Niger", "ng"=> "Nigeria", "nu"=> "Niue", "nf"=> "Norfolk Island", "kp"=> "North Korea", "mp"=> "Northern Mariana Islands", "no"=> "Norway", "om"=> "Oman", "pk"=> "Pakistan", "pw"=> "Palau", "ps"=> "Palestinian Occupied Territories", "pa"=> "Panama", "pg"=> "Papua New Guinea", "py"=> "Paraguay", "pe"=> "Peru", "ph"=> "Philippines", "pn"=> "Pitcairn Islands", "pl"=> "Poland", "pt"=> "Portugal", "pr"=> "Puerto Rico", "qa"=> "Qatar", "re"=> "Reunion", "ro"=> "Romania", "ru"=> "Russia", "rw"=> "Rwanda", "sh"=> "Saint Helena and Dependencies", "kn"=> "Saint Kitts and Nevis", "lc"=> "Saint Lucia", "pm"=> "Saint Pierre and Miquelon", "vc"=> "Saint Vincent and the Grenadines", "ws"=> "Samoa", "sm"=> "San Marino", "st"=> "Sao Tome and Principe", "sa"=> "Saudi Arabia", "sn"=> "Senegal", "cs"=> "Serbia and Montenegro", "sc"=> "Seychelles", "sl"=> "Sierra Leone", "sg"=> "Singapore", "sk"=> "Slovakia", "si"=> "Slovenia", "sb"=> "Solomon Islands", "so"=> "Somalia", "za"=> "South Africa", "gs"=> "South Georgia and South Sandwich Islands", "kr"=> "South Korea", "es"=> "Spain", "pi"=> "Spratly Islands", "lk"=> "Sri Lanka", "sr"=> "Suriname", "sj"=> "Svalbard and Jan Mayen", "sz"=> "Swaziland", "se"=> "Sweden", "ch"=> "Switzerland", "sy"=> "Syria", "tw"=> "Taiwan", "tj"=> "Tajikistan", "tz"=> "Tanzania", "th"=> "Thailand", "tg"=> "Togo", "tk"=> "Tokelau", "to"=> "Tonga", "tt"=> "Trinidad and Tobago", "tn"=> "Tunisia", "tr"=> "Turkey", "tm"=> "Turkmenistan", "tc"=> "Turks And Caicos Islands", "tv"=> "Tuvalu", "ug"=> "Uganda", "ua"=> "Ukraine", "ae"=> "United Arab Emirates", "uk"=> "United Kingdom", "xd"=> "United Nations Neutral Zone", "us"=> "United States", "um"=> "United States Minor Outlying Islands", "uy"=> "Uruguay", "vi"=> "US Virgin Islands", "uz"=> "Uzbekistan", "vu"=> "Vanuatu", "va"=> "Vatican City", "ve"=> "Venezuela", "vn"=> "Vietnam", "wf"=> "Wallis and Futuna", "eh"=> "Western Sahara", "ye"=> "Yemen", "zm"=> "Zambia", "zw"=> "Zimbabwe");

// Stati USA
$usa_states = array('AL'=>"Alabama",  
			'AK'=>"Alaska",  
			'AZ'=>"Arizona",  
			'AR'=>"Arkansas",  
			'CA'=>"California",  
			'CO'=>"Colorado",  
			'CT'=>"Connecticut",  
			'DE'=>"Delaware",  
			'DC'=>"District Of Columbia",  
			'FL'=>"Florida",  
			'GA'=>"Georgia",  
			'HI'=>"Hawaii",  
			'ID'=>"Idaho",  
			'IL'=>"Illinois",  
			'IN'=>"Indiana",  
			'IA'=>"Iowa",  
			'KS'=>"Kansas",  
			'KY'=>"Kentucky",  
			'LA'=>"Louisiana",  
			'ME'=>"Maine",  
			'MD'=>"Maryland",  
			'MA'=>"Massachusetts",  
			'MI'=>"Michigan",  
			'MN'=>"Minnesota",  
			'MS'=>"Mississippi",  
			'MO'=>"Missouri",  
			'MT'=>"Montana",
			'NE'=>"Nebraska",
			'NV'=>"Nevada",
			'NH'=>"New Hampshire",
			'NJ'=>"New Jersey",
			'NM'=>"New Mexico",
			'NY'=>"New York",
			'NC'=>"North Carolina",
			'ND'=>"North Dakota",
			'OH'=>"Ohio",  
			'OK'=>"Oklahoma",  
			'OR'=>"Oregon",  
			'PA'=>"Pennsylvania",  
			'RI'=>"Rhode Island",  
			'SC'=>"South Carolina",  
			'SD'=>"South Dakota",
			'TN'=>"Tennessee",  
			'TX'=>"Texas",  
			'UT'=>"Utah",  
			'VT'=>"Vermont",  
			'VA'=>"Virginia",  
			'WA'=>"Washington",  
			'WV'=>"West Virginia",  
			'WI'=>"Wisconsin",  
			'WY'=>"Wyoming");


define ('UPLOAD_DIR', FILEROOT."uploads/"); // dir principale dove salvare i file caricati

		
/* --- INIZIO PARAMETRI UPLOADFOTO.PHP / SWF UPLOAD  OLD!!! ---*/
define ('MAX_FILES_UPLOAD', 0); // Numero massimo di file uploadabili per default - 0 =  illimitati (per swfupload)
define ('MAX_FILESIZE_UPLOAD', 8); // massima dimensione del file che si possono uploadare - in MB (per swfupload)
$upload_files_permitted = array("jpg", "jpeg", "pdf", "xls", "doc", "zip", "rar"); // le estensioni permesse (per swfupload)

// nuovo parametro (unica dimensione)
$upload_dir["onesize"] = FILEROOT.PATH_PHOTO;
$fill_color["onesize"] = false;
$upscale["onesize"] = false;
$t_width["onesize"] = 650;
$t_height["onesize"] = 0;

$upload_dir["medium"] = false; // se false crea img, ma non salva (per funzionalità backend)
$fill_color["medium"] = 0x00FFFFFF; // valore HEX
$upscale["medium"] = false;
$t_width["medium"] = 650;
$t_height["medium"] = 0;


$t_width["tiny"]  = 100; // utilizzato per miniature che appaiono quando si fa upload
$t_height["tiny"] = 0; // utilizzato per miniature che appaiono quando si fa upload

define ('FOTO_DB_REF', "immobile"); // Il campo di riferimento in tabella foto che lega foto a elemento (p.e. immobile, prodotto, news etc)
define ('INSERT_FOTO_DB', true); // se true memorizzo le foto in DB... mah!
define ('ORDINE_FOTO', false); // vecchio metodo
define ('CATEGORIE_FOTO', false); // se true devo avere campo "categoria" in tabella foto


/* --- FINE PARAMETRI UPLOADFOTO.PHP  ---*/


/* --- INIZIO PARAMETRI PAYPAL  ---*/
define ('NOME_SHOP', 'DB Central');
define ('CURRENCY', 'EUR');

define ('PAYPAL_USE_SANDBOX', true);

define ('PAYPAL_USE_CURL', true);
define ('ORDER_NUM_SHIFT', 0);

if(PAYPAL_USE_SANDBOX){
	define ('PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr'); // sandbox
	define ('SELLER_EMAIL', 'rpravi_1338364790_biz@gmail.com'); // email account paypal fittizio (sandbox)
	define ('SELLER_EMAIL_TO', 'rpravisani@gmail.com, roberto@creativechaos.it'); // email a cui inviare notifica
	define ('CONTACT_EMAIL', 'rpravisani@gmail.com'); // indirizzo reale usato come mittente per email verso cliente
	define ('NOREPLY_EMAIL', 'ordini@labcare.it');  // indirizzo fitizio usato come mittente per email verso venditore
}else{
	define ('PAYPAL_URL', 'https://www.paypal.com/cgi-bin/webscr'); // the real stuff!	
	define ('SELLER_EMAIL', 'gm.remonato@labcare.it');
	define ('SELLER_EMAIL_TO','gm.remonato@labcare.it'); // email a cui inviare notifica
	define ('CONTACT_EMAIL', 'info@labcare.it'); // indirizzo reale usateo come mittente per email verso cliente
	define ('NOREPLY_EMAIL', 'ordini@labcare.it'); // indirizzo fitizio usato come mittente per email verso venditore
}

define ('IPN_SCRIPT', 'includes/ipn.php');
define ('RETURN_SCRIPT', 'grazie.php');
define ('INVOICE_SCRIPT', 'fattura.php');

define ('IPN_URL', HTTP_PROTOCOL.HOSTROOT.SITEROOT.IPN_SCRIPT);
define ('RETURN_URL', HTTP_PROTOCOL.HOSTROOT.SITEROOT.RETURN_SCRIPT);
define ('INVOICE_URL', HTTP_PROTOCOL.HOSTROOT.SITEROOT.INVOICE_SCRIPT);
/* --- FINE PARAMETRI PAYPAL  ---*/


?>
