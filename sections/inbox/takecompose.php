<?
if(!isset($_POST['toid']) || !is_number($_POST['toid'])) { error(404); }

if (isset($_POST['convid']) && is_number($_POST['convid'])) {
	$ConvID = $_POST['convid'];
	$Subject='';
	$DB->query("SELECT UserID FROM pm_conversations_users WHERE UserID='$LoggedUser[ID]' AND ConvID='$ConvID'");
	if($DB->record_count() == 0) {
		error(403);
	}
} else {
	$ConvID='';
	$Subject = trim($_POST['subject']);
	if (empty($Subject)) {
		$Err = "You can't send a message without a subject";
	}
}
$Body = trim($_POST['body']);
if(empty($Body)) {
	$Err = "You can't send a message without a body!";
}

if(!empty($Err)) {
	save_message($Err);
	//header('Location: inbox.php?action=compose&to='.$_POST['toid']);
	$ToID = $_POST['toid'];
	$Return = true;
	include(SERVER_ROOT.'/sections/inbox/compose.php');
	die();
}

$ConvID = send_pm($_POST['toid'],$LoggedUser['ID'],db_string($Subject),db_string($Body),$ConvID);

header('Location: inbox.php');
?>
