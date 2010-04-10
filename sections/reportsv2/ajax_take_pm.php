<?
/*
 * This is the AJAX backend for the SendNow() function.
 */

if(!check_perms('admin_reports')){
	echo 'HAX on premissions!';
	die();
}

$Recipient = $_POST['pm_type'];

if(isset($_POST['uploader_pm']) && $_POST['uploader_pm'] != "") {
	$Message = $_POST['uploader_pm'];
} else {
	//No message given
	die();
}

if(!is_number($_POST['categoryid'])) {
	echo 'HAX on categoryid!';
	die();
} else {
	$CategoryID = $_POST['categoryid'];
}

if (array_key_exists($_POST['type'], $Types[$CategoryID])) {
	$ReportType = $Types[$CategoryID][$_POST['type']];
} else if(array_key_exists($_POST['type'],$Types['master'])) {
	$ReportType = $Types['master'][$_POST['type']];
} else {
	//There was a type but it wasn't an option!
	echo 'HAX on section type';
	die();
}

if(!isset($_POST['from_delete'])) {
	$Report = true;
} else if(!is_number($_POST['from_delete'])) {
	echo 'Hax occured in from_delete';
}

if($Recipient == 'Uploader') {
	$ToID = $_POST['uploaderid'];
	if($Report) {
		$Message = "You uploaded the above torrent, it has been reported for the reason: ".$ReportType['title'].":\n\"".$_POST['report_reason']."\"\n\n".$Message;
	} else {
		$Message = "I am PMing you as you are the uploader of the above torrent.\n\n".$Message;
	}
} else if($Recipient == 'Reporter') {
	$ToID = $_POST['reporterid'];
	$Message = "You reported the above torrent for the reason ".$ReportType['title'].":\n\"".$_POST['report_reason']."\"\n\n".$Message;
} else {
	$Err = "Something went horribly wrong";
}

$Subject = $_POST['raw_name'];

if(!is_number($ToID)) {
	$Err = "Haxx occuring, non number present";
}

if($ToID == $LoggedUser['ID']) {
	$Err = "That's you!";
}

if(isset($Err)) {
	echo $Err;
} else {
	send_pm($ToID, $LoggedUser['ID'], db_string($Subject), db_string($Message));
}