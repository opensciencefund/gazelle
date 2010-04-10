<?
if (!empty($_GET['userid']) && is_number($_GET['userid'])) {
	$UserID = $_GET['userid'];
} else {
	error(0);
}

if(!check_perms('zip_downloader')){ error(403); }

if ($UserID != $LoggedUser['ID']) {
	$DB->query("SELECT Paranoia FROM users_main WHERE ID='".$UserID."'");
	list($Paranoia)=$DB->next_record();
}

require(SERVER_ROOT.'/classes/class_torrent.php');
require(SERVER_ROOT.'/classes/class_zip.php');

if (empty($_GET['type'])) {
	error(0);
} else {
	switch ($_GET['type']) {
		case 'uploads':
			if(!check_perms('users_view_seedleech') && $UserID!=$LoggedUser['ID'] && $Paranoia>=3) { error(403); }
			$SQL = "WHERE t.UserID='$UserID'";
			break;
		case 'snatches':
			if(!check_perms('users_view_seedleech') && $UserID!=$LoggedUser['ID'] && $Paranoia>=2) { error(403); }
				$SQL = "JOIN xbt_snatched AS x ON t.ID=x.fid WHERE x.uid='$UserID'";
			break;
		case 'seeding':
			if (!check_perms('users_view_seedleech') && $UserID!=$LoggedUser['ID'] && $Paranoia>=2) { error(403); }
				$SQL = "JOIN xbt_files_users AS xfu ON t.ID = xfu.fid WHERE xfu.uid='$UserID' AND xfu.remaining = 0";
			break;
		default:
			error(0);
	}
}

ZIP::unlimit();

$DB->query("SELECT 
	DATE_FORMAT(t.Time,'%b \'%y') AS Month,
	t.GroupID,
	t.Media,
	t.Format,
	t.Encoding,
	IF(t.RemasterYear=0,tg.Year,t.RemasterYear),
	tg.Name,
	t.Size,
	f.File
	FROM torrents as t 
	JOIN torrents_group AS tg ON t.GroupID=tg.ID 
	LEFT JOIN torrents_files AS f ON t.ID=f.TorrentID
	".$SQL);
$Downloads = $DB->to_array(false,MYSQLI_NUM,false);
$Artists = get_artists($DB->collect('GroupID'));

list($UserID, $Username) = array_values(user_info($UserID));
$Zip = new ZIP($Username.'\'s '.ucfirst($_GET['type']));
foreach($Downloads as $Download) {
	list($Month, $GroupID, $Media, $Format, $Encoding, $Year, $Album, $Size, $Contents) = $Download;
	$Artist = display_artists($Artists[$GroupID],false);
	$Contents = unserialize(base64_decode($Contents));
	$Tor = new TORRENT($Contents, true);
	$Tor->set_announce_url(ANNOUNCE_URL.'/'.$LoggedUser['torrent_pass'].'/announce');
	unset($Tor->Val['announce-list']);
	$Zip->add_file($Tor->enc(), file_string($Month).'/'.file_string($Artist.$Album).' - '.file_string($Year).' ('.file_string($Media).' - '.file_string($Format).' - '.file_string($Encoding).').torrent');
}
$Zip->close_stream();
