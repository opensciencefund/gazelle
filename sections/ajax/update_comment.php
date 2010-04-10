<?
// perform the back end of updating a report comment

if(!check_perms('admin_reports')){
	error(403);
}

if(!is_number($_GET['id'])) {
	echo 'HAX ATTEMPT!'.$_GET['id'];
	die();
}

$Message = db_string($_GET['message']);
//Message can be blank!

$DB->query("SELECT ModComment FROM reportsv2 WHERE ID=".$_GET['id']);
list($ModComment) = $DB->next_record();
if(isset($ModComment)) {
	$DB->query("Update reportsv2 SET ModComment='".$Message."' WHERE ID=".$_GET['id']);
}
