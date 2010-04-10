<?
//******************************************************************************//
//--------------- Vote on a request --------------------------------------------//
authorize();
if(!check_perms('site_vote')) { error(403); }


if(empty($_GET['id']) || !is_number($_GET['id'])) { error(0); }
$RequestID = $_GET['id'];

if(empty($_GET['amount']) || !is_number($_GET['amount'])){
	$Amount = 20*1024*1024;
} else {
	$Amount = $_GET['amount']*1024*1024;
}
$Bounty = $Amount/2;

$DB->query('SELECT Filled FROM requests WHERE ID='.$RequestID);
list($Filled) = $DB->next_record();

if($LoggedUser['BytesUploaded'] >= $Amount && $Filled == 0){
	
	// Update bounty
	$DB->query("
		UPDATE requests 
		SET Bounty=Bounty+'$Bounty'
		WHERE ID=$RequestID");
	
	// Record vote (one vote per user)
	$DB->query("
		INSERT IGNORE INTO requests_votes 
		(RequestID, UserID) VALUES
		($RequestID, $LoggedUser[ID])");
	
	// Subtract amount from user
	$DB->query("UPDATE users_main SET Uploaded=Uploaded-'$Amount' WHERE ID=".$LoggedUser['ID']);
	$Cache->delete_value('user_stats_'.$LoggedUser['ID']);
}

$_SESSION['clearnext'] = true; // Tell it to clear the next page it hits
header('Location: requests.php?action=viewrequest&id='.$RequestID);
?>