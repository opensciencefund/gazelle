<?

include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

$RequestID = $_GET['id'];
if(!is_number($RequestID)) { error(0); }

$DB->query("SELECT
	r.ID,
	r.Name,
	r.Description,
	r.ArtistID,
	ag.Name AS ArtistName,
	r.TimeAdded,
	COUNT(v.RequestID) AS Votes,
	r.FillerID,
	filler.Username,
	r.Filled,
	r.Bounty,
	r.UserID,
	u.Username
	FROM requests AS r
	LEFT JOIN users_main AS u ON u.ID=UserID
	LEFT JOIN users_main AS filler ON filler.ID=FillerID AND FillerID!=0
	LEFT JOIN artists_group AS ag ON ag.ArtistID=r.ArtistID AND r.ArtistID!=0
	JOIN requests_votes AS v ON v.RequestID=r.ID
	WHERE r.ID='$RequestID'
	GROUP BY v.RequestID");
if($DB->record_count() == 0) { error(404); }

list($ID, $Name, $Description, $ArtistID, $ArtistName, $TimeAdded, $Votes, $FillerID, $FillerName, $Filled, $Bounty, $UserID, $Username) = $DB->next_record();

show_header('View request: '.$ArtistName.' - '.$Name,'comments,requests');

?>
<div class="thin">
	<h2><a href="requests.php">Requests</a> &gt; 
<? if($ArtistID) { ?> 
		<a href="artist.php?id=<?=$ArtistID?>"><?=$ArtistName?></a> - 
<? } ?> 	
		<?=$Name?></h2>
	<div class="linkbox">
<? if(check_perms('site_moderate_requests') || ($LoggedUser['ID'] == $UserID && $Votes < 2)) { ?> 
			<a href="requests.php?action=edit&amp;requestid=<?=$RequestID?>">[Edit]</a>
			<a href="requests.php?action=delete&amp;id=<?=$RequestID?>">[Delete]</a>
<? } ?>
			<a href="reports.php?action=report&amp;type=request&amp;id=<?=$RequestID?>">[Report Request]</a>
	</div>
<? if(!empty($_SESSION['Error'])) { ?>
	<p style="color: red;text-align:center;"><?=$_SESSION['Error']?></p>
<? 	unset($_SESSION['Error']);
   } ?>
	<table>
		<tr>
			<td class="label">Name</td>
			<td>
				<strong>
<? if($ArtistID) { ?> 
					<a href="artist.php?id=<?=$ArtistID?>"><?=$ArtistName?></a> - 
<? } ?> 	
					<?=$Name?>
				</strong> 
				was requested <?=time_diff($TimeAdded)?>
				by user <strong><?=format_username($UserID, $Username)?></strong>
			</td>
		</tr>
		<tr>
			<td class="label">Votes</td>
			<td>
				<?=$Votes?> 
<? if(!$Filled && check_perms('site_vote')) { ?>
				&nbsp;<a href="requests.php?action=vote&amp;id=<?=$ID?>&amp;auth=<?=$LoggedUser['AuthKey']?>"><strong>(+)</strong></a>
				<strong>Costs 20 MB</strong>
<? }?> 
			</td>
		</tr>
<? if(!$Filled && check_perms('site_vote')) { ?>
		<tr id="voting">
			<td class="label">Custom bounty (MB)</td>
			<td>
				<input type="text" id="amount_box"  size="8" />
				<input type="button" value="Preview" onclick="Calculate();"/>
				<strong>50% of this is deducted as tax by the system.</strong>
			</td>
		</tr>
		<tr>
			<td class="label">Post vote information</td>
			<td>
				<form action="requests.php" method="get" id="request_form">
					<input type="hidden" name="action" value="vote" />
					<input type="hidden" name="id" value="<?=$ID?>" />
					<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
					<input type="hidden" id="amount" name="amount">
					<input type="hidden" id="current_uploaded" value="<?=$LoggedUser['BytesUploaded']?>" />
					<input type="hidden" id="current_downloaded" value="<?=$LoggedUser['BytesDownloaded']?>" />
					If you add the entered <strong><span id="new_bounty">0.00 MB</span></strong> of bounty, your new stats will be: <br/>
					Uploaded: <span id="new_uploaded"><?=get_size($LoggedUser['BytesUploaded'])?></span>
					Ratio: <span id="new_ratio"><?=ratio($LoggedUser['BytesUploaded'],$LoggedUser['BytesDownloaded'])?></span>
				</form>
			</td>
		</tr>
<? }?> 
		<tr id="bounty">
			<td class="label">Bounty</td>
			<td><?=get_size($Bounty)?></td>
		</tr>
		<tr>
			<td class="label">Filled</td>
			<td>
<? if($Filled > 0) { ?>
				<strong><a href="torrents.php?id=<?=$Filled?>">Yes</a></strong>, 
				by user <?=format_username($FillerID, $FillerName)?>
<?	if($LoggedUser['ID'] == $UserID || $LoggedUser['ID'] == $FillerID || check_perms('site_moderate_requests')) { ?>
					<strong><a href="requests.php?action=unfill&amp;id=<?=$RequestID?>">(Unfill)</a></strong> Unfilling a request without a valid, nontrivial reason will result in a warning. 
<?   	} ?> 
<? } else { ?>
				No
<? } ?>
			</td>
		</tr>
<? if(!$Filled) { ?>
		<tr>
			<td class="label" valign="top">Fill request</td>
			<td>
				<form action="" method="post">
					<div>
						<input type="hidden" name="action" value="fill" />
						<input type="hidden" name="requestid" value="<?=$RequestID?>" />
						<input type="text" size="40" name="url" />
						<input type="submit" value="Fill request" />
						<br /> 
						Should be the full URL of the  torrent group (e.g. http://<?=NONSSL_SITE_URL?>/torrents.php?id=xxxx).
					</div>
				</form>
				
			</td>
		</tr>
	
<? } 
	
	?>
		<tr>
			<td colspan="2" class="center"><strong>Description</strong></td>
		</tr>
		<tr>
			<td colspan="2"><?=$Text->full_format($Description)?></td>
		</tr>
	</table>
<?

$Results = $Cache->get_value('request_comments_'.$RequestID);
if($Results === false) {
	$DB->query("SELECT
			COUNT(c.ID)
			FROM requests_comments as c
			WHERE c.RequestID = '$RequestID'");
	list($Results) = $DB->next_record();
	$Cache->cache_value('request_comments_'.$RequestID, $Results, 0);
}

list($Page,$Limit) = page_limit(TORRENT_COMMENTS_PER_PAGE,$Results);

//Get the cache catalogue
$CatalogueID = floor((TORRENT_COMMENTS_PER_PAGE*$Page-TORRENT_COMMENTS_PER_PAGE)/THREAD_CATALOGUE);
$CatalogueLimit=$CatalogueID*THREAD_CATALOGUE . ', ' . THREAD_CATALOGUE;

//---------- Get some data to start processing

// Cache catalogue from which the page is selected, allows block caches and future ability to specify posts per page
$Catalogue = $Cache->get_value('request_comments_'.$RequestID.'_catalogue_'.$CatalogueID);
if($Catalogue === false) {
	$DB->query("SELECT
			c.ID,
			c.AuthorID,
			c.AddedTime,
			c.Body,
			c.EditedUserID,
			c.EditedTime,
			u.Username
			FROM requests_comments as c
			LEFT JOIN users_main AS u ON u.ID=c.EditedUserID
			WHERE c.RequestID = '$RequestID'
			ORDER BY c.ID
			LIMIT $CatalogueLimit");
	$Catalogue = $DB->to_array(false,MYSQLI_ASSOC);
	$Cache->cache_value('request_comments_'.$RequestID.'_catalogue_'.$CatalogueID, $Catalogue, 0);
}

//This is a hybrid to reduce the catalogue down to the page elements: We use the page limit % catalogue
$Thread = array_slice($Catalogue,((TORRENT_COMMENTS_PER_PAGE*$Page-TORRENT_COMMENTS_PER_PAGE)%THREAD_CATALOGUE),TORRENT_COMMENTS_PER_PAGE,true);
?>
	<div class="linkbox"><a name="comments"></a>
<?
$Pages=get_pages($Page,$Results,TORRENT_COMMENTS_PER_PAGE,9,'#comments');
echo $Pages;
?>
	</div>
<?

//---------- Begin printing
foreach($Thread as $Key => $Post){
	list($PostID, $AuthorID, $AddedTime, $Body, $EditedUserID, $EditedTime, $EditedUsername) = array_values($Post);
	list($AuthorID, $Username, $PermissionID, $Artist, $Donor, $Warned, $Avatar, $Enabled, $UserTitle) = array_values(user_info($AuthorID));
?>
<table class="forum_post box vertical_margin" id="post<?=$PostID?>">
	<tr class="colhead_dark">
		<td colspan="2">
			<span style="float:left;"><a href='#post<?=$PostID?>'>#<?=$PostID?></a>
				by <strong><?=format_username($AuthorID, $Username, $Donor, $Warned, $Enabled, $PermissionID)?></strong> <?=time_diff($AddedTime)?> <a href="reports.php?action=report&amp;type=requests_comment&amp;id=<?=$PostID?>">[Report Comment]</a>
				- <a href="#quickpost" onclick="Quote('<?=$PostID?>','<?=$Username?>');">[Quote]</a>
<?if ($AuthorID == $LoggedUser['ID'] || check_perms('site_moderate_forums')){ ?>				- <a href="#post<?=$PostID?>" onclick="Edit_Form('<?=$PostID?>','<?=$Key?>');">[Edit]</a><? }
if (check_perms('site_moderate_forums')){ ?>				- <a href="#post<?=$PostID?>" onclick="Delete('<?=$PostID?>');">[Delete]</a> <? } ?>
			</span>
			<span id="bar<?=$PostID?>" style="float:right;">
				<a href="#">&uarr;</a>
			</span>
		</td>
	</tr>
	<tr>
		<td class="avatar" valign="top">
<? if(empty($HeavyInfo['DisableAvatars'])) { ?>
	<? if ($Avatar) { ?>
			<img src="<?=$Avatar?>" width="150" alt="<?=$Username ?>'s avatar" />
	<? } else { ?>
			<img src="<?=STATIC_SERVER?>common/avatars/default.png" width="150" alt="Default avatar" />
	<?
	}
}
?>
		</td>
		<td class="body" valign="top">
			<div id="content<?=$PostID?>">
<?=$Text->full_format($Body)?>
<? if($EditedUserID){ ?>
				<br /><br />Last edited by
				<?=format_username($EditedUserID, $EditedUsername) ?> <?=time_diff($EditedTime)?>
<? } ?>
			</div>
		</td>
	</tr>
</table>
<?	} ?>
		<div class="linkbox">
		<?=$Pages?>
		</div>
<?
if(!$LoggedUser['DisablePosting']) { ?>
			<br />
			<h3>Post reply</h3>
			<div class="box pad" style="padding:20px 10px 10px 10px;">
				<form id="quickpostform" action="" method="post" style="display: block; text-align: center;">
					<div id="quickreplypreview" class="box hidden" style="text-align: left; padding: 10px;"></div>
					<div id="quickreplytext">
						<input type="hidden" name="action" value="reply" />
						<input type="hidden" name="requestid" value="<?=$RequestID?>" />
						<textarea id="quickpost" name="body"  cols="70"  rows="8"></textarea> <br />
					</div>
					<div id="quickreplybuttons">
						<input type="button" value="Preview" onclick="Quick_Preview();" />
						<input type="submit" value="Submit reply" />
					</div>
				</form>
			</div>
<? } ?>
</div>
<? show_footer(); ?>
