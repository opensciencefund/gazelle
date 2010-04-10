<?
$UserStats = $Cache->get_value('user_stats_'.$UserID);
if(!is_array($UserStats)) {
	if (!isset($DB)) {
		require(SERVER_ROOT.'/classes/class_mysql.php');
		$DB = new DB_MYSQL;
	}
	$DB->query("SELECT Uploaded AS BytesUploaded, Downloaded AS BytesDownloaded, RequiredRatio FROM users_main WHERE ID='$UserID'");
	$UserStats = $DB->next_record(MYSQLI_ASSOC);
	$Cache->cache_value('user_stats_'.$LoggedUser['ID'], $UserStats, 3600);
}
$Up = $UserStats['BytesUploaded'];
$Down = $UserStats['BytesDownloaded'];
$ReqRat = $UserStats['RequiredRatio'];
if ($Down > 0) {
	$Rat = $Up/$Down;
} else {
	$Rat = 0;
}
?>
<uploaded><?=$Up?></uploaded>
<downloaded><?=$Down?></downloaded>
<ratio><?=$Rat?></ratio>
<buffer><?=$Up-$Down?></buffer>
<disposable><?=$Up-($Down*$ReqRat)?></disposable>
<reqratio><?=$ReqRat?></reqratio>
