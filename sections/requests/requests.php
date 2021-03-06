<?

$Queries = array();

$OrderWays = array('votes', 'bounty', 'created', 'lastvote', 'filled');
list($Page,$Limit) = page_limit(REQUESTS_PER_PAGE);
$Submitted = !empty($_GET['submit']);

if(empty($_GET['type'])) { 
	$Title = 'Requests';
	if(!check_perms('site_see_old_requests') || empty($_GET['showall'])) {
		$SS->SetFilter('visible', array(1));
	}
} else {
	switch($_GET['type']) {
		case 'created':
			$Title = 'My requests';
			$SS->SetFilter('userid', array($LoggedUser['ID']));
			break;
		case 'voted':
			if(!empty($_GET['userid'])) {
				if(is_number($_GET['userid'])) {
					$DB->query("SELECT Username FROM users_main WHERE ID = ".$_GET['userid']);
					list($Username) = $DB->next_record();
					$Title = "Requests voted for by ".$Username;
					$SS->SetFilter('voter', array($_GET['userid']));
				} else {
					error(404);
				}
			} else {
				$Title = "Requests I've voted on";
				$SS->SetFilter('voter', array($LoggedUser['ID']));
			}
			break;
		case 'filled':
			if(empty($_GET['userid']) || !is_number($_GET['userid'])) {
				error(404);
			} else {
				$Title = "Requests filled";
				$SS->SetFilter('fillerid', array($_GET['userid']));
			}
			break;
		default:
			error(404);
	}
}

if(($Submitted && empty($_GET['show_filled'])) || (!$Submitted && (!empty($_GET['type']) && $_GET['type'] != "filled"))) {
	$SS->SetFilter('torrentid', array(0));
}

if(!empty($_GET['search'])) {
	$Queries[] = '@* '.$SS->escape_string($_GET['search']);
}

$TagMatcher = (!empty($_GET['tagmatcher']) && $_GET['tagmatcher'] == "any") ? "any" : "all";

if(!empty($_GET['tags'])){
	$Tags = explode(',', $_GET['tags']);
	$TagNames = array();
	foreach ($Tags as $Tag){
		$Tag = sanitize_tag($Tag);
		if(!empty($Tag)) {
			$TagNames[] = $Tag;
		}
	}
	
	$Tags = get_tags($TagNames);
	if(count($Tags) < 1) {
		$Fail = true;
	} else {
		$SS->SetFilter('tagid', array_keys($Tags));
	}
}

if(!empty($_GET['filter_cat'])) {
	$Keys = array_keys($_GET['filter_cat']);
	$SS->SetFilter('categoryid', $Keys);
}

if(!empty($_GET['releases'])) {
	$ReleaseArray = $_GET['releases'];
	if(count($ReleaseArray) != count($ReleaseTypes)) {
		foreach($ReleaseArray as $Index => $Value) {
			if(!is_number($Value)) {
				error(0);
			}
		}
		
		$SS->SetFilter('releasetype', $ReleaseArray);
	}
}

if(!empty($_GET['formats'])) {
	$FormatArray = $_GET['formats'];
	if(count($FormatArray) != count($Formats)) {
		$FormatNameArray = array();
		foreach($FormatArray as $Index => $MasterIndex) {
			if(array_key_exists($Index, $Formats)) {
				$FormatNameArray[$Index] = $Formats[$MasterIndex];
			} else {
				//Hax
				error(0);
			}
		}
		
		$Queries[]='@formatlist '.implode(' ', $FormatNameArray);
	}
}

if(!empty($_GET['media'])) {
	$MediaArray = $_GET['media'];
	if(count($MediaArray) != count($Media)) {
		$MediaNameArray = array();
		foreach($MediaArray as $Index => $MasterIndex) {
			if(array_key_exists($Index, $Media)) {
				$MediaNameArray[$Index] = $Media[$MasterIndex];
			} else {
				//Hax
				error(0);
			}
		}

		$Queries[]='@medialist '.implode(' ', $MediaNameArray);
	}
}

