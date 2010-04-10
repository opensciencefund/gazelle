<?
gc_enable();
ini_set('memory_limit',2048*1024*1024);
ini_set('max_execution_time', 3600);

define('URL', '/torrents.php?id=726330');
define('URL2', '/torrents.php?id=726343');
define('URL3', '/torrents.php?id=726332');

$Length = strlen(URL);
?>
<html><head><TITLE></TITLE><body>
<?

function handle($ID, $Username, $URL) {
	global $Cache, $Length;
	$AccessLog = $Cache->get_value('access_log_'.$ID);
	$Count = count($AccessLog);
	for($i = 0; $i<$Count; $i++) {
		if(substr($AccessLog[$i]['url'], 0, $Length) == $URL) {
		//if(strpos($Acc
			/*?>
			<a href="user.php?id=<?=$ID?>"><?=$Username?></a>
			<?*/
			return true;
		} else {
			return false;
		}
	}
	$AccessLog = null;
}

$DB->query("SELECT ID, Username FROM users_main WHERE LastAccess>NOW()-INTERVAL 24 HOUR LIMIT $_GET[start], $_GET[limit]");

while(list($ID, $Username) = $DB->next_record()) {
	if(handle($ID, $Username, URL) && handle($ID, $Username, URL2) && handle($ID, $Username, URL3)) {
		?><a href="user.php?id=<?=$ID?>"><?=$Username?></a><?
	}
}

echo 'Done';

?>
</body></head></html>
