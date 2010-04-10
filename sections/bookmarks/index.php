<?
enforce_login();
// Number of users per page 
define('BOOKMARKS_PER_PAGE', '20');
if(!empty($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
		case 'add':
			require(SERVER_ROOT.'/sections/bookmarks/add.php');
			break;

		case 'remove':
			authorize();
			$DB->query("DELETE FROM bookmarks_torrents WHERE UserID='".$LoggedUser['ID']."' AND GroupID='".db_string($_GET['groupid'])."'");
			$Cache->delete_value('bookmarks_'.$UserID);
			break;
		case 'remove_snatched':
			authorize();
			$DB->query("DELETE b FROM bookmarks_torrents AS b WHERE b.UserID='".$LoggedUser['ID']."' AND b.GroupID IN(SELECT DISTINCT t.GroupID FROM torrents AS t INNER JOIN xbt_snatched AS s ON s.fid=t.ID AND s.uid='".$LoggedUser['ID']."')");
			$Cache->delete_value('bookmarks_'.$UserID);
			header('Location: bookmarks.php');
			die();
			break;
		default:
			error(0);
	}
} else {
	require(SERVER_ROOT.'/sections/bookmarks/bookmarks.php');
}
?>
