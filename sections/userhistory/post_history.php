<?
//TODO: replace 24-43 with user_info()
/*
User post history page
*/

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;


$UserID = empty($_GET['userid']) ? $LoggedUser['ID'] : $_GET['userid'];
if(!is_number($UserID)){
	error(0);
}

if (isset($LoggedUser['PostsPerPage'])) {
	$PerPage = $LoggedUser['PostsPerPage'];
} else {
	$PerPage = POSTS_PER_PAGE;
}

list($Page,$Limit) = page_limit($PerPage);

$DB->query("SELECT
	m.Username,
	m.Class,
	m.Enabled,
	m.Title,
	i.Avatar,
	i.Donor,
	i.Warned
	FROM users_main AS m
	JOIN users_info AS i ON i.UserID = m.ID
	WHERE m.ID = $UserID");

if($DB->record_count() == 0){ // If user doesn't exist
	error(404);
}

list($Username, $Class, $Enabled, $Title, $Avatar, $Donor, $Warned) = $DB->next_record();
if(check_perms('site_proxy_images') && !empty($Avatar)) {
	$Avatar = 'http://'.SITE_URL.'/image.php?c=1&i='.urlencode($Avatar);
}

show_header('Post history for '.$Username,'subscriptions');

$ShowUnread = ($UserID == $LoggedUser['ID'] && (!isset($_GET['showunread']) || !!$_GET['showunread']));
$ShowGrouped = ($UserID == $LoggedUser['ID'] && (!isset($_GET['group']) || !!$_GET['group']));
$sql = 'SELECT
	SQL_CALC_FOUND_ROWS';
if($ShowGrouped) {
	$sql.=' * FROM (SELECT';
}
$sql.="
	p.ID,
	p.AddedTime,
	p.Body,
	p.EditedUserID,
	p.EditedTime,
	ed.Username,
	p.TopicID,
	t.Title,
	t.LastPostID,
	CEIL((SELECT COUNT(ID) 
		FROM forums_posts 
		WHERE forums_posts.TopicID = p.TopicID 
		AND forums_posts.ID <= p.ID)/$PerPage) 
		AS Page,
	t.IsLocked,
	t.IsSticky
	FROM forums_posts as p
	LEFT JOIN users_main AS um ON um.ID	 = p.AuthorID
	LEFT JOIN users_info AS ui ON ui.UserID = p.AuthorID
	LEFT JOIN users_main AS ed ON ed.ID	 = p.EditedUserID
	JOIN forums_topics AS t ON t.ID		 = p.TopicID
	JOIN forums AS f ON f.ID				= t.ForumID ";

if($ShowUnread){
	$sql.=' LEFT JOIN forums_last_read_topics AS l ON l.UserID='.$UserID.' AND l.TopicID=t.ID ';
}

$sql .= ' WHERE p.AuthorID = '.$UserID.'
	AND f.MinClassRead<='.$LoggedUser['Class'];

if($ShowUnread){
	$sql.=' AND ((t.IsLocked=\'0\' OR t.IsSticky=\'1\') AND (l.PostID<t.LastPostID OR l.PostID IS NULL)) ';
}

$sql .= ' ORDER BY p.ID DESC';

if($ShowGrouped) {
	$sql.=') AS sub GROUP BY TopicID ORDER BY ID DESC';
}

$sql.=' LIMIT '.$Limit;

$Posts = $DB->query($sql);

$DB->query("SELECT FOUND_ROWS()");
list($Results) = $DB->next_record();

$DB->set_query_id($Posts);

if($UserID == $LoggedUser['ID']){
	$TopicIDs = implode(', ', $DB->collect('TopicID'));
	if($TopicIDs) {
		$DB->query("SELECT 
			l.TopicID, 
			l.PostID ,
			CEIL((SELECT COUNT(ID) FROM forums_posts WHERE forums_posts.TopicID = l.TopicID AND forums_posts.ID<=l.PostID)/$PerPage) AS Page
			FROM forums_last_read_topics AS l
			WHERE TopicID IN($TopicIDs)
			AND UserID='$LoggedUser[ID]'");
		
		$LastRead = $DB->to_array('TopicID');
	}
	$ViewingOwn = true;
}

if(($UserSubscriptions = $Cache->get_value('subscriptions_user_'.$LoggedUser['ID'])) === FALSE) {
	$DB->query("SELECT TopicID FROM users_subscriptions WHERE UserID = '$LoggedUser[ID]'");
	$UserSubscriptions = $DB->collect(0);
	$Cache->cache_value('subscriptions_user_'.$LoggedUser['ID'],$UserSubscriptions,0);
}

$DB->set_query_id($Posts);

?>
<div class="thin">
	<h2 class="center">
<?
	if($ShowGrouped) {
		echo "Grouped ".($ShowUnread?"unread ":"")."post history for <a href=\"user.php?id=$UserID\">$Username</a>";
	}
	elseif($ShowUnread) {
		echo "Unread post history for <a href=\"user.php?id=$UserID\">$Username</a>";
	}
	else {
		echo "Post history for <a href=\"user.php?id=$UserID\">$Username</a>";
	}
?>
	</h2>
	
	<div class="linkbox">
<?
if(isset($ViewingOwn)){
	if(!$ShowUnread){ ?>
		<br /><br />
		<? if($ShowGrouped) { ?>
			<a href="userhistory.php?action=posts&amp;userid=<?=$UserID?>&amp;showunread=0&amp;group=0">Show all posts</a>&nbsp;&nbsp;&nbsp;
		<? } else { ?>
			<a href="userhistory.php?action=posts&amp;userid=<?=$UserID?>&amp;showunread=0&amp;group=1">Show all posts (grouped)</a>&nbsp;&nbsp;&nbsp;
		<? } ?>
		<a href="userhistory.php?action=posts&amp;userid=<?=$UserID?>&amp;showunread=1&amp;group=1">Only display posts with unread replies (grouped)</a>&nbsp;&nbsp;&nbsp;
<?	} else { ?>
		<br /><br />
		<a href="userhistory.php?action=posts&amp;userid=<?=$UserID?>&amp;showunread=0&amp;group=0">Show all posts</a>&nbsp;&nbsp;&nbsp;
<?	
		if(!$ShowGrouped) {
			?><a href="userhistory.php?action=posts&amp;userid=<?=$UserID?>&amp;showunread=1&amp;group=1">Only display posts with unread replies (grouped)</a>&nbsp;&nbsp;&nbsp;<?
		}
		else {
			?><a href="userhistory.php?action=posts&amp;userid=<?=$UserID?>&amp;showunread=1&amp;group=0">Only display posts with unread replies</a>&nbsp;&nbsp;&nbsp;<?
		}
	}
?>
			<a href="userhistory.php?action=subscriptions">Go to subscriptions</a>
<?
}

?>
	</div>
<?
if(empty($Results)) {
?>
	<div class="center">
		No topics<?=$ShowUnread?' with unread posts':''?>
	</div>
<?
} else {
?>
	<div class="linkbox">
<?
	$Pages=get_pages($Page,$Results,$PerPage, 11);
	echo $Pages;
?>
	</div>
<?
	while(list($PostID, $AddedTime, $Body, $EditedUserID, $EditedTime, $EditedUserName, $TopicID, $ThreadTitle, $LastPostID, $Page, $Locked, $Sticky) = $DB->next_record()){
?>
	<table class='forum_post vertical_margin' id='post<?=$PostID ?>'>
		<tr class='colhead_dark'>
			<td  colspan="2">
				<span style="float:left;">
					<?=time_diff($AddedTime) ?>
					in <a href="forums.php?action=viewthread&amp;threadid=<?=$TopicID?>&amp;page=<?=$Page?>#post<?=$PostID?>"><?=cut_string($ThreadTitle, 50)?></a>
<?
		if(isset($ViewingOwn)){
			if ((!$Locked  || $Sticky) && (!$LastRead[$TopicID] || $LastRead[$TopicID]['PostID'] < $LastPostID)) { ?> 
					<span style="color: red;">(New!)</span>
<?			}
			if(!empty($LastRead[$TopicID])) { ?>
					<a href="forums.php?action=viewthread&amp;threadid=<?=$TopicID?>&amp;page=<?=$LastRead[$TopicID]['Page']?>#post<?=$LastRead[$TopicID]['PostID']?>">
					<img src="<?=STATIC_SERVER?>/styles/<?=$LoggedUser['StyleName']?>/images/go_last_read.png" alt="Last read post" title="Go to last read post" />
					</a>
<?			}
		}
?>
				</span>
				<span id="bar<?=$PostID ?>" style="float:right;">
<? 		if(!in_array($TopicID, $UserSubscriptions)) { ?>
					<a href="#" onclick="Subscribe(<?=$TopicID?>);$('.subscribelink<?=$TopicID?>').remove();return false;" class="subscribelink<?=$TopicID?>">[Subscribe]</a>
					&nbsp;
<? 		} ?>
					<a href="#">&uarr;</a>
				</span>
			</td>
		</tr>
<?
		if(!$ShowGrouped) {
?>
		<tr>
			<td class='avatar' valign="top">
<?
				if($Avatar && empty($HeavyInfo['DisableAvatars'])){
?>
				<img src='<?=$Avatar?>' width='150' alt="<?=$Username?>'s avatar" />
<?
				} 
?>
			</td>
			<td class='body' valign="top">
				<?=$Text->full_format($Body) ?> 
<?
				if($EditedUserID){ 
?>
				<br /><br />
				Last edited by
				<?=format_username($EditedUserID, $EditedUserName) ?> <?=time_diff($EditedTime)?>
<?
				}
?>

			</td>
		</tr>
<?
		}
?>
	</table>
<? 	} ?>
	<div class="linkbox">
<?=$Pages?>
	</div>
<? } ?>
</div>
<?

show_footer();

?>