if(!empty($_GET['bitrates'])) {
	$BitrateArray = $_GET['bitrates'];
	if(count($BitrateArray) != count($Bitrates)) {
		$BitrateNameArray = array();
		foreach($BitrateArray as $Index => $MasterIndex) {
			if(array_key_exists($Index, $Bitrates)) {
				$BitrateNameArray[$Index] = $Bitrates[$MasterIndex];
			} else {
				//Hax
				error(0);
			}
		}

		$Queries[]='@bitratelist '.implode(' ', $BitrateNameArray);
	}
}

if(!empty($_GET['requestor']) && check_perms('site_see_old_requests')) {
	if(is_number($_GET['requestor'])) {
		$SS->SetFilter('uesrid', array($_GET['requestor']));
	} else {
		error(404);
	}
}

if(!empty($_GET['page']) && is_number($_GET['page'])) {
	$Page = $_GET['page'];
	$SS->limit(($Page - 1) * REQUESTS_PER_PAGE, REQUESTS_PER_PAGE);
} else {
	$Page = 1;
	$SS->limit(0, REQUESTS_PER_PAGE);
}

if(empty($_GET['order'])) {
	$CurrentOrder = 'created';
	$CurrentSort = 'desc';
	$Way = SPH_SORT_ATTR_DESC;
	$NewSort = 'asc';
} else {
	if(in_array($_GET['order'], $OrderWays)) {
		$CurrentOrder = $_GET['order'];
		if($_GET['sort'] == 'asc' || $_GET['sort'] == 'desc') {
			$CurrentSort = $_GET['sort'];
			$Way = ($CurrentSort == 'asc' ? SPH_SORT_ATTR_ASC : SPH_SORT_ATTR_DESC);
			$NewSort = ($_GET['sort'] == 'asc' ? 'desc' : 'asc');
		} else {
			error(404);
		}
	} else {
		error(404);
	}
}

switch($CurrentOrder) {
	case 'votes' :
		$OrderBy = "Votes";
		break;
	case 'bounty' :
		$OrderBy = "Bounty";
		break;
	case 'created' :
		$OrderBy = "TimeAdded";
		break;
	case 'lastvote' :
		$OrderBy = "LastVote";
		break;
	case 'filled' :
		$OrderBy = "TimeFilled";
		break;
	default :
		$OrderBy = "TimeAdded";
		break;
}
//print($Way); print($OrderBy); die();
$SS->SetSortMode($Way, $OrderBy);

if(count($Queries) > 0) {
	$Query = implode(' ',$Queries);
} else {
	$Query='';
}

$SS->set_index('requests requests_delta');
$SphinxResults = $SS->search($Query, '', 0, array(), '', '');
$NumResults = $SS->TotalResults;

//We don't use sphinxapi's default cache searcher, we use our own functions
if(!empty($SphinxResults['notfound'])) {
	$SQLResults = get_requests($SphinxResults['notfound']);
	if(is_array($SQLResults['notfound'])) {
		//Something wasn't found in the db, remove it from results
		reset($SQLResults['notfound']);
		foreach($SQLResults['notfound'] as $ID) {
			unset($SQLResults['matches'][$ID]);
			unset($SphinxResults['matches'][$ID]);
		}
	}
	
	// Merge SQL results with memcached results
	foreach($SQLResults['matches'] as $ID => $SQLResult) {
		$SphinxResults['matches'][$ID] = $SQLResult;
		
		//$Requests['matches'][$ID] = array_merge($Requests['matches'][$ID], $SQLResult);
		//We ksort because depending on the filter modes, we're given our data in an unpredictable order
		//ksort($Requests['matches'][$ID]);
	}
}


$PageLinks = get_pages($Page, $NumResults, REQUESTS_PER_PAGE);

$Requests = $SphinxResults['matches'];

$CurrentURL = get_url(array('order', 'sort'));

show_header($Title, 'requests');

