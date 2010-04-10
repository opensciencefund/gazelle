<?
//******************************************************************************//
//--------------- Take unfill request ------------------------------------------//
authorize();

$RequestID = $_GET['requestid'];
if(!is_number($RequestID)){error(0);}

$DB->query('SELECT 
	r.UserID, 
	r.FillerID, 
	r.Bounty,
	r.Name, 
	ag.Name AS ArtistName,
	u.Uploaded
	FROM requests AS r 
	LEFT JOIN users_main AS u ON u.ID=FillerID
	LEFT JOIN artists_group AS ag ON ag.ArtistID=r.ArtistID AND r.ArtistID<>\'\'
	WHERE r.ID=\''.$RequestID.'\'');
list($UserID, $FillerID, $Bounty, $Name, $ArtistName, $Uploaded) = $DB->next_record();

if($LoggedUser['ID'] != $UserID && $LoggedUser['ID'] != $FillerID && !check_perms('site_moderate_requests')) { error(403); }


// Unfill
$DB->query('
	UPDATE requests SET
	Filled=\'0\',
	FillerID=\'0\',
	TimeFilled=\'0000-00-00 00:00:00\',
	Visible=\'1\'
	WHERE ID='.$RequestID);

if($FillerID){ // prevent people from unfilling multiple times to piss people off
	if ($Bounty > $Uploaded) {
		$DB->query('UPDATE users_main SET Downloaded=Downloaded+'.$Bounty.' WHERE ID='.$FillerID);
	} else {
		$DB->query('UPDATE users_main SET Uploaded=Uploaded-'.$Bounty.' WHERE ID='.$FillerID);
	}
}


$FullName = ($ArtistName)? $ArtistName.' - ':'';
$FullName .= $Name;

$DB->query("SELECT ID, UserID 
	FROM pm_conversations AS pc 
	JOIN pm_conversations_users AS pu ON pu.ConvID=pc.ID AND pu.UserID!=0 
	WHERE Subject='The request \"".db_string($FullName)."\" has been filled'");
$ConvIDs = implode(',',$DB->collect('ID'));
$UserIDs = $DB->collect('UserID');

if($ConvIDs){
	$DB->query("DELETE FROM pm_conversations WHERE ID IN($ConvIDs)");
	$DB->query("DELETE FROM pm_conversations_users WHERE ConvID IN($ConvIDs)");
	$DB->query("DELETE FROM pm_messages WHERE ConvID IN($ConvIDs)");
}
foreach($UserIDs as $UserID) {
	$Cache->delete_value('inbox_new_'.$UserID);
}

write_log("Request $RequestID ($FullName), with a ".get_size($Bounty)." bounty, was un-filled by user $LoggedUser[ID] ($LoggedUser[Username])");

header('Location: requests.php?action=viewrequest&id='.$RequestID);
?>
