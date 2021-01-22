<?php
$file = var_export($_FILES, true);
$post = var_export($_POST, true);
$dbg = date("Y-m-d H:i:s", time());
$dbg .= "\n\n";
$dbg .= "File ".$file."\n\n";
$dbg .= "Post ".$post."\n\n";

$handle = fopen("test.txt", "a");
fwrite($handle, $dbg);
fclose($handle);

?>