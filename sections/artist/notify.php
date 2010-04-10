<?
if(!check_perms('site_torrents_notify')) { error(403); }

$ArtistName = $_GET['artistname'];
$DB->query("SELECT ID, Artists FROM users_notify_filters WHERE Label='Artist notifications' AND UserID='$LoggedUser[ID]' ORDER BY ID LIMIT 1");
if($DB->record_count() == 0) {
	$DB->query("INSERT INTO users_notify_filters (UserID, Label, Artists) VALUES ('$LoggedUser[ID]', 'Artist notifications', '|".db_string($ArtistName)."|')");
} else {
	list($ID, $ArtistNames) = $DB->next_record();
	$ArtistNames.=$ArtistName.'|';
	$DB->query("UPDATE users_notify_filters SET Artists='".db_string($ArtistNames)."' WHERE ID='$ID'");
}
$Cache->delete_value('notify_filters_'.$LoggedUser['ID']);
header('Location: '.$_SERVER['HTTP_REFERER']);

?>
