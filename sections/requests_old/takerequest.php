<?
//******************************************************************************//
//--------------- Make a request -----------------------------------------------//
if(!check_perms('site_submit_requests') || $LoggedUser['BytesUploaded'] < 250*1024*1024){
	error('403');
}
include(SERVER_ROOT.'/classes/class_validate.php');
$Validate = new VALIDATE;

//----- Validate


$Validate->SetFields('artist',
		'0','string','The artist name must be between 1 and 100 characters.',array('maxlength'=>100, 'minlength'=>1));
$Validate->SetFields('name',
		'1','string','The title must be between 1 and 100 characters.',array('maxlength'=>100, 'minlength'=>1));
$Validate->SetFields('description',
		'1','string','You must enter a description.',array('maxlength'=>10000, 'minlength'=>2));
$Validate->SetFields('tags',
		'1','string','You must enter at least one tag.',array('maxlength'=>255, 'minlength'=>2));

$Err=$Validate->ValidateForm($_POST); // Validate the form
if($Err){
	$_SESSION['Error'] = $Err;
	$_SESSION['data'] = $_POST;
	header('Location: requests.php?action=new');
	exit;
}
//----- Database stuff

$Name = trim($_POST['name']);
$Artist = trim($_POST['artist']);
$ArtistID = 0;

if(!empty($Artist)){
	$Artist = "'".db_string($Artist)."'";
	$Announce = $Artist.' - '.$Name;
	//Try to get the artist id
	$DB->query("
		SELECT
		aa.ArtistID
		FROM artists_alias AS aa
		WHERE aa.Name=$Artist");
	if($DB->record_count() > 0){ // Artist exists
		list($ArtistID) = $DB->next_record();
		$Cache->delete_value('artist_'.$ArtistID);
	} else { // Create artist
			$DB->query("INSERT INTO artists_group (Name) VALUES ($Artist)");
			$ArtistID = $DB->inserted_id();
			$Cache->increment('stats_artist_count');
	
			$DB->query("INSERT INTO artists_alias (ArtistID, Name) VALUES ('$ArtistID', $Artist)");
			$AliasID = $DB->inserted_id();


	}
} else {
	$Announce = $Name;
}

// Request
$DB->query("
	INSERT INTO requests 
	(UserID, Name, Description, TimeAdded, Bounty, ArtistID) VALUES
	('$LoggedUser[ID]', '".db_string($Name)."', '".db_string($_POST['description'])."', '".sqltime()."', 50*1024*1024, '$ArtistID')");
$RequestID = $DB->inserted_id();

$Announce .= ' http://'.NONSSL_SITE_URL.'/requests.php?action=viewrequest&id='.$RequestID;

// Tags
$Tags = explode(',', $_POST['tags']);
foreach($Tags as $Tag) {
	$Tag = sanitize_tag($Tag);
	
	$DB->query("INSERT INTO tags 
		(Name, UserID) VALUES 
		($Tag, $LoggedUser[ID]) 
		ON DUPLICATE KEY UPDATE Uses=Uses+1;
	");
	$TagID = $DB->inserted_id();
	
	$DB->query("INSERT IGNORE INTO requests_tags
		(TagID, RequestID, UserID) VALUES 
		($TagID, $RequestID, $LoggedUser[ID])
	");
}

$Announce .= ' - '.$_POST['tags'];

// Vote
$DB->query('INSERT INTO requests_votes (RequestID, UserID) VALUES (\''.$RequestID.'\', \''.$LoggedUser['ID'].'\')');

// Subtract amount from user
$DB->query('UPDATE users_main SET Uploaded=Uploaded-100*1024*1024 WHERE ID='.$LoggedUser['ID']);
$Cache->delete_value('user_stats_'.$LoggedUser['ID']);
$_SESSION['clearnext'] = true;

//Announce
send_irc('PRIVMSG #'.NONSSL_SITE_URL.'-requests :'.$Announce);

header('Location: requests.php');
?>
