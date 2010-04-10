<?
if(empty($_POST['id']) || !is_number($_POST['id']) || empty($_POST['type']) || empty($_POST['reason'])) {
	error(404);
}

include(SERVER_ROOT.'/sections/reports/array.php');

if(!array_key_exists($_POST['type'], $Types)) {
	error(403);
}
$Short = $_POST['type'];
$Type = $Types[$Short]; 
$ID = $_POST['id'];
$Reason = $_POST['reason'];

show_header('Reported '.$Type['title']);

$DB->query("INSERT INTO reports
				(UserID, ThingID, Type, ReportedTime, Reason)
			VALUES
				(".db_string($LoggedUser['ID']).", ".$ID." , '".$Short."', '".sqltime()."', '".db_string($Reason)."')");

$Cache->delete_value('num_other_reports');

save_message($Type['title']." reported!");

switch($Short) {
	case "request" :
		header('Location: requests.php?action=view&id='.$ID);
		break;
	case "user" :
		header('Location: user.php?id='.$ID);
		break;
	case "collage" :
		header('Location: collages.php?id='.$ID);
		break;
	case "thread" :
		header('Location: forums.php?action=viewthread&threadid='.$ID);
		break;
	case "post" :
		$DB->query("SELECT TopicID FROM forums_posts WHERE ID = ".$ID);
		list($ThreadID) = $DB->next_record();
		//header('Location: forums.php?action=viewthread&threadid='.$ThreadID.'#post'.$ID);
		header('Location: forums.php?action=viewthread&threadid='.$ThreadID);
}

?>
	<div class="thin center">
		<p><?=$Type['title']?> successfully reported.</p>
	</div>
<?
show_footer();
?>
