<?
if(!check_perms('site_torrents_notify')){ error(403); }
$ArtistList = '';
$TagList = '';
$ReleaseTypeList = '';
$CategoryList = '';
$FormatList = '';
$EncodingList = '';
$MediaList = '';
$FromYear = 0;
$ToYear = 0;
$HasFilter = false;

if($_POST['artists']){
	$ArtistList = '|';
	$Artists = explode(',', $_POST['artists']);
	foreach($Artists as $Artist){
		$ArtistList.=db_string(trim($Artist)).'|';
	}
	$HasFilter = true;
}

if($_POST['excludeva']){
	$ExcludeVA = '1';
	$HasFilter = true;
} else {
	$ExcludeVA = '0';
}

if($_POST['tags']){
	$TagList = '|';
	$Tags = explode(',', $_POST['tags']);
	foreach($Tags as $Tag){
		$TagList.=db_string(trim($Tag)).'|';
	}
	$HasFilter = true;
}

if($_POST['categories']){
	$CategoryList = '|';
	foreach($_POST['categories'] as $Category){
		$CategoryList.=db_string(trim($Category)).'|';
	}
	$HasFilter = true;
}

if($_POST['releasetypes']){
	$ReleaseTypeList = '|';
	foreach($_POST['releasetypes'] as $ReleaseType){
		$ReleaseTypeList.=db_string(trim($ReleaseType)).'|';
	}
	$HasFilter = true;
}

if($_POST['formats']){
	$FormatList = '|';
	foreach($_POST['formats'] as $Format){
		$FormatList.=db_string(trim($Format)).'|';
	}
	$HasFilter = true;
}


if($_POST['bitrates']){
	$EncodingList = '|';
	foreach($_POST['bitrates'] as $Bitrate){
		$EncodingList.=db_string(trim($Bitrate)).'|';
	}
	$HasFilter = true;
}

if($_POST['media']){
	$MediaList = '|';
	foreach($_POST['media'] as $Medium){
		$MediaList.=db_string(trim($Medium)).'|';
	}
	$HasFilter = true;
}

if($_POST['fromyear'] && is_number($_POST['fromyear'])){
	$FromYear = db_string(trim($_POST['fromyear']));
	$HasFilter = true;
	if($_POST['toyear'] && is_number($_POST['toyear'])) {
		$ToYear = db_string(trim($_POST['toyear']));
	} else {
		$ToYear = date('Y')+3;
	}
}

if(!$HasFilter){
	$_SESSION['error'] = 'You must add at least one criterion to filter by';
} elseif(!$_POST['label'] && !$_POST['id']) {
	$_SESSION['error'] = 'You must add a label for the filter set';
}

if($_SESSION['error']){
	header('Location: user.php?action=notify');
	die();
}

$ArtistList = str_replace('||','|',$ArtistList);
$TagList = str_replace('||','|',$TagList);

if($_POST['id'] && is_number($_POST['id'])){
	$DB->query("UPDATE users_notify_filters SET
		Artists='$ArtistList',
		ExcludeVA='$ExcludeVA',
		Tags='$TagList',
		ReleaseTypes='$ReleaseTypeList',
		Categories='$CategoryList',
		Formats='$FormatList',
		Encodings='$EncodingList',
		Media='$MediaList',
		FromYear='$FromYear',
		ToYear='$ToYear'
		WHERE ID='".db_string($_POST['id'])."' AND UserID='$LoggedUser[ID]'");
} else {
	$DB->query("INSERT INTO users_notify_filters 
		(UserID, Label, Artists, ExcludeVA, Tags, ReleaseTypes, Categories, Formats, Encodings, Media, FromYear, ToYear)
		VALUES
		('$LoggedUser[ID]','".db_string($_POST['label'])."','$ArtistList','$ExcludeVA','$TagList','$ReleaseTypeList','$CategoryList','$FormatList','$EncodingList','$MediaList', '$FromYear', '$ToYear')");
}

$Cache->delete_value('notify_filters_'.$LoggedUser['ID']);
header('Location: user.php?action=notify');
?>
