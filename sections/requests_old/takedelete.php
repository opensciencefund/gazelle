<?
//******************************************************************************//
//--------------- Delete request -----------------------------------------------//

$RequestID = $_POST['id'];
if(!is_number($RequestID)) { error(0); }

$DB->query("SELECT r.UserID,
					r.Name,
					r.Bounty,
					ag.Name AS ArtistName
			FROM requests AS r
			LEFT JOIN artists_group AS ag ON ag.ArtistID=r.ArtistID AND r.ArtistID<>''
			WHERE ID='$RequestID'");
list($UserID, $Name, $Bounty, $ArtistName) = $DB->next_record();

if($LoggedUser['ID'] != $UserID && !check_perms('site_moderate_requests')) { error(403); }

$FullName = ($ArtistName)? $ArtistName.' - ':'';
$FullName .= $Name;

// Delete request
$DB->query("DELETE FROM requests WHERE ID='$RequestID'");

// Delete votes
$DB->query("DELETE FROM requests_votes WHERE RequestID='$RequestID'");

// Delete tags
$DB->query("DELETE FROM requests_tags WHERE RequestID='$RequestID'");

if($UserID != $LoggedUser['ID']) {
	send_pm($UserID, 0, db_string("A request you created has been deleted"), db_string("The request '".$FullName."' was deleted by [url=http://".NONSSL_SITE_URL."/user.php?id=".$LoggedUser['ID']."]".$LoggedUser['Username']."[/url] for the reason: ".$_POST['reason']));
}

write_log("Request $RequestID ($FullName), with a ".get_size($Bounty)." bounty, was deleted by user ".$LoggedUser['ID']." (".$LoggedUser['Username'].") for the reason: ".$_POST['reason']);

header('Location: requests.php');
?>
