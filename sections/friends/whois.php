<?
/* 
 * This is a page that will tell you how you know another user
 * I intend for it to tell you if you commented on a users upload / request and if they did so on yours,
 * have PM correspondance with them and whether you've commented on the same thing as them.
 * It'll probably be dirt slow and will thus need to be restricted >.>
 */
show_header();
if(empty($_GET['username'])) {
show_message();
?>
<div class='thin center'>
	<div class="box" style="width:600px; margin:0px auto;">
		<div class="head colhead">
			How do I know...?
		</div>
		<div class='pad'>
			<form action='friends.php' method='get'>
				<input type="hidden" name="action" value="whois" />
				Username:
				<input type="text" width="20" name="username" />
			</form>
		</div>
	</div>
</div>

<?
} else {
	$Username2 = $_GET['username'];
	$DB->query("SELECT ID FROM users_main WHERE Username LIKE '".db_string($Username2)."'");
	if($DB->record_count() < 1) {
		error_message("No user with that username was found.");
		header('Location: friends.php?action=whois');
	}
	list($ID2) = $DB->next_record();
	$ID1 = $LoggedUser['ID'];
	$Username1 = $LoggedUser['Username'];
	
	//Ok, let's begin!
	$Nothing = true;
?>
<div class='thin center'>
	<div class="head">
		These are the ways that you know <strong><a href="user.php?id=<?=$ID2?>"><?=$Username2?></a></strong>
	</div>
	<div class="box">
<?
	//Lets see if 2 has snatched any of 1's torrents
	$DB->query("SELECT ID FROM torrents WHERE UserID=".$ID1);
	if($DB->record_count() > 0) {
		$Uploads = $DB->collect('ID');
		$DB->query("SELECT t.ID,
						t.Media,
						t.Format,
						t.Encoding,
						t.GroupID,
						tg.Name
					FROM xbt_snatched AS x 
					LEFT JOIN torrents AS t ON t.ID=x.fid  
					LEFT JOIN torrents_group AS tg ON t.GroupID=tg.ID 
					WHERE x.fid IN (".implode(',', $Uploads).") 
						AND x.uid=".$ID2);
		if($DB->record_count() > 0) {
			$Nothing = false;
?>
		<div class="head colhead">
			<?=$Username2?> has snatched the following of your uploads:
		</div>
		<div class='pad'>
			<ul>
<? 
			while(list($TorrentID, $Media, $Format, $Encoding, $GroupID, $GroupName) = $DB->next_record()) {
				$Extra = $Format." / ".$Encoding." / ".$Media;
?>
				<li><?=display_artists(get_artist($GroupID), true, false)?> - <a href="torrents.php?id=<?=$GroupID?>"><?=$GroupName?></a> [<a href="torrents.php?id=<?=$GroupID?>&amp;torrentid=<?=$TorrentID?>"><?=$Extra?>]</a></li> 
<?
			}
?>
			</ul>
		</div>
<?
		}	
	}
	
	//Okay, now if 1 has snatched any of 2
	$DB->query("SELECT ID FROM torrents WHERE UserID=".$ID2);
	if($DB->record_count() > 0) {
		$Uploads = $DB->collect('ID');
		$DB->query("SELECT t.ID,
						t.Media,
						t.Format,
						t.Encoding,
						t.GroupID,
						tg.Name
					FROM xbt_snatched AS x 
					LEFT JOIN torrents AS t ON t.ID=x.fid  
					LEFT JOIN torrents_group AS tg ON t.GroupID=tg.ID 
					WHERE x.fid IN (".implode(',', $Uploads).") 
						AND x.uid=".$ID1);
		if($DB->record_count() > 0) {
			$Nothing = false;
?>
		<div class="head colhead">
			You have snatched the following of <?=$Username2?>'s uploads:
		</div>
		<div class='pad'>
			<ul>
<? 
			while(list($TorrentID, $Media, $Format, $Encoding, $GroupID, $GroupName) = $DB->next_record()) {
				$Extra = $Format." / ".$Encoding." / ".$Media;
?>
				<li><?=display_artists(get_artist($GroupID), true, false)?> - <a href="torrents.php?id=<?=$GroupID?>"><?=$GroupName?></a> [<a href="torrents.php?id=<?=$GroupID?>&amp;torrentid=<?=$TorrentID?>"><?=$Extra?>]</a></li> 
<?
			}
?>
			</ul>
		</div>
<?
		}	
	}
?>


		<div class="head colhead">
			Place holder
		</div>
		<div class='pad'>
			Wooooooo
		</div>
		
		
	</div>
</div>
<?
}
show_footer();
?>