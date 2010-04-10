<?
/*
Make a new request
*/

$Action = $_GET['action'];

if(!check_perms('site_submit_requests') && $Action == 'new') {
	error('You do not have a high enough class to make a request.');
}

if($LoggedUser['BytesUploaded'] < 250*1024*1024 && $_GET['action'] == 'new') {
	error('You do not have enough uploaded to make a request.');
}

if(!empty($_SESSION['data'])) {
	$Data = $_SESSION['data'];
}
if($_GET['action'] == 'edit') {
	$Action = 'edit';
	$RequestID = $_GET['requestid'];
	if(!is_number($RequestID)) { error(0); }
	
	$DB->query("SELECT
		r.Name AS name,
		r.Description AS description,
		aa.Name AS artist,
		r.Filled,
		r.UserID,
		r.TimeAdded
		FROM requests AS r
		LEFT JOIN artists_alias AS aa ON aa.ArtistID=r.ArtistID AND r.ArtistID!=0
		WHERE r.ID='$RequestID'");
	$Data = $DB->next_record();
	
	$DB->query("SELECT 
		t.Name
		FROM requests_tags AS rt
		JOIN tags AS t ON t.ID=rt.TagID
		WHERE rt.RequestID='$RequestID'");
	$Data['tags'] = implode(', ', $DB->collect('Name'));
	
	// To determine if we can edit the artist/name 
	// (Allowing users to edit these values later gives them the ability to create 'new' requests for no bounty)
	$Data['TimeAdded'] = strtotime($Data['TimeAdded']);
	if($Data['TimeAdded'] > time()-3600 || check_perms('site_moderate_requests')) {
		$New = true;
	} else {
		$New = false;
	}
} else {
	$Action = 'new';
	$New = true;
}
show_header( ($Action == 'new') ? 'Make a request' : 'Edit request');
//---------------------------------------------------- ?>
<div class="thin">
	<h2><?= ($Action == 'new') ? 'Make a request' : 'Edit request' ?></h2>
	
	<div class="box pad">
<?
if(!empty($_SESSION['Error'])) {
	echo '<p style="color: red;text-align:center;">'.$_SESSION['Error'].'</p>';
	unset($_SESSION['Error']);
}
?>
		<form action="" method="post">
			<div>
			<? if ($Action == 'new') { ?> 
				<input type="hidden" name="action" value="takerequest" />
			<? } else { ?> 
				<input type="hidden" name="action" value="takeedit" />
				<input type="hidden" name="requestid" value="<?=$RequestID?>" />
			<? } ?> 
			</div>
			<table>
				<tr>
					<td colspan="2" class="center">Please make sure your request follows <a href="/rules.php?p=requests">the request rules!</a></td>
				</tr>
<? if($New) { ?>
				<tr>
					<td class="label">Artist </td>
					<td>
						<input type="text" name="artist" size="85" value="<?=(!empty($Data['artist']) ? display_str($Data['artist']) : '')?>" />
						<p class="min_padding">(Optional - only applies to music requests)</p>
					</td>
				</tr>
				<tr>
					<td class="label">Title</td>
					<td><input type="text" name="name" size="85" value="<?=(!empty($Data['name']) ? display_str($Data['name']) : '')?>" /></td>
				</tr>
<? } ?>
				<tr>
					<td class="label">Tags</td>
						<td><input type="text" name="tags" size="85" value="<?=(!empty($Data['tags']) ? display_str($Data['tags']) : '')?>" />
						<p class="min_padding">(Comma-separated list of tags)</p>
					</td>
				</tr>
				<tr>
					<td class="label">Description</td>
					<td>
						<textarea name="description" cols="74" rows="7"><?=(!empty($Data['description']) ? display_str($Data['description']) : '')?></textarea> <br />
					</td>
				</tr>
<?   if($Action == 'new') { ?> 
				<tr>
					<td colspan="2" class="center">It will cost you 100MB of upload credit to make this request. 50MB will be left as bounty for the request filler. <strong>If the torrent that is filled is not satisfactory, you can unfill the request - however, you can never get the bounty back.</strong></td>
				</tr>
<?   } ?> 
				<tr>
					<td colspan="2" class="center">
						<input type="submit" value="<?= ($Action == 'new') ? 'Create request' : 'Edit request' ?>" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<?
unset($_SESSION['data']);
show_footer(); 
?>
