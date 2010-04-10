<?
//******************************************************************************//
//--------------- Take edit ----------------------------------------------------//
// This pages handles the backend of the 'edit torrent' function. It checks	 //
// the data, and if it all validates, it edits the values in the database	   //
// that correspond to the torrent in question.								  //
//******************************************************************************//

enforce_login();

require(SERVER_ROOT.'/classes/class_validate.php');
$Validate = new VALIDATE;

//******************************************************************************//
//--------------- Set $Properties array ----------------------------------------//
// This is used if the form doesn't validate, and when the time comes to enter  //
// it into the database.														//

$Properties=array();
$TypeID = (int)$_POST['type'];
$Type = $Categories[$TypeID-1];
$TorrentID = (int)$_POST['torrentid'];
$Properties['Remastered'] = (isset($_POST['remaster']))? 1 : 0;
if($Properties['Remastered']) {
	$Properties['UnknownRelease'] = (isset($_POST['unknown'])) ? 1 : 0;
	$Properties['RemasterYear'] = $_POST['remaster_year'];
	$Properties['RemasterTitle'] = $_POST['remaster_title'];
	$Properties['RemasterRecordLabel'] = $_POST['remaster_record_label'];
	$Properties['RemasterCatalogueNumber'] = $_POST['remaster_catalogue_number'];
}
if(!$Properties['Remastered'] || $Properties['UnknownRelease']) {
	$Properties['UnknownRelease'] = 0;
	$Properties['RemasterYear'] = '';
	$Properties['RemasterTitle'] = '';
	$Properties['RemasterRecordLabel'] = '';
	$Properties['RemasterCatalogueNumber'] = '';
}
$Properties['Scene'] = (isset($_POST['scene']))? 1 : 0;
$Properties['HasLog'] = (isset($_POST['flac_log']))? 1 : 0;
$Properties['HasCue'] = (isset($_POST['flac_cue']))? 1 : 0;
$Properties['LogScore'] = $_POST['log_score'];
$Properties['AdjustmentReason'] = $_POST['adjustment_reason'];
$Properties['Format'] = $_POST['format'];
$Properties['Media'] = $_POST['media'];
$Properties['Bitrate'] = $_POST['bitrate'];
$Properties['Encoding'] = $_POST['bitrate'];
$Properties['TorrentDescription'] = $_POST['release_desc'];
if($_POST['album_desc']) {
	$Properties['GroupDescription'] = $_POST['album_desc'];
}
if(check_perms('torrents_freeleech')) {
	$Properties['FreeLeech'] = (isset($_POST['freeleech'])) ? 1 : 0;
}

//******************************************************************************//
//--------------- Validate data in edit form -----------------------------------//

$DB->query('SELECT UserID FROM torrents WHERE ID='.$TorrentID);
list($UserID) = $DB->next_record();

if($LoggedUser['ID']!=$UserID && !check_perms('torrents_edit')) {
	error(403);
}

$Validate->SetFields('type','1','number','Not a valid type.',array('maxlength'=>count($Categories), 'minlength'=>1));
$Validate->SetFields('type','1','number','Invalid torrent ID.',array('maxlength'=>1000000000, 'minlength'=>1)); // we shouldn't have torrent IDs higher than a billion
switch ($Type) {
	case 'Music':
		if(!empty($Properties['Remastered']) && !empty($Properties['UnknownRelease'])){
			$Validate->SetFields('remaster_year', '1', 'number', 'Year of remaster/re-issue must be entered.');
		} else {
			$Validate->SetFields('remaster_year', '0','number', 'Invalid remaster year.');
		}
		
		$Validate->SetFields('remaster_title',
			'0','string','Remaster title must be between 2 and 50 characters.',array('maxlength'=>50, 'minlength'=>2));
			
		$Validate->SetFields('remaster_record_label',
			'0','string','Remaster record label must be between 2 and 40 characters.',array('maxlength'=>40, 'minlength'=>2));
			
		$Validate->SetFields('remaster_catalogue_number',
			'0','string','Remaster catalogue number must be between 2 and 40 characters.',array('maxlength'=>40, 'minlength'=>2));
			
				
		$Validate->SetFields('format',
			'1','inarray','Not a valid format.',array('inarray'=>$Formats));
		
		$Validate->SetFields('bitrate',
			'1','inarray','You must choose a bitrate.', array('inarray'=>$Bitrates));
		
		
		// Handle 'other' bitrates
		if($Properties['Encoding'] == 'Other') {
			$Validate->SetFields('other_bitrate',
				'1','text','You must enter the other bitrate (max length: 9 characters).', array('maxlength'=>9));
			$enc = trim($_POST['other_bitrate']);
			if(isset($_POST['vbr'])) { $enc.=' (VBR)'; }
			
			$Properties['Encoding'] = $enc;
			$Properties['Bitrate'] = $enc;
		} else {
			$Validate->SetFields('bitrate',
				'1','inarray','You must choose a bitrate.', array('inarray'=>$Bitrates));
		}
		
		$Validate->SetFields('media',
			'1','inarray','Not a valid media.',array('inarray'=>$Media));
		
		$Validate->SetFields('release_desc',
			'0','string','Invalid release description.',array('maxlength'=>1000000, 'minlength'=>0));
		
		break;
		
	case 'Audiobooks':
	case 'Comedy':
		$Validate->SetFields('title',
			'1','string','Title must be between 2 and 200 characters.',array('maxlength'=>200, 'minlength'=>2));
		
		$Validate->SetFields('year',
			'1','number','The year of the release must be entered.');
		
		$Validate->SetFields('format',
			'1','inarray','Not a valid format.',array('inarray'=>$Formats));
		
		$Validate->SetFields('bitrate',
			'1','inarray','You must choose a bitrate.', array('inarray'=>$Bitrates));
		
		
		// Handle 'other' bitrates
		if($Properties['Encoding'] == 'Other') {
			$Validate->SetFields('other_bitrate',
				'1','text','You must enter the other bitrate (max length: 9 characters).', array('maxlength'=>9));
			$enc = trim($_POST['other_bitrate']);
			if(isset($_POST['vbr'])) { $enc.=' (VBR)'; }
			
			$Properties['Encoding'] = $enc;
			$Properties['Bitrate'] = $enc;
		} else {
			$Validate->SetFields('bitrate',
				'1','inarray','You must choose a bitrate.', array('inarray'=>$Bitrates));
		}
		
		$Validate->SetFields('release_desc',
			'0','string','The release description has a minimum length of 10 characters.',array('maxlength'=>1000000, 'minlength'=>10));
		
		break;
	
	case 'Applications':
	case 'Comics':
	case 'E-Books':
	case 'E-Learning Videos':
		$Validate->SetFields('title',
			'1','string','Title must be between 2 and 200 characters.',array('maxlength'=>200, 'minlength'=>2));
		break;
}

