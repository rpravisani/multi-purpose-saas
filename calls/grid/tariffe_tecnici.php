<?php
/*** CALLS/GRID - TARIFFE TECNICI ***/

$cell_tab = TABELLA_TARIFFE;
$campi = array(
	"costo_km" => "Costo al Km.",
	"costo_ore_viaggio" => "Costo ore viaggio",
	"costo_ore_lavoro" => "Costo ora lavoro",
	"costo_chiamata" => "Costo chiamata",
	"velocita" => "Velocità"
);


$col_rif = "tecnico"; // nome colonna della tabella celle($cell_tab) a cui fare riferimento
$row_rif = "richiedente"; // nome colonna della tabella celle($cell_tab) a cui fare riferimento





$titolo_pagina = "Tariffe Tecnici";
$description = "";
$title = "";


$col_tab  = TABELLA_TECNICI;
$col_name = "nome";
$col_condition = "";
$col_rif = "tecnico"; // nome colonna della tabella celle($cell_tab) a cui fare riferimento

$row_tab  = TABELLA_RICHIEDENTI;
$row_name = "rag_soc";
$row_condition = "";
$row_rif = "richiedente"; // nome colonna della tabella celle($cell_tab) a cui fare riferimento

$cell_tab = TABELLA_TARIFFE;
$cell_id = "id";
$cell_val = "costo_km";

$legenda = ""; // Didascalia per la legenda collocata nella cella A0

$tmpl_colonna = "%s"; // template per intestazione colonne (verrà utilizzato sprintf, quindi deve avere %s)
$tmpl_riga = "%s";// template per intestazione riga (verrà utilizzato sprintf, quindi deve avere %s)
$tmpl_cell = "&euro;&nbsp;%s"; // template per cella (verrà utilizzato sprintf, quindi deve avere %s)


		

//extra menu button
/*
$_peso['nome'] = "Mod. / Agg.<br>Pesi";
$_peso['url'] = "modPesi";
$_peso['title'] = "Crea un nuovo peso ";
$_peso['class'] = "";
$_peso['icon'] = "peso.png";
$_peso['mode'] = "ajax";

$_fascia['nome'] = "Mod. / Agg.<br>Zona";
$_fascia['url'] = "scheda.php?mod=6";
$_fascia['title'] = "Crea una nuova zona ";
$_fascia['class'] = "";
$_fascia['icon'] = "zone.png";
$_fascia['mode'] = "href";

$extra_menu_btn[2] = $_peso;
$extra_menu_btn[1] = $_fascia;

$save_btn = "salvaGrid";
$save_ajax = true;
*/
?>