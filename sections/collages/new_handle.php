<?
include(SERVER_ROOT.'/classes/class_validate.php');
$Val = new VALIDATE;

$Val->SetFields('name', '1','string','The name must be between 3 and 100 characters',array('maxlength'=>100, 'minlength'=>3));
$Val->SetFields('description', '1','string','The description must be at least 10 characters',array('maxlength'=>65535, 'minlength'=>10));

$Err = $Val->ValidateForm($_POST);
$P = array();
$P = db_array($_POST);

if(!$Err) {
	$DB->query("SELECT ID FROM collages WHERE Name='$P[name]'");
	if($DB->record_count()>0) {
		$Err = 'That collection already exists!';
	}
}

if($Err) {
	$_SESSION['error_message'] = $Err;
	header('Location: collages.php?action=new');
	die();
}

$DB->query("INSERT INTO collages 
	(Name, Description, UserID) 
	VALUES
	('$P[name]', '$P[description]', $LoggedUser[ID])");

$CollageID = $DB->inserted_id();
$Cache->delete_value('collage_'.$CollageID);
write_log("Collage ".$CollageID." (".$P[name].") was created by ".$LoggedUser['Username']);
header('Location: collages.php?id='.$CollageID);

?>