?>
<div class="thin">
	<h2><?=$Title?></h2>
	<div class="linkbox">
<?	if(check_perms('site_submit_requests')){ ?> 
		<a href="requests.php?action=new">[New request]</a>
		<a href="requests.php?type=created">[My requests]</a>
<?	} 
	if(check_perms('site_vote')){?> 
		<a href="requests.php?type=voted">[Requests I've voted on]</a>
<?	} ?> 
	</div>
	<div class="center">
		<form action="" method="get">
			<input type="hidden" name="submit" value="true" />
			<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
				<tr>
					<td class="label">Search terms:</td>
					<td>
						<input type="text" name="search" size="75" value="<?if(isset($_GET['search'])) { echo display_str($_GET['search']); } ?>" />
					</td>
				</tr>
				<tr>
					<td class="label">Tags (comma-separated):</td>
					<td>
						<input type="text" name="tags" size="60" value="<?= (!empty($TagNames) ? display_str(implode(', ', $TagNames)) : '') ?>" />
						<?/*
						<input type="radio" name="tagmatcher" value="any" <?=((empty($TagMatcher) || $TagMatcher == "any") ? ' checked="checked" ' : '')?>/>Any &nbsp;
						<input type="radio" name="tagmatcher" value="all" <?=((!empty($TagMatcher) && $TagMatcher == "all") ? ' checked="checked" ' : '')?>/>All
						*/?>
					</td>
				</tr>
				<tr>
					<td class="label">Include filled:</td>
					<td>
						<input type="checkbox" name="show_filled" <? if(($Submitted && empty($_GET['show_filled'])) || (!$Submitted && (!empty($_GET['type']) && $_GET['type'] != "filled"))) { ?>checked="checked"<? } ?> />
					</td>
				</tr>
<?	if(check_perms('site_see_old_requests')){ ?> 
				<tr>
					<td class="label">Include old:</td>
					<td>
						<input type="checkbox" name="showall" <? if(!empty($_GET['showall'])) {?>checked="checked"<? } ?> />
					</td>
				</tr>
<?	/* ?> 
				<tr>
					<td class="label">Requested by:</td>
					<td>
						<input type="text" name="requester" size="75" value="<?=display_str($_GET['requester'])?>" />
					</td>
				</tr>
<?	*/} ?>
			</table>
			<table class="cat_list">
<?
$x=1;
reset($Categories);
foreach($Categories as $CatKey => $CatName) {
	if($x%8==0 || $x==1) {
?>
					<tr class="cat_list">
<?	} ?>
						<td>
							<input type="checkbox" name="filter_cat[<?=($CatKey+1)?>]" id="cat_<?=($CatKey+1)?>" value="1" <? if(isset($_GET['filter_cat'][$CatKey+1])) { ?>checked="checked"<? } ?> />
							<label for="cat_<?=($CatKey+1)?>"><?=$CatName?></label>
						</td>
<?
	if($x%7==0) {
?>
					</tr>
<?
	}
	$x++;
}
?>
			</table>
			<table>
				<tr id="release_list">
					<td class="label">Release Types</td>
					<td>
						<input type="checkbox" id="toggle_releases" onchange="Toggle('releases', 0)" <?=(!$Submitted || count($ReleaseArray) == count($ReleaseTypes) ? ' checked="checked"' : '')?>/> All
<?		$i = 0;
		foreach ($ReleaseTypes as $Key => $Val) {
			if($i % 8 == 0) echo "<br />";?>
						<input type="checkbox" name="releases[]" value="<?=$Key?>"
							<?=(((!$Submitted) || !empty($ReleaseArray) && in_array($Key, $ReleaseArray)) ? ' checked="checked" ' : '')?>
						/> <?=$Val?>
<?			$i++;
		}?>
					</td>
				</tr>
				<tr id="format_list">
					<td class="label">Formats</td>
					<td>
						<input type="checkbox" id="toggle_formats" onchange="Toggle('formats', 0);" <?=(!$Submitted || count($FormatArray) == count($Formats) ? ' checked="checked"' : '')?>/> All
<?		foreach ($Formats as $Key => $Val) {
			if($Key % 8 == 0) echo "<br />";?>
						<input type="checkbox" name="formats[]" value="<?=$Key?>" 
							<?=(((!$Submitted) || !empty($FormatArray) && in_array($Key, $FormatArray)) ? ' checked="checked" ' : '')?>
						/> <?=$Val?>
<?		}?>
					</td>
				</tr>				
				<tr id="bitrate_list">
					<td class="label">Bitrates</td>
					<td>
						<input type="checkbox" id="toggle_bitrates" onchange="Toggle('bitrates', 0);"<?=(!$Submitted || count($BitrateArray) == count($Bitrates) ? ' checked="checked"' : '')?> /> All
<?		foreach ($Bitrates as $Key => $Val) {
			if($Key % 8 == 0) echo "<br />";?>
						<input type="checkbox" name="bitrates[]" value="<?=$Key?>" 
							<?=(((!$Submitted) || !empty($BitrateArray) && in_array($Key, $BitrateArray)) ? ' checked="checked" ' : '')?>
						/> <?=$Val?>
<?		}?>
					</td>
				</tr>
				<tr id="media_list">
					<td class="label">Media</td>
					<td>
						<input type="checkbox" id="toggle_media" onchange="Toggle('media', 0);"<?=(!$Submitted || count($MediaArray) == count($Media) ? ' checked="checked"' : '')?> /> All
<?		foreach ($Media as $Key => $Val) {
			if($Key % 8 == 0) echo "<br />";?>
						<input type="checkbox" name="media[]" value="<?=$Key?>" 
							<?=(((!$Submitted) || !empty($MediaArray) && in_array($Key, $MediaArray)) ? ' checked="checked" ' : '')?>
						/> <?=$Val?>
<?		}?>
					</td>
				</tr>
			</table>
			<table>
				<tr>
					<td colspan="2" class="center">
						<input type="submit" value="Search requests" />
					</td>
				</tr>
			</table>	
		</form>
	</div>
	
	<div class="linkbox">
		<?=$PageLinks?>
	</div>
	<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
		<tr class="colhead_dark">
			<td style="width: 38%;">
				<strong>Request Name</strong>
			</td>
			<td>
				<a href="requests.php?order=votes&amp;sort=<?=(($CurrentOrder == 'votes') ? $NewSort : 'asc')?>&amp;<?=$CurrentURL ?>"><strong>Votes</strong></a>
			</td>
			<td>
				<a href="requests.php?order=bounty&amp;sort=<?=(($CurrentOrder == 'bounty') ? $NewSort : 'asc')?>&amp;<?=$CurrentURL ?>"><strong>Bounty</strong></a>
			</td>
			<td>
				<a href="requests.php?order=filled&amp;sort=<?=(($CurrentOrder == 'filled') ? $NewSort : 'asc')?>&amp;<?=$CurrentURL ?>"><strong>Filled</strong></a>
			</td>
			<td>
				<strong>Filled by</strong>
			</td>
			<td>
				<strong>Requested by</strong>
			</td>
			<td>
				<a href="requests.php?order=created&amp;sort=<?=(($CurrentOrder == 'created') ? $NewSort : 'asc')?>&amp;<?=$CurrentURL ?>"><strong>Created</strong></a>
			</td>
			<td>
				<a href="requests.php?order=lastvote&amp;sort=<?=(($CurrentOrder == 'lastvote') ? $NewSort : 'asc')?>&amp;<?=$CurrentURL ?>"><strong>Last Vote</strong></a>
			</td>
		</tr>

<?	if($NumResults == 0 || !empty($Fail)) { ?>
		<tr class="rowb">
			<td colspan="8">
				Nothing found!
			</td>
		</tr>
<?	} else {
		$Row = 'a';
		$TimeCompare = 1267643718; // Requests v2 was implemented 2010-03-03 20:15:18
		foreach ($Requests as $RequestID => $Request) {
			
			//list($BitrateList, $CatalogueNumber, $CategoryID, $Description, $FillerID, $FormatList, $RequestID, $Image, $LogCue, $MediaList, $ReleaseType, 
			//	$Tags, $TimeAdded, $TimeFilled, $Title, $TorrentID, $RequestorID, $RequestorName, $Year, $RequestID, $Categoryid, $FillerID, $LastVote, 
			//	$ReleaseType, $TagIDs, $TimeAdded, $TimeFilled, $TorrentID, $RequestorID, $Voters) = array_values($Request);
			
			list($RequestID, $RequestorID, $RequestorName, $TimeAdded, $LastVote, $CategoryID, $Title, $Year, $Image, $Description, $CatalogueNumber, 
				$ReleaseType, $BitrateList, $FormatList, $MediaList, $LogCue, $FillerID, $FillerName, $TorrentID, $TimeFilled) = $Request;
				
			$RequestVotes = get_votes_array($RequestID);
			
			$VoteCount = count($RequestVotes['Voters']);
			
			if($CategoryID == 0) {
				$CategoryName = "Unknown";
			} else {
				$CategoryName = $Categories[$CategoryID - 1];
			}
			
			$IsFilled = ($TorrentID != 0);
			
			if($CategoryName == "Music") {
				$ArtistForm = get_request_artists($RequestID);
				$ArtistLink = display_artists($ArtistForm, true, true);
				$FullName = $ArtistLink."<a href='requests.php?action=view&amp;id=".$RequestID."'>".$Title." [".$Year."]</a>";
			} else if($CategoryName == "Audiobooks" || $CategoryName == "Comedy") {
				$FullName = "<a href='requests.php?action=view&amp;id=".$RequestID."'>".$Title." [".$Year."]</a>";
			} else {
				$FullName ="<a href='requests.php?action=view&amp;id=".$RequestID."'>".$Title."</a>";
			}
			
			$Row = ($Row == 'a') ? 'b' : 'a';
			
			$Tags = $Request['Tags'];
?>
		<tr class="row<?=$Row?>">
			<td>
				<?=$FullName?>
				<div class="tags">
<?			
			$TagList = array();
			foreach($Tags as $TagID => $TagName) {
				$TagList[] = "<a href='requests.php?tags=".$TagName."'>".display_str($TagName)."</a>";
			}
			$TagList = implode(', ', $TagList);
?>
					<?=$TagList?>
				</div>
			</td>
			<td>
				<?=$VoteCount?>
<?  	 	if(!$IsFilled && check_perms('site_vote')){ ?>
				<input type="hidden" id="auth" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
				&nbsp;&nbsp; <a href="javascript:Vote()"><strong>(+)</strong></a>
<?  		} ?> 
			</td>
			<td>
				<?=get_size($RequestVotes['TotalBounty'])?>
			</td>
			<td>
<?   		if($IsFilled){ ?>
				<a href="torrents.php?<?=(strtotime($TimeFilled)<$TimeCompare?'id=':'torrentid=').$TorrentID?>"><strong><?=time_diff($TimeFilled)?></strong></a>
<?   		} else { ?>
				<strong>No - <a href="upload.php?requestid=<?=$RequestID?>">[Upload]</a></strong>
<?   		} ?>
			</td>
			<td>
<?			if($IsFilled){ ?>
			<a href="user.php?id=<?=$FillerID?>"><?=$FillerName?></a>
<?			} else { ?>
			--
<?			} ?>
			</td>
			<td>
				<a href="user.php?id=<?=$RequestorID?>"><?=$RequestorName?></a>
			</td>
			<td>
				<?=time_diff($TimeAdded)?>
			</td>
			<td>
				<?=time_diff($LastVote)?>
			</td>
		</tr>
<?
		} // while
	} // else
?>
	</table>
	<div class="linkbox">
		<?=$PageLinks?>
	</div>
</div>
<?
show_footer();
?>
