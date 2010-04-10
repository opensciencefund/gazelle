<?
enforce_login();
if(!isset($_REQUEST['action'])) { $_REQUEST['action'] = ''; }
switch($_REQUEST['action']) {
	case 'takecompose':
		require('takecompose.php');
		break;
	case 'takeedit':
		require('takeedit.php');
		break;
	case 'compose':
		require('compose.php');
		break;
	case 'viewconv':
		require('conversation.php');
		break;
	case 'masschange':
		require('massdelete_handle.php');
		break;
	case 'get_post':
		require('get_post.php');
		break;		
	default:
		require(SERVER_ROOT.'/sections/inbox/inbox.php');
}
