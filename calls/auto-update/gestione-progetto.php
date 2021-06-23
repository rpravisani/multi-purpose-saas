<?php

// stabilisco tabella da aggiornare ed eventuale sanificazione / formattazione / modifca di valore (p.e. da data d/m/Y a Y-m-d)
switch($prefix){
    case 'm':
        $table = "data_meetings";
        break;
    case 'p':
    default:
        $table = "data_progetti";
        break;
}



