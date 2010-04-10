<?
//******************************************************************************//
//--------------- Fill a request -----------------------------------------------//

$RequestID = $_POST['requestid'];
if(!is_number($RequestID)) { error(0); }

$URL = trim($_POST['url']);

include(SERVER_ROOT.'/classes/class_validate.php');
$Validate = new VALIDATE;

// Make sure the URL they entered is on our site, and is a link to a torrent
$URLRegex = '/^https?:\/\/(www\.|ssl\.)?'.NONSSL_SITE_URL.'\/torrents\.php\?id=([0-9]+)/i';
$Validate->SetFields('url', '1','regex','The URL must be a link to a torrent on the site.',array('regex'=>$URLRegex));
$Err=$Validate->ValidateForm($_POST); // Validate the form

if($Err) { // if something didn't validate
	$_SESSION['Error'] = $Err;
	header('Location: requests.php?action=viewrequest&id='.$RequestID);
	exit;
}

// Get torrent ID
$URLRegex = '/torrents\.php\?id=([0-9]+)/i';
preg_match($URLRegex, $URL, $Matches);
$TorrentID=$Matches[1];
if(!$TorrentID || (int)$TorrentID == 0){ error(404); }

$DB->query("SELECT ID, Time FROM torrents_group WHERE ID='$TorrentID'");
list($GroupID, $FillTime) = $DB->next_record();
$FillTime = strtotime($FillTime);
if($FillTime>time()-3600) {
	$DB->query("SELECT ID FROM torrents WHERE UserID='$LoggedUser[ID]' AND GroupID='$TorrentID'");
	if($DB->record_count()<1) {
		error('There is a one hour grace period for new uploads, to allow the torrent\'s uploader to fill the request');
	}
}

if(!$GroupID) {
	error('The torrent was not found in the database');
}

$DB->query("SELECT 
	r.Name, 
	ag.Name AS ArtistName,
	r.Bounty,
	r.Filled,
	r.FillerID,
	r.UserID
	FROM requests AS r 
	LEFT JOIN artists_group AS ag ON ag.ArtistID=r.ArtistID AND r.ArtistID<>''
	WHERE r.ID='$RequestID'");
list($Name, $ArtistName, $Bounty, $Filled, $FillerID, $RequesterID) = $DB->next_record();

// Fill request
if(!$Filled && !$FillerID) {
	
	$DB->query("
		UPDATE requests SET 
		FillerID='".$LoggedUser['ID']."',
		Filled='$TorrentID',
		TimeFilled='".sqltime()."'
		WHERE ID=$RequestID");
	
	$FullName = ($ArtistName) ? $ArtistName.' - ' : '';
	$FullName .= $Name;
	
	// PM Voters - one of the only queries on the site that is done in a while loop
	$DB->query("SELECT DISTINCT UserID FROM requests_votes WHERE RequestID='$RequestID'");
	$UserIDs = $DB->to_array();
	foreach ($UserIDs as $UserID) {
		list($UserID) = $UserID;
		send_pm($UserID,0,db_string('The request "'.$FullName.'" has been filled'),db_string('One of your requests - '.$FullName.' - has been filled. You can view it at '.$URL), '');
	}
	
	write_log("Request $RequestID ($FullName) was filled by user $LoggedUser[ID] ($LoggedUser[Username]) with the torrent $TorrentID, for a ".get_size($Bounty)." bounty.");
	
	// Give bounty
	$DB->query("UPDATE users_main SET Uploaded=Uploaded+'$Bounty' WHERE ID='$LoggedUser[ID]'");
	
	// Contest
	/*
	$DB->query("INSERT INTO users_points (UserID, GroupID) VALUES ('$LoggedUser[ID]', '$TorrentID') ON DUPLICATE KEY UPDATE Points=Points+1;");
	$DB->query("UPDATE torrents SET FreeTorrent='1',FreeLeechType='2',flags='2' WHERE GroupID='$TorrentID'");
	*/
	$Cache->delete_value('user_stats_'.$LoggedUser['ID']);
	header('Location: requests.php?action=viewrequest&id='.$RequestID);
} else {
	error('That request has already been filled.');
}
?>
