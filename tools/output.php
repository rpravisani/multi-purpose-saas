<?php

include 'barcode.php';

$code = trim($_GET['code']);
$size = (int) $_GET['size'];

if(empty($size)) $size = 2;
$barcode = new Barcode($code, $size);

$barcode->display();

?>