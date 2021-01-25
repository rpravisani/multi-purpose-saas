<?php
/* REDIRECT A LOGIN */
session_start();
include 'required/variables.php';
header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'cpanel.php');
?>
