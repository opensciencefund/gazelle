<?
if (!isset($_REQUEST['authkey']) || !isset($_REQUEST['torrent_pass'])) {
	enforce_login();
	$TorrentPass = $LoggedUser['torrent_pass'];
	$DownloadAlt = $LoggedUser['DownloadAlt'];
} else {
	$UserInfo = $Cache->get_value('user_'.$_REQUEST['torrent_pass']);
	if(!is_array($UserInfo)) {
		$DB->query("SELECT 
			ID,
			DownloadAlt
			FROM users_main AS m 
			INNER JOIN users_info AS i ON i.UserID=m.ID 
			WHERE m.torrent_pass='".db_string($_REQUEST['torrent_pass'])."' 
			AND m.Enabled='1'");
		$UserInfo = $DB->next_record();
		$Cache->cache_value('user_'.$_REQUEST['torrent_pass'], $UserInfo, 0);
	}
	$UserInfo = array($UserInfo);
	list($UserID,$DownloadAlt)=array_shift($UserInfo);
	if(!$UserID) { error(403); }
	$TorrentPass = $_REQUEST['torrent_pass'];
}
require(SERVER_ROOT.'/classes/class_torrent.php');

$TorrentID = $_REQUEST['id'];

if (!is_number($TorrentID)){ error(0); }

$Info = $Cache->get_value('torrent_download_'.$TorrentID);
if(!is_array($Info)) {
	$DB->query("SELECT
		t.Media,
		t.Format,
		t.Encoding,
		IF(t.RemasterYear=0,tg.Year,t.RemasterYear),
		tg.ID,
		tg.Name,
		tg.WikiImage,
		tg.CategoryID
		FROM torrents AS t
		INNER JOIN torrents_group AS tg ON tg.ID=t.GroupID
		WHERE t.ID='".db_string($TorrentID)."'");
	if($DB->record_count() < 1) {
		header('Location: log.php?search='.$TorrentID);
		die();
	}
	$Info = array($DB->next_record(MYSQLI_NUM, array(4,5,6)));
	$Info['Artists'] = display_artists(get_artist($Info[0][4]), false, true);
	$Cache->cache_value('torrent_download_'.$TorrentID, $Info, 0);
}
if(!is_array($Info[0])) {
	error(404);
}
list($Media,$Format,$Encoding,$Year,$GroupID,$Name,$Image, $CategoryID) = array_shift($Info); // used for generating the filename
$Artists = $Info['Artists'];

//Recent Snatches On User Page
if($CategoryID == '1' && $Image != "") {
	$RecentSnatches = $Cache->get_value('recent_snatches_'.$UserID);
	array_pop($RecentSnatches);
	array_unshift($RecentSnatches, array('ID'=>$GroupID,'Name'=>$Name,'Artist'=>$Artists,'WikiImage'=>$Image));
	$Cache->cache_value('recent_snatches_'.$UserID, $RecentSnatches, 0);
}

// Fucking btjunkie piece of shit
if(strpos($_SERVER['HTTP_REFERER'], 'btjunkie.org')) {
	$DB->query("UPDATE users_main SET Cursed='1' WHERE ID='$UserID'");
	$DB->query("UPDATE users_info SET AdminComment=CONCAT('".sqltime()." - Account cursed at $LoggedUser[BytesDownloaded] bytes downloaded for accessing the site from ".db_string($_SERVER['HTTP_REFERER'])."

', AdminComment) WHERE UserID='$LoggedUser[ID]'");

}

$DB->query("INSERT INTO users_downloads (UserID, TorrentID, Time) VALUES ('$UserID', '$TorrentID', '".sqltime()."') ON DUPLICATE KEY UPDATE Time=VALUES(Time)");

/*
// Where is the .torrent file?
$File = TORRENTS_DIRECTORY.'/'.$TorrentID.'.torrent';

// Make sure file exists
if (!is_file($File) || !is_readable($File)) { error(404); }

// Open torrent file into $Torrent array
$File = fopen($File, 'rb'); // open file for reading
$Contents = fread($File, 10000000);
*/
$DB->query("SELECT File FROM torrents_files WHERE TorrentID='$TorrentID'");
list($Contents) = $DB->next_record(MYSQLI_NUM, array(0));
//echo $Contents;
$Contents = unserialize(base64_decode($Contents));
$Tor = new TORRENT($Contents, true); // New TORRENT object
//$Tor->dump();
// Set torrent announce URL
$Tor->set_announce_url(ANNOUNCE_URL.'/'.$TorrentPass.'/announce');
//$Tor->dump();
// Remove multiple trackers from torrent
unset($Tor->Val['announce-list']);
// Remove web seeds (put here for old torrents not caught by previous commit
unset($Tor->Val['url-list']);
//die();
// Torrent name takes the format of Artist - Album - YYYY (Media - Format - Encoding)

$TorrentName='';

if ($Artist != '') {
	$TorrentName = $Artist;
}

$TorrentName.=$Name;

if ($Year>0) { $TorrentName.=' - '.$Year; }

if ($Media!='') { $TorrentInfo.=$Media; }

if ($Format!='') {
	if ($TorrentInfo!='') { $TorrentInfo.=' - '; }
	$TorrentInfo.=$Format;
}

if ($Encoding!='') {
	if ($TorrentInfo!='') { $TorrentInfo.=' - '; }
	$TorrentInfo.=$Encoding;
}

if ($TorrentInfo!='') { $TorrentName.=' ('.$TorrentInfo.')'; }

if($_GET['mode'] == 'bbb'){
	$TorrentName = $Artists.' -- '.$Name;
}

if (!$TorrentName) { $TorrentName="No Name"; }

if($DownloadAlt) {
	header('Content-Type: text/plain');
	header('Content-Disposition: inline; filename="'.file_string($TorrentName).'.txt"');
	//header('Content-Length: '.strlen($Tor->enc()));
	echo $Tor->enc();
	
} elseif(!$DownloadAlt || $Failed) {
	header('Content-Type: application/x-bittorrent');
	header('Content-Disposition: inline; filename="'.file_string($TorrentName).'.torrent"');
	//header('Content-Length: '.strlen($Tor->enc()));
	echo $Tor->enc();
}
?>
