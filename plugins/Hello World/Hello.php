<?php
	class Hello {
		static function Test($arguments = array()) {
			die("Hello World!");
		}
	}
	
	$Hook->register("error.404", Hello::Test);
?>