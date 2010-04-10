<?

//Set by system
if(!$_POST['groupid'] || !is_number($_POST['groupid'])) {
	error(404);
}
$GroupID = $_POST['groupid'];

//Usual perm checks
if(!check_perms('torrents_edit')) {
	$DB->query("SELECT UserID FROM torrents WHERE GroupID = ".$GroupID);
	if(!in_array($LoggedUser['ID'], $DB->collect('UserID'))) { 
		error(403);
	}
}

//Escape fields
$Year = db_string((int)$_POST['year']);
$RecordLabel = db_string($_POST['record_label']);
$CatalogueNumber = db_string($_POST['catalogue_number']);



$DB->query("UPDATE torrents_group SET 
	Year = '$Year',
	RecordLabel = '".$RecordLabel."',
	CatalogueNumber = '".$CatalogueNumber."'
	WHERE ID = ".$GroupID);

$DB->query("SELECT ID FROM torrents WHERE GroupID='$GroupID'");
while(list($TorrentID) = $DB->next_record()) {
	$Cache->delete_value('torrent_download_'.$TorrentID);
}
update_hash($GroupID);
$Cache->delete_value('torrents_details_'.$GroupID);

header("Location: torrents.php?id=".$GroupID);
?>
