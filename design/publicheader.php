<?
global $LoggedUser;
define('FOOTER_FILE',SERVER_ROOT.'/design/publicfooter.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?=display_str($PageTitle)?></title>
	<meta http-equiv="X-UA-Compatible" content="chrome=1;IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="favicon.ico" />
	<link href="<?=STATIC_SERVER ?>styles/public/style.css?rev=4" rel="stylesheet" type="text/css" />
	<script src="<?=STATIC_SERVER?>functions/sizzle.js" type="text/javascript"></script>
	<script src="<?=STATIC_SERVER?>functions/script_start.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/script_start.js')?>" type="text/javascript"></script>
	<script src="<?=STATIC_SERVER?>functions/class_ajax.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/class_ajax.js')?>" type="text/javascript" async="async"></script>
	<script src="<?=STATIC_SERVER?>functions/class_cookie.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/class_cookie.js')?>" type="text/javascript" async="async"></script>
	<script src="<?=STATIC_SERVER?>functions/class_storage.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/class_storage.js')?>" type="text/javascript"></script>
	<script src="<?=STATIC_SERVER?>functions/global.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/global.js')?>" type="text/javascript"></script>
</head>
<body>
<div id="head">
<?=($_SERVER['SERVER_PORT'] == 443)?'<span>SSL</span>':''?>
</div>
<table id="maincontent">
	<tr>
		<td align="center" valign="middle">
			<div id="logo">
				<a href="index.php">Home</a>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="login.php">Login</a>
<? if (OPEN_REGISTRATION) { ?> 
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="register.php">Register</a>
<? } ?> 
			</div>
