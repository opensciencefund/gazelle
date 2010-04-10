<?

function notify ($Channel, $Message) {
	global $LoggedUser;
	send_irc("PRIVMSG ".$Channel." :".$Message." error by ".(!empty($LoggedUser['ID']) ? "http://".NONSSL_SITE_URL."/user.php?id=".$LoggedUser['ID'] ." (".$LoggedUser['Username'].")" : $_SERVER['REMOTE_ADDR']." (".geoip($_SERVER['REMOTE_ADDR']).")")." accessing http://".NONSSL_SITE_URL."".$_SERVER['REQUEST_URI'].(!empty($_SERVER['HTTP_REFERER'])? " from ".$_SERVER['HTTP_REFERER'] : ''));
}

$Errors = array('403','404','413','504');

if(!empty($_GET['e']) && in_array($_GET['e'],$Errors)) {
	include($_GET['e'].'.php');
}

if(!empty($Error)) {
	switch ($Error) {
		case '403':
			$Title = "Error 403";
			$Description = "You just tried to go to a page that you don't have enough permission to view.";
			notify(STATUS_CHAN,'403');
			break;
		case '404':
			$Title = "Error 404";
			$Description = "You just tried to go to a page that doesn't really exist.";
			break;
		case '0':
			$Title = "Invalid Input";
			$Description = "Something was wrong with the input provided with your request and the server is refusing to fulfill it.";
			notify(STATUS_CHAN,'PHP-0');
			break;
		case '-1':
			$Title = "Unexpected Error";
			$Description = "You have encountered an unexpected error.";
			break;
		default:
			$Title = 'Error';
			$Description = $Error;
	}

	if(empty($Ajax)) {
		show_header($Title);
?>
	<div class="thin">
		<h2><?=$Title?></h2>
		<div class="box pad">
			<p><?=$Description?></p>
		</div>
	</div>
<?
		show_footer();
	} else {
		echo $Description;
	}
}
