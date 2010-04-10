<?
$CollageID = $_POST['collageid'];
if(!is_number($CollageID)) { error(0); }

$DB->query("UPDATE collages SET Description='".db_string($_POST['description'])."' WHERE ID='$CollageID'");

if (check_perms('site_collages_delete')) {
	$DB->query("UPDATE collages SET Name='".db_string($_POST['name'])."' WHERE ID='$CollageID'");
}

$Cache->delete_value('collage_'.$CollageID);
header('Location: collages.php?id='.$CollageID);
?>
