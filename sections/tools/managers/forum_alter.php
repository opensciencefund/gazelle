<?
if(!check_perms('admin_manage_forums')) { error(403); }
$P = db_array($_POST);
if($_POST['submit'] == 'Delete'){ //Delete
	if(!is_number($_POST['id']) || $_POST['id'] == ''){ error(0); }
	$DB->query('DELETE FROM forums WHERE ID='.$_POST['id']);
} else { //Edit & Create, Shared Validation
	$Val->SetFields('name', '1','string','The name must be set, and has a max length of 40 characters', array('maxlength'=>40, 'minlength'=>1));
	$Val->SetFields('description', '0','string','The description has a max length of 255 characters', array('maxlength'=>255));
	$Val->SetFields('sort', '1','number','Sort must be set');
	$Val->SetFields('categoryid', '1','number','Category must be set');
	$Val->SetFields('minclassread', '1','number','MinClassRead must be set');
	$Val->SetFields('minclasswrite', '1','number','MinClassWrite must be set');
	$Val->SetFields('minclasscreate', '1','number','MinClassCreate must be set');
	$Err=$Val->ValidateForm($_POST); // Validate the form
	if($Err){ error($Err); }

	if($_POST['submit'] == 'Edit'){ //Edit
		if(!is_number($_POST['id']) || $_POST['id'] == ''){ error(0); }
		$DB->query("UPDATE forums SET
			Sort='$P[sort]',
			CategoryID='$P[categoryid]',
			Name='$P[name]',
			Description='$P[description]',
			MinClassRead='$P[minclassread]',
			MinClassWrite='$P[minclasswrite]',
			MinClassCreate='$P[minclasscreate]'
			WHERE ID='$P[id]'");
	} else { //Create
		$DB->query("INSERT INTO forums
			(Sort, CategoryID, Name, Description, MinClassRead, MinClassWrite, MinClassCreate) VALUES
			('$P[sort]', '$P[categoryid]', '$P[name]','$P[description]','$P[minclassread]','$P[minclasswrite]','$P[minclasscreate]')");
	}
}

$Cache->delete('forums_list'); // Clear cache

// Go back
header('Location: tools.php?action=forum')
?>
