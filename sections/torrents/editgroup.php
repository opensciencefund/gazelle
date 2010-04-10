<?
/************************************************************************
||------------|| Edit artist wiki page ||------------------------------||

This page is the page that is displayed when someone feels like editing 
an artist's wiki page.

It is called when $_GET['action'] == 'edit'. $_GET['artistid'] is the 
ID of the artist, and must be set.

The page inserts a new revision into the wiki_artists table, and clears 
the cache for the artist page. 

************************************************************************/

$GroupID = $_GET['groupid'];
if(!is_number($GroupID) || !$GroupID) { error(0); }

// Get the artist name and the body of the last revision
$DB->query("SELECT
	tg.Name,
	wt.Image,
	wt.Body,
	tg.WikiImage,
	tg.WikiBody,
	tg.Year,
	tg.RecordLabel,
	tg.CatalogueNumber,
	tg.ReleaseType
	FROM torrents_group AS tg
	LEFT JOIN wiki_torrents AS wt ON wt.RevisionID=tg.RevisionID
	WHERE tg.ID='$GroupID'");
list($Name, $Image, $Body, $WikiImage, $WikiBody, $Year, $RecordLabel, $CatalogueNumber, $ReleaseType) = $DB->next_record();

if(!$Name) { error(404); }

if(!$Body) { $Body = $WikiBody; $Image = $WikiImage; }

show_header('Edit torrent group');

// Start printing form
?>
<div class="center thin">
	<h2>Edit <a href="torrents.php?id=<?=$GroupID?>"><?=$Name?></a></h2>
	<div class="box pad">
		<form action="torrents.php" method="post">
			<div>
				<input type="hidden" name="action" value="takegroupedit" />
				<input type="hidden" name="groupid" value="<?=$GroupID?>" />
				<h3>Image</h3>
				<input type="text" name="image" size="92" value="<?=$Image?>" /><br />
				<h3>Description</h3>
				<textarea name="body" cols="91" rows="20"><?=$Body?></textarea> <br />
				<select id="releasetype" name="releasetype">
<?
			foreach ($ReleaseTypes as $Key => $Val) {
				echo "<option value='$Key'";
				if($Key == $ReleaseType){ echo " selected='selected'"; }
				echo ">";
				echo $Val;
				echo "</option>\n";
			}

?>
				</select><br />
				<h3>Edit summary</h3>
				<input type="text" name="summary" size="92" /><br />
				<div style="text-align: center;">
					<input type="submit" value="Submit" />
				</div>
			</div>
		</form>
	</div>
<?	$DB->query("SELECT UserID FROM torrents WHERE GroupID = ".$GroupID);
	//Users can edit the group info if they've uploaded a torrent to the group or have torrents_edit
	if(in_array($LoggedUser['ID'], $DB->collect('UserID')) || check_perms('torrents_edit')) { ?> 
	<h2>Non-wiki group editing</h2>
	<div class="box pad">
		<form action="torrents.php" method="post">
			<input type="hidden" name="action" value="nonwikiedit" />
			<input type="hidden" name="groupid" value="<?=$GroupID?>" />
			<table cellpadding="3" cellspacing="1" border="0" class="border" width="100%">
				<tr>
					<td colspan="2" class="center">This is for editing the information related to the <strong>original release</strong> only.</td>
				</tr>
				<tr>
					<td class="label">Year</td>
					<td>
						<!--<strip>--><input type="hidden" name="oldyear" value="<?=$Year?>" /><!--</strip-->
						<input type="text" name="year" size="10" value="<?=$Year?>" />
					</td>
				</tr>
				<tr>
					<td class="label">Record label</td>
					<td>
						<input type="text" name="record_label" size="40" value="<?=$RecordLabel?>" />
					</td>
				</tr>
				<tr>
					<td class="label">Catalogue Number</td>
					<td>
						<input type="text" name="catalogue_number" size="40" value="<?=$CatalogueNumber?>" />
					</td>
				</tr>								
			</table>
			<input type="submit" value="Edit" />
		</form>
	</div>
<? 
	}
	if(check_perms('torrents_edit')) { 
?> 
	<h2>Rename (won't merge)</h2>
	<div class="box pad">
		<form action="torrents.php" method="post">
			<div>
				<input type="hidden" name="action" value="rename" />
				<input type="hidden" name="groupid" value="<?=$GroupID?>" />
				<input type="text" name="name" size="92" value="<?=$Name?>" />
				<div style="text-align: center;">
					<input type="submit" value="Rename" />
				</div>
				
			</div>
		</form>
	</div>
	<h2>Merge with another group</h2>
	<div class="box pad">
		<form action="torrents.php" method="post">
			<div>
				<input type="hidden" name="action" value="merge" />
				<input type="hidden" name="groupid" value="<?=$GroupID?>" />
				<h3>Target Group ID</h3>
				<input type="text" name="targetgroupid" size="10" />
				<div style="text-align: center;">
					<input type="submit" value="Merge" />
				</div>
				
			</div>
		</form>
	</div>
<?	} ?> 
</div>
<? show_footer() ?>
