<?
if(in_array($_GET['stat'], array('inbox', 'uploads', 'bookmarks', 'notifications', 'subscriptions', 'comments', 'friends'))) {
	$Cache->increment('stats_links_'.$_GET['stat']);
}
?>
