<?
enforce_login();
if(!check_perms('site_upload')) { error(403); }
if($LoggedUser['DisableUpload']) {
	error_message('Your upload privileges have been revoked.');
	//Can't use referrer as might not show_message()
	header('Location: index.php');
}
//build the page

if(!empty($_POST['submit'])) {
	include('upload_handle.php');
} else {
	include(SERVER_ROOT.'/sections/upload/upload.php');
}
?>
