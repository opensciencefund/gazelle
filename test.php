<?php
	include("classes/class_hook.php");
	$Hook = new HOOK;
	$Hook->scan_plugins();
	$Hook->raise("error.404");
?>
