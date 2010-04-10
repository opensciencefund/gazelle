<?
if(!check_perms('admin_reports')) {
	error(403);
}

if(empty($_POST['reportid']) && !is_number($_POST['reportid'])) {
	error(403);
}
$ReportID = $_POST['reportid'];

$DB->query("UPDATE reports 
			SET Status='Resolved',
				ResolvedTime='".sqltime()."',
				ResolverID='".$LoggedUser['ID']."'
			WHERE ID='".db_string($ReportID)."'");

$Cache->delete_value('num_other_reports');

header('Location: reports.php');
?>
