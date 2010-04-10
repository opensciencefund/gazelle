<?
/************************************************************************
||------------|| Delete artist ||--------------------------------------||

This is a very powerful page - it deletes an artist, and all associated
requests and torrents. It is called when $_GET['action'] == 'delete'.

************************************************************************/

authorize();

$ArtistID = $_GET['artistid'];
if(!is_number($ArtistID) || empty($ArtistID)) { error(0); }

if(!check_perms('site_delete_artist') || !check_perms('torrents_delete')) { error(403); }

show_header('Artist deleted');

$DB->query('SELECT Name FROM artists_group WHERE ArtistID='.$ArtistID);
list($Name) = $DB->next_record();

$DB->query('SELECT tg.Name, 
					tg.ID 
				FROM torrents_group AS tg 
				LEFT JOIN torrents_artists AS ta ON ta.GroupID=tg.ID 
				WHERE ta.ArtistID='.$ArtistID);
if($DB->record_count() > 0) {
?>
	<div class="thin">
		There are still torrents that have <a href="artist.php?id=<?=$ArtistID?>" title="View Artist"><?=$Name?></a> as an artist<br>
		Please remove the artist from these torrents manually before attempting to delete.<br>
		<div class="box pad">
			<ul>
<?
	while(list($GroupName, $GroupID) = $DB->next_record(MYSQLI_NUM, true)) {
?>
				<li>
					<a href="torrents.php?id=<?=$GroupID?>" title="View Torrent"><?=$GroupName?></a>
				</li>
<?
	}
?>
			</ul>
		</div>
	</div>
<?

} else {
	delete_artist($ArtistID);
?>
<div class="thin">Artist deleted!</div>
<?
}
show_footer();?>
