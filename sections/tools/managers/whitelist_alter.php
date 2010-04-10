<?
if(!check_perms('admin_whitelist')) {
	error(403);
}

if($_POST['submit'] == 'Delete'){
	if(!is_number($_POST['id']) || $_POST['id'] == ''){
		error("1");
	}
	
	$DB->query('DELETE FROM xbt_client_whitelist WHERE id='.$_POST['id']);
} else { //Edit & Create, Shared Validation
	
	if(empty($_POST['client']) || empty($_POST['peer_id'])) {
		print_r($_POST);
		die();
	}
	
	$Client = db_string($_POST['client']);
	$PeerID = db_string($_POST['peer_id']);

	if($_POST['submit'] == 'Edit'){ //Edit
		if(empty($_POST['id']) || !is_number($_POST['id'])) {
			error("3");
		} else {
			$DB->query("UPDATE xbt_client_whitelist SET
				vstring='".$Client."',
				peer_id='".$PeerID."'
				WHERE ID=".$_POST['id']);
		}
	} else { //Create
		$DB->query("INSERT INTO xbt_client_whitelist
			(vstring, peer_id) 
		VALUES
			('".$Client."','".$PeerID."')");
	}
}

$Cache->delete('whitelisted_clients');

// Go back
header('Location: tools.php?action=whitelist')
?>