$Err=$Validate->ValidateForm($_POST); // Validate the form

if($Err){ // Show the upload form, with the data the user entered
	error_message($Err);
	header('Location: '.$_SERVER['HTTP_REFERER']);
	die();
}


//******************************************************************************//
//--------------- Make variables ready for database input ----------------------//

// Shorten and escape $Properties for database input
$T = array();
foreach ($Properties as $Key => $Value) {
	$T[$Key]="'".db_string(trim($Value))."'";
	if(!$T[$Key]){
		$T[$Key] = NULL;
	}
}

if(!($Properties['Remastered'] && !$Properties['RemasterYear'] && $TorrentID > 1309521) || check_perms('edit_unknowns')) {
	$AllowRemasterEdits = true;	
}

//******************************************************************************//
//--------------- Start database stuff -----------------------------------------//

// Update info for the torrent
$SQL = "
	UPDATE torrents SET
		Media=$T[Media], 
		Format=$T[Format], 
		Encoding=$T[Encoding], ";
if($AllowRemasterEdits) {
$SQL .=	"RemasterYear=$T[RemasterYear], 
		Remastered=$T[Remastered], 
		RemasterTitle=$T[RemasterTitle], 
		RemasterRecordLabel=$T[RemasterRecordLabel], 
		RemasterCatalogueNumber=$T[RemasterCatalogueNumber], ";
}
$SQL.= "Scene=$T[Scene], 
		Description=$T[TorrentDescription],";

if(check_perms('torrents_freeleech')) {
	$SQL .= "FreeTorrent=$T[FreeLeech],";
}

if(check_perms('users_mod')) {
	if($T[Format] != "'FLAC'") {
		$SQL .= "
	                HasLog='0',
	                HasCue='0',
	                LogScore=0,
	        ";
	} else {
		$SQL .= "
			HasLog=$T[HasLog],
			HasCue=$T[HasCue],
			LogScore=$T[LogScore],
		";
	}

	$DB->query("SELECT LogScore FROM torrents WHERE ID='$TorrentID'");
	list($LogScore) = $DB->next_record();
	
	if($LogScore!=$Properties['LogScore']) {
		$DB->query("UPDATE torrents_logs_new SET
				Adjusted=1,
				AdjustedBy='$LoggedUser[ID]',
				AdjustmentReason=$T[AdjustmentReason]
				WHERE TorrentID='$TorrentID'");
	}
}

$SQL .= "
	flags='2'
	WHERE ID=$TorrentID
";
$DB->query($SQL);

$DB->query("SELECT GroupID, Time FROM torrents WHERE ID='$TorrentID'");
list($GroupID, $Time) = $DB->next_record();

// Competition
if(strtotime($Time)>1241352173) {
	if($_POST['log_score'] == '100') {
		$DB->query("INSERT IGNORE into users_points (GroupID, UserID, Points) VALUES ('$GroupID', '$UserID', '1')");
	}
}
// End competiton

$DB->query("SELECT Name FROM torrents_group WHERE ID=$GroupID");
list($Name) = $DB->next_record();

write_log("Torrent $TorrentID ($Name) in group $GroupID was edited by ".$LoggedUser['Username']); // TODO: this is probably broken
$Cache->delete_value('torrents_details_'.$GroupID);
$Cache->delete_value('torrent_download_'.$TorrentID);

$DB->query("SELECT ArtistID FROM torrents_artists WHERE GroupID = ".$GroupID);
$Artists = $DB->collect('ArtistID');
foreach($Artists as $ArtistID) {
	$Cache->delete_value('artist_'.$ArtistID);
}

update_hash($GroupID);
// All done!

header("Location: torrents.php?id=$GroupID");
?>
