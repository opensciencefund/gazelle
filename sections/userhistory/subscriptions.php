<?
/*
User topic subscription page
*/
//die();
include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;

if (isset($LoggedUser['PostsPerPage'])) {
	$PerPage = $LoggedUser['PostsPerPage'];
} else {
	$PerPage = POSTS_PER_PAGE;
}
list($Page,$Limit) = page_limit($PerPage);

show_header('Subscribed topics','subscriptions');
if(($UserSubscriptions = $Cache->get_value('subscriptions_user_'.$LoggedUser['ID'])) === FALSE) {
	$DB->query('SELECT TopicID FROM users_subscriptions WHERE UserID = '.db_string($LoggedUser['ID']));
	if($UserSubscriptions = $DB->collect(0)) {
		$Cache->cache_value('subscriptions_user_'.$LoggedUser['ID'],$UserSubscriptions,0);
	}
}
$ShowUnread = (!isset($_GET['showunread']) || !!$_GET['showunread']) && (!isset($HeavyInfo['SubscriptionsUnread']) || !!$HeavyInfo['SubscriptionsUnread']);
$ShowCollapsed = (!isset($_GET['collapse']) || !!$_GET['collapse']) && (!isset($HeavyInfo['SubscriptionsCollapse']) || !!$HeavyInfo['SubscriptionsCollapse']);
if(!empty($UserSubscriptions)) {
	$sql = 'SELECT
		SQL_CALC_FOUND_ROWS
		* FROM (SELECT
		f.ID AS ForumID,
		f.Name AS ForumName,
		p.TopicID,
		t.Title,
		p.Body,
		t.LastPostID,
		t.IsLocked,
		t.IsSticky,
		l.PostID,
		IFNULL((SELECT COUNT(ID)
			FROM forums_posts
			WHERE forums_posts.TopicID=p.TopicID
			AND forums_posts.ID<=l.PostID),1)
			AS LastReadNum,
		um.ID,
		um.Username,
		ui.Avatar,
		p.EditedUserID,
		p.EditedTime,
		ed.Username AS EditedUsername
		FROM forums_posts as p
		JOIN forums_topics AS t ON t.ID = p.TopicID
		JOIN forums AS f ON f.ID = t.ForumID
		LEFT JOIN forums_last_read_topics AS l ON l.UserID='.$LoggedUser['ID'].' AND l.TopicID = t.ID
		LEFT JOIN users_main AS um ON um.ID = (SELECT AuthorID FROM forums_posts WHERE ID = l.PostID)
		LEFT JOIN users_info AS ui ON ui.UserID = um.ID
		LEFT JOIN users_main AS ed ON ed.ID = um.ID
		WHERE p.TopicID IN ('.implode(',',$UserSubscriptions).')
		AND f.MinClassRead<='.$LoggedUser['Class'].'
		AND p.ID = IF(l.PostID IS NULL OR l.PostID>t.LastPostID,t.LastPostID,l.PostID)';
	if($ShowUnread) {
		$sql.='
		AND IFNULL(l.PostID,IF(t.IsLocked && t.IsSticky!=0, t.LastPostID, 0))<t.LastPostID';
	}
		$sql.=')
		AS sub GROUP BY TopicID ORDER BY ForumName ASC, TopicID DESC
		LIMIT '.$Limit;
	
	$Posts = $DB->query($sql);
	$DB->query("SELECT FOUND_ROWS()");
	list($NumResults) = $DB->next_record();
	$DB->set_query_id($Posts);
}
?>
<div class="thin">
	<h2 class="center"><?='Subscribed topics'.($ShowUnread?' with unread posts':'')?></h2>
	
	<div class="linkbox">
<?
if(!$ShowUnread) {
?>
			<br /><br />
			<a href="userhistory.php?action=subscriptions&amp;showunread=1">Only display topics with unread replies</a>&nbsp;&nbsp;&nbsp;
<?
} else {
?>
			<br /><br />
			<a href="userhistory.php?action=subscriptions&amp;showunread=0">Show all subscribed topics</a>&nbsp;&nbsp;&nbsp;
<?
}
if($NumResults > 0) {
?>
			<a href="#" onclick="Collapse();return false;" id="collapselink"><?=$ShowCollapsed?'Show':'Hide'?> post bodies</a>&nbsp;&nbsp;&nbsp;
<?
}
?>
			<a href="userhistory.php?action=posts&amp;userid=<?=$LoggedUser['ID']?>">Go to post history</a>&nbsp;&nbsp;&nbsp;
	</div>
<?
if(empty($NumResults)) {
?>
	<div class="center">
		No subscribed topics<?=$ShowUnread?' with unread posts':''?>
	</div>
<?
} else {
?>
	<div class="linkbox">
<?
	$Pages=get_pages($Page,$NumResults,$PerPage, 11);
	echo $Pages;
?>
	</div>
<?
	while(list($ForumID, $ForumName, $TopicID, $ThreadTitle, $Body, $LastPostID, $Locked, $Sticky, $PostID, $LastReadNum, $AuthorID, $AuthorName, $AuthorAvatar, $EditedUserID, $EditedTime, $EditedUsername) = $DB->next_record(MYSQLI_NUM, FALSE)){
?>
	<table class='forum_post box vertical_margin'>
		<tr class='colhead_dark'>
			<td colspan="2">
				<span style="float:left;">
					<a href="forums.php?action=viewforum&amp;forumid=<?=$ForumID?>"><?=$ForumName?></a> &gt;
					<a href="forums.php?action=viewthread&amp;threadid=<?=$TopicID?>"><?=cut_string($ThreadTitle, 50)?></a>
		<? if($PostID<$LastPostID && !$Locked) { ?>
					<span style="color: red;">(New!)</span>
		<? } ?>
					<a href="forums.php?action=viewthread&amp;threadid=<?=$TopicID.($PostID?'&amp;post='.$LastReadNum.'#post'.$PostID:'')?>">
					<img src="<?=STATIC_SERVER?>/styles/<?=$LoggedUser['StyleName']?>/images/go_last_read.png" alt="Last read post" title="Go to last read post" />
					</a>
				</span>
				<span id="bar<?=$PostID ?>" style="float:right;">
					<a href="#" onclick="Subscribe(<?=$TopicID?>);return false;" id="subscribelink<?=$TopicID?>">[Unsubscribe]</a>
					&nbsp;
					<a href="#">&uarr;</a>
				</span>
			</td>
		</tr>
		<tr class="row<?=$ShowCollapsed?' hidden':''?>">
		<? if(empty($HeavyInfo['DisableAvatars'])) { ?>
			<td class='avatar' valign="top">
			<? if(check_perms('site_proxy_images') && preg_match('/^https?:\/\/(localhost(:[0-9]{2,5})?|[0-9]{1,3}(\.[0-9]{1,3}){3}|([a-zA-Z0-9\-\_]+\.)+([a-zA-Z]{1,5}[^\.]))(:[0-9]{2,5})?(\/[^<>]+)+\.(jpg|jpeg|gif|png|tif|tiff|bmp)$/is',$AuthorAvatar)) { ?>
				<img src="<?='http://'.SITE_URL.'/image.php?c=1&i='.urlencode($AuthorAvatar)?>" width="150" style="max-height:400px;" alt="<?=$AuthorName?>'s avatar" />
			<? } elseif(!$AuthorAvatar) { ?>
				<img src="<?=STATIC_SERVER.'common/avatars/default.png'?>" width="150" style="max-height:400px;" alt="Default avatar" />
			<? } else { ?>
				<img src="<?=$AuthorAvatar?>" width="150" style="max-height:400px;" alt="<?=$AuthorName?>'s avatar" />
			<? } ?>
			</td>
		<? } ?>
			<td class='body' valign="top">
				<div class="content3">
					<?=$Text->full_format($Body) ?> 
		<? if($EditedUserID) { ?>
					<br /><br />
					Last edited by
					<?=format_username($EditedUserID, $EditedUsername) ?> <?=time_diff($EditedTime)?>
		<? } ?>
				</div>
			</td>
		</tr>
	</table>
	<? } // while(list(...)) ?>
	<div class="linkbox">
<?=$Pages?>
	</div>
<? } // else -- if(empty($NumResults)) ?>
</div>
<?

show_footer();

?>