<?php

include_once '_head.php';
$posts['categoria'] = 1;

$result = callFile("calls/get_dati_categoria.php", $posts);

echo "<pre>";
print_r(json_decode($result));
echo "</pre>";


?>