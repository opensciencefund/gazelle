<?
//******************************************************************************//
//--------------- Take edit request --------------------------------------------//

include(SERVER_ROOT.'/classes/class_validate.php');
$Validate = new VALIDATE;
$RequestID = $_POST['requestid'];
if(!is_number($RequestID)) { error(0); }

$P = db_array($_POST);

//----- Validate

$Validate->SetFields('artist',
		'0','string','The artist name must be between 2 and 100 characters.',array('maxlength'=>100, 'minlength'=>2));
$Validate->SetFields('name',
		'0','string','The title must be between 2 and 255 characters.',array('maxlength'=>255, 'minlength'=>2));
$Validate->SetFields('description',
		'1','string','You must enter a description.',array('maxlength'=>10000, 'minlength'=>2));
$Validate->SetFields('tags',
		'1','string','You must enter at least one tag.',array('maxlength'=>255, 'minlength'=>2));

$Err=$Validate->ValidateForm($_POST); // Validate the form

if($Err) {
	$_SESSION['Error'] = $Err;
	$_SESSION['data'] = $_POST;
	header('Location: '.$_SERVER['HTTP_REFERER']);
	die();
} else {
	$DB->query("SELECT UserID, TimeAdded FROM requests WHERE ID='$RequestID'");
	list($UserID, $TimeAdded) = $DB->next_record();
	if($LoggedUser['ID']!=$UserID && !check_perms('site_moderate_requests')) {
		error(403);
	}
}

if($TimeAdded > time()-3600 || check_perms('site_moderate_requests')) {
	$New = true;
} else {
	$New = false;
}

//----- Database stuff
if($New) {
	$ArtistID = 0;
	if($_POST['artist']!='') {
		$Artist=$P['artist'];
		//Try to get the artist id
		$DB->query("
			SELECT
			aa.ArtistID
			FROM artists_alias AS aa
			WHERE aa.Name='$Artist'");
		if($DB->record_count() > 0) { // Artist exists
			list($ArtistID) = $DB->next_record();
		} else {
				// Create artist
				$DB->query("
						INSERT INTO artists
						(Name) VALUES
						('$Artist')");
		
				$DB->query("INSERT INTO artists_group (Name) VALUES ('$Artist')");
				$ArtistID = $DB->inserted_id();
				$Cache->increment('stats_artist_count');
		
				$DB->query("INSERT INTO artists_alias (ArtistID, Name) VALUES ('$ArtistID', '$Artist')");
				$AliasID = $DB->inserted_id();
		}
	}
	
	// Request
	$DB->query("
		UPDATE requests SET
		Name = '$P[name]', 
		Description = '$P[description]', 
		ArtistID = '$ArtistID' 
		WHERE ID='$RequestID'");
} else {
	$DB->query("
		UPDATE requests SET
		Description = '$P[description]'
		WHERE ID='$RequestID'");
}
// Tags
$DB->query('DELETE FROM requests_tags WHERE RequestID='.$RequestID);
$Tags = explode(',', $_POST['tags']);
foreach($Tags as $Tag) {
	$Tag = "'".db_string(trim($Tag))."'";
	$Tag = strtolower($Tag);
	$Tag = str_replace(' ', '.', $Tag);
	
	$DB->query("INSERT INTO tags 
		(Name, UserID) VALUES 
		($Tag, $LoggedUser[ID]) 
		ON DUPLICATE KEY UPDATE Uses=Uses+1;
	");
	$TagID = $DB->inserted_id();
	
	$DB->query("INSERT INTO requests_tags
		(TagID, RequestID, UserID) VALUES 
		($TagID, $RequestID, $LoggedUser[ID]) 
	");
}

header('Location: requests.php?action=viewrequest&id='.$RequestID);
?>
