<?
//******************************************************************************//
//--------------- Fill a request -----------------------------------------------//

$RequestID = $_REQUEST['requestid'];
if(!is_number($RequestID)) {
	error(0);
}

//VALIDATION
if(!empty($_GET['torrentid']) && is_number($_GET['torrentid'])) {
	$TorrentID = $_GET['torrentid'];
} else {
	if(empty($_POST['link'])) {
		$Err = "You forgot to supply a link to the filling torrent";
	} else {
		$Link = $_POST['link'];
		if(preg_match("/".TORRENT_REGEX."/i", $Link, $Matches) < 1) {
			$Err = "Your link didn't seem to be a valid torrent link";
		} else {
			$TorrentID = $Matches[0];
		}
	}
	
	if(!empty($Err)) {
		error_message($Err);
		header('Location: requests.php?action=view&id='.$RequestID);
		die();
	}
	
	preg_match("/torrentid=([0-9]+)/i", $Link, $Matches);
	$TorrentID = $Matches[1];
	if(!$TorrentID || !is_number($TorrentID)) {
		error(404);
	}
}

//Torrent exists, check it's applicable
$DB->query("SELECT t.UserID,
				t.Time,
				tg.ReleaseType,
				t.Encoding,
				t.Format,
				t.Media, 
				t.HasLog, 
				t.HasCue, 
				t.LogScore,
				tg.CategoryID,
				IF(t.Remastered = '1', t.RemasterCatalogueNumber, tg.CatalogueNumber)
			FROM torrents AS t
				LEFT JOIN torrents_group AS tg ON t.GroupID=tg.ID
			WHERE t.ID = ".$TorrentID." 
			LIMIT 1");

if($DB->record_count() < 1) {
	//No such torrent
	error(404);
}
list($UploaderID, $UploadTime, $TorrentReleaseType, $Bitrate, $Format, $Media, $HasLog, $HasCue, $LogScore, $TorrentCategoryID, $TorrentCatalogueNumber) = $DB->next_record();

if(time_ago($UploadTime) < 3600 && $UploaderID != $LoggedUser['ID']) {
	$Err = "There is a one hour grace period for new uploads, to allow the torrent's uploader to fill the request";
}


$DB->query("SELECT 
		Title, 
		UserID,
		TorrentID,
		CategoryID,
		ReleaseType,
		CatalogueNumber,
		BitrateList,
		FormatList,
		MediaList,
		LogCue
	FROM requests 
	WHERE ID = ".$RequestID);
list($Title, $RequesterID, $OldTorrentID, $RequestCategoryID, $RequestReleaseType, $RequestCatalogueNumber, $BitrateList, $FormatList, $MediaList, $LogCue) = $DB->next_record();

if(!empty($OldTorrentID)) {
	$Err = "This request has already been filled";
}

if($RequestCategoryID != 0 && $TorrentCategoryID != $RequestCategoryID) {
	$Err = "This torrent is of a different category than the request";
}

$CategoryName = $Categories[$RequestCategoryID - 1];

if($CategoryName == "Music") {
	if($RequestCatalogueNumber) {
		if($TorrentCatalogueNumber != $RequestCatalogueNumber) {
			$Err = "This request requires the catalogue number ".$RequestCatalogueNumber;
		}
	}
	if($Format == "FLAC" && $LogCue) {
		if(strpos($LogCue, "Log") && !$HasLog) {
			$Err = "This request requires a log";
		}
		if(strpos($LogCue, "Cue") && !$HasCue) {
			$Err = "This request requires a cue";
		}
		if(strpos($LogCue, "%")) {
			preg_match("/\d+/", $LogCue, $Matches);
			if((int) $LogScore < (int) $Matches[0]) {
				$Err = "This torrent's log score is too low";
			}
		}
	}
	
	if($BitrateList && $BitrateList != "Any") {
		if(strpos($BitrateList, $Bitrate) === false) {
			$Err = $Bitrate." is not an allowed bitrate for this request";
		}
	}
	if($FormatList && $FormatList != "Any") {
		if(strpos($FormatList, $Format) === false) {
			$Err = $Format." is not an allowed format for this request";
		}
	}
	if($MediaList && $MediaList != "Any") {
		if(strpos($MediaList, $Media) === false) {
			$Err = $Media." is not allowed media for this request";
		}
	}
}

// Fill request
if(!empty($Err)) {
	error_message($Err);
	header('Location: requests.php?action=view&id='.$RequestID);
	die();
}

//We're all good! Fill!
$DB->query("UPDATE requests SET
				FillerID = ".$LoggedUser['ID'].",
				TorrentID = ".$TorrentID.",
				TimeFilled = '".sqltime()."'
			WHERE ID = ".$RequestID);


if($CategoryName == "Music") {
	$ArtistForm = get_request_artists($RequestID);
	$ArtistName = display_artists($ArtistForm, false, true);
	$FullName = $ArtistName.$Title;
} else {
	$FullName = $Title;
}

$DB->query("SELECT UserID FROM requests_votes WHERE RequestID = ".$RequestID);
$UserIDs = $DB->to_array();
foreach ($UserIDs as $User) {
	list($UserID) = $User;
	send_pm($UserID, 0, db_string("The request '".$FullName."' has been filled"), db_string("One of your requests - [url=http://".NONSSL_SITE_URL."/requests.php?action=view&id=".$RequestID."]".$FullName."[/url] - has been filled. You can view it at [url]http://".NONSSL_SITE_URL."/torrents.php?torrentid=".$TorrentID), '');
}

$RequestVotes = get_votes_array($RequestID);
write_log("Request ".$RequestID." (".$FullName.") was filled by user ".$LoggedUser['ID']." (".$LoggedUser['Username'].") with the torrent ".$TorrentID.", for a ".get_size($RequestVotes['TotalBounty'])." bounty.");

// Give bounty
$DB->query("UPDATE users_main
			SET Uploaded = (Uploaded + ".$RequestVotes['TotalBounty'].") 
			WHERE ID = ".$LoggedUser['ID']);


// Contest
/*
$DB->query("INSERT INTO users_points (UserID, GroupID) VALUES ('$LoggedUser[ID]', '$TorrentID') ON DUPLICATE KEY UPDATE Points=Points+1;");
$DB->query("UPDATE torrents SET FreeTorrent='1',FreeLeechType='2',flags='2' WHERE GroupID='$TorrentID'");
*/

$Cache->delete_value('user_stats_'.$LoggedUser['ID']);
$Cache->delete_value('request_'.$RequestID);

update_sphinx_requests($RequestID);

header('Location: requests.php?action=view&id='.$RequestID);
?>
