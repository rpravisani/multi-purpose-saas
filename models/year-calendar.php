<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         ***
 *** filename: elenco-militi.php                   ***
 *** elenco dei militi                             ***
 ***                                               ***
 *****************************************************/

$js_assets[] = "plugins/year-calendar/bootstrap-year-calendar.min.js";
$js_assets[] = "plugins/year-calendar/bootstrap-year-calendar.it.js";
$css_assets[] = "plugins/year-calendar/bootstrap-year-calendar.min.css";

$selected_milite = ""; // per ora poi magari inserisco valore preso da get

$militi_options = $helper->getAllMiliti(true, $selected_milite);

?>