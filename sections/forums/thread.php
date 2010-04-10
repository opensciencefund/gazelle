<?
//TODO: Normalize thread_*_info don't need to waste all that ram on things that are already in other caches
/**********|| Page to show individual threads || ********************************\

Things to expect in $_GET:
	ThreadID: ID of the forum curently being browsed
	page:	The page the user's on.
	page = 1 is the same as no page

********************************************************************************/

//Change in poll_vote.php too
define('STAFF_FORUM', 6);

//---------- Things to sort out before it can start printing/generating content

include(SERVER_ROOT.'/classes/class_text.php');

$Text = new TEXT;

// Check for lame SQL injection attempts
if(!isset($_GET['threadid']) || !is_number($_GET['threadid'])) { error(0); }
$ThreadID = $_GET['threadid'];



if (isset($LoggedUser['PostsPerPage'])) {
	$PerPage = $LoggedUser['PostsPerPage'];
} else {
	$PerPage = POSTS_PER_PAGE;
}

//Post links utilize the catalogue & key params to prevent issues with custom posts per page
if (isset($_GET['post']) && is_number($_GET['post'])) {
	$CatalogueID = floor(($_GET['post']-1)/THREAD_CATALOGUE);
	$RequestKey = ($_GET['post']-1)%THREAD_CATALOGUE;
	$Page = ceil((($CatalogueID*THREAD_CATALOGUE)+($RequestKey+1))/$PerPage);
	$CatalogueLimit=$CatalogueID*THREAD_CATALOGUE . ', ' . THREAD_CATALOGUE;
} else {
	list($Page,$Limit) = page_limit(TOPICS_PER_PAGE);
	list($CatalogueID,$CatalogueLimit) = catalogue_limit($Page,$PerPage,THREAD_CATALOGUE);
}


//---------- Get some data to start processing

// Thread information, constant across all pages
if(!$ThreadInfo = $Cache->get_value('thread_'.$ThreadID.'_info')) {
	$DB->query("SELECT
		t.Title,
		t.ForumID,
		t.IsLocked,
		t.IsSticky,
		COUNT(fp.id) AS Posts,
		t.LastPostAuthorID,
		ISNULL(p.TopicID) AS NoPoll
		FROM forums_topics AS t
		JOIN forums_posts AS fp ON fp.TopicID = t.ID
		LEFT JOIN forums_polls AS p ON p.TopicID=t.ID
		WHERE t.ID = '$ThreadID'
		GROUP BY fp.TopicID");
	if($DB->record_count()==0) { error(404); }
	$ThreadInfo = $DB->next_record(MYSQLI_ASSOC);
	if (!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
		$Cache->cache_value('thread_'.$ThreadID.'_info', $ThreadInfo, 0);
	}
}
$ForumID = $ThreadInfo['ForumID'];

// Make sure they're allowed to look at the page
if($Forums[$ForumID]['MinClassRead'] > $LoggedUser['Class']) { error(403); }

// Cache catalogue from which the page is selected, allows block caches and future ability to specify posts per page
if(!$Catalogue = $Cache->get_value('thread_'.$ThreadID.'_catalogue_'.$CatalogueID)) {
	$DB->query("SELECT
		p.ID,
		p.AuthorID,
		p.AddedTime,
		p.Body,
		p.EditedUserID,
		p.EditedTime,
		ed.Username
		FROM forums_posts as p
		LEFT JOIN users_main AS ed ON ed.ID = p.EditedUserID
		WHERE p.TopicID = '$ThreadID'
		LIMIT $CatalogueLimit");
	$Catalogue = $DB->to_array(false,MYSQLI_ASSOC);
	if (!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
		$Cache->cache_value('thread_'.$ThreadID.'_catalogue_'.$CatalogueID, $Catalogue, 0);
	}
}
$Thread = catalogue_select($Catalogue,$Page,$PerPage,THREAD_CATALOGUE);

//Handle last read
if (!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
	$DB->query("SELECT PostID From forums_last_read_topics WHERE UserID='$LoggedUser[ID]' AND TopicID='$ThreadID'");
	list($LastRead) = $DB->next_record();
}

//Handle subscriptions
if(($UserSubscriptions = $Cache->get_value('subscriptions_user_'.$LoggedUser['ID'])) === FALSE) {
	$DB->query("SELECT TopicID FROM users_subscriptions WHERE UserID = '$LoggedUser[ID]'");
	$UserSubscriptions = $DB->collect(0);
	$Cache->cache_value('subscriptions_user_'.$LoggedUser['ID'],$UserSubscriptions,0);
}
if(empty($UserSubscriptions)) {
	$UserSubscriptions = array();
}

// Start printing
show_header('Forums > '.$Forums[$ForumID]['Name'].' > '.$ThreadInfo['Title'],'comments,subscriptions');
show_message();
?>
<div class="thin">
	<h2>
		<a href="forums.php">Forums</a> &gt;
		<a href="forums.php?action=viewforum&amp;forumid=<?=$ThreadInfo['ForumID']?>"><?=$Forums[$ForumID]['Name']?></a> &gt;
		<?=display_str($ThreadInfo['Title'])?>
	</h2>
	<div class="linkbox">
		<div class="center">
<?
if(!$ThreadInfo['IsLocked']) {
?>
			<a href="reports.php?action=report&amp;type=thread&amp;id=<?=$ThreadID?>">[Report Thread]</a>
<? } ?>
			<a href="#" onclick="Subscribe(<?=$ThreadID?>);return false;" id="subscribelink<?=$ThreadID?>"><?=in_array($ThreadID, $UserSubscriptions) ? '[Unsubscribe]' : '[Subscribe]'?></a>
		</div>
<?
$Pages=get_pages($Page,$ThreadInfo['Posts'],$PerPage,9);
echo $Pages;
?>
	</div>
<?
if ($ThreadInfo['NoPoll'] == 0) {
	if (!list($Question,$Answers,$Votes,$Featured,$Closed) = $Cache->get_value('polls_'.$ThreadID)) {
		$DB->query("SELECT Question, Answers, Featured, Closed FROM forums_polls WHERE TopicID='".$ThreadID."'");
		list($Question, $Answers, $Featured, $Closed) = $DB->next_record(MYSQLI_NUM, array(1));
		$Answers = unserialize($Answers);
		$DB->query("SELECT Vote, COUNT(UserID) FROM forums_polls_votes WHERE TopicID='$ThreadID' AND Vote <> '0' GROUP BY Vote");
		$VoteArray = $DB->to_array(false, MYSQLI_NUM);
		
		$Votes = array();
		foreach ($VoteArray as $VoteSet) {
			list($Key,$Value) = $VoteSet; 
			$Votes[$Key] = $Value;
		}
		
		for ($i = 1, $il = count($Answers); $i <= $il; ++$i) {
			if (!isset($Votes[$i])) {
				$Votes[$i] = 0;
			}
		}
		$Cache->cache_value('polls_'.$ThreadID, array($Question,$Answers,$Votes,$Featured,$Closed), 0);
	}
	
	if (!empty($Votes)) {
		$TotalVotes = array_sum($Votes);
		$MaxVotes = max($Votes);
	} else {
		$TotalVotes = 0;
		$MaxVotes = 0;
	}
	
	//Polls lose the you voted arrow thingy
	$DB->query("SELECT Vote FROM forums_polls_votes WHERE UserID='".$LoggedUser['ID']."' AND TopicID='$ThreadID'");
	list($UserResponse) = $DB->next_record();
	if (!empty($UserResponse) && $UserResponse != 0) {
		$Answers[$UserResponse] = '&raquo; '.$Answers[$UserResponse];
	}
?>
	<div class="box thin clear">
		<div class="head colhead_dark"><strong>Poll<? if ($Closed) { echo ' [Closed]'; } ?><? if ($Featured && $Featured !== '0000-00-00 00:00:00') { echo ' [Featured]'; } ?></strong> <a href="#" onclick="$('#threadpoll').toggle();log_hit();return false;">(View)</a></div>
		<div class="pad<? if (/*$LastRead !== null || */$ThreadInfo['IsLocked']) { echo ' hidden'; } ?>" id="threadpoll">
			<p><strong><?=display_str($Question)?></strong></p>
<?	if ($UserResponse !== null || $Closed) { ?>
			<ul class="poll nobullet">
<?		
		if($ForumID != STAFF_FORUM) {
			for ($i = 1, $il = count($Answers); $i <= $il; $i++) {
				if (!empty($Votes[$i]) && $TotalVotes > 0) {
					$Ratio = $Votes[$i]/$MaxVotes;
					$Percent = $Votes[$i]/$TotalVotes;
				} else {
					$Ratio=0;
					$Percent=0;
				} 
?>
					<li><?=display_str($Answers[$i])?> (<?=number_format($Percent*100,2)?>%)</li>
					<li class="graph">
						<span id="left_poll"></span>
						<span id="center_poll" style="width:<?=round($Ratio*750)?>px;"></span>
						<span id="right_poll"></span>
					</li>
<?			}
		} else {
			//Staff forum, output voters, not percentages
			$DB->query("SELECT GROUP_CONCAT(um.Username SEPARATOR ', '), 
							fpv.Vote 
						FROM users_main AS um 
							JOIN forums_polls_votes AS fpv ON um.ID = fpv.UserID
						WHERE TopicID = ".$ThreadID."
						GROUP BY fpv.Vote");
			
			$StaffVotes = $DB->to_array();
			foreach($StaffVotes as $StaffVote) {
				list($StaffString, $StaffVoted) = $StaffVote;
?>
				<li><?=display_str($Answers[$StaffVoted])?> - <?=$StaffString?></li>
<?
			}
		}
?>
			</ul>
			<br />
			<strong>Votes:</strong> <?=number_format($TotalVotes)?><br /><br />
<? } else { 
	//User has not voted
?>
			<div id="poll_results">
			<form id="polls">
				<input type="hidden" name="action" value="poll"/>
				<input type="hidden" name="large" value="1"/>
				<input type="hidden" name="topicid" value="<?=$ThreadID?>" />
<? for ($i = 1, $il = count($Answers); $i <= $il; $i++) { ?>
				<input type="radio" name="vote" id="answer_<?=$i?>" value="<?=$i?>" />
				<label for="answer_<?=$i?>"><?=display_str($Answers[$i])?></label><br />
<? } ?>
				<br /><input type="radio" name="vote" id="answer_0" value="0" /> <label for="answer_0">Blank - Show the results!</label><br /><br />
				<input type="button" style="float: left;" onclick="ajax.post('index.php','polls',function(response){$('#poll_results').raw().innerHTML = response});" value="Vote">
			</form>
			</div>
<? } ?>
<? if(check_perms('forums_polls_moderate') && $ForumID != STAFF_FORUM) { ?>
	<? if (!$Featured || $Featured == '0000-00-00 00:00:00') { ?>
			<form action="forums.php" method="post">
				<input type="hidden" name="action" value="poll_mod"/>
				<input type="hidden" name="topicid" value="<?=$ThreadID?>" />
				<input type="hidden" name="feature" value="1">
				<input type="submit" style="float: left;" onclick="return confirm('Are you sure you want to feature this poll?');"; value="Feature">
			</form>
	<? } ?>
			<form action="forums.php" method="post">
				<input type="hidden" name="action" value="poll_mod"/>
				<input type="hidden" name="topicid" value="<?=$ThreadID?>" />
				<input type="hidden" name="close" value="1">
				<input type="submit" style="float: left;" value="<?=!$Closed?'Close':'Open'?>">
			</form>
<? } ?>
		</div>
	</div>
<? 
} //End Polls


$LastPost = 0;
foreach($Thread as $Key => $Post){
	list($PostID, $AuthorID, $AddedTime, $Body, $EditedUserID, $EditedTime, $EditedUsername) = array_values($Post);
	list($AuthorID, $Username, $PermissionID, $Artist, $Donor, $Warned, $Avatar, $Enabled, $UserTitle) = array_values(user_info($AuthorID));
	$LastPost = $PostID;
?>
<table class="forum_post box vertical_margin<? if (((!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) && $PostID>$LastRead && strtotime($AddedTime)>$LoggedUser['CatchupTime']) || (isset($RequestKey) && $Key==$RequestKey)) { echo ' forum_unread'; } ?>" id="post<?=$PostID?>">
	<tr class="colhead_dark">
		<td colspan="2">
			<span style="float:left;"><a href='forums.php?action=viewthread&amp;threadid=<?=$ThreadID?>&amp;post=<?=($CatalogueID*THREAD_CATALOGUE)+($Key+1)?>#post<?=$PostID?>'>#<?=$PostID?></a>
				by <strong><?=format_username($AuthorID, $Username, $Donor, $Warned, $Enabled == 2 ? false : true, $PermissionID)?></strong> <?=!empty($UserTitle) ? '('.$UserTitle.')' : '' ?>
			<?=time_diff($AddedTime,2)?>
<? if (!$ThreadInfo['IsLocked'] || check_perms('site_moderate_forums')){ ?>				- <a href="#quickpost" onclick="Quote('<?=$PostID?>','<?=$Username?>');">[Quote]</a><? }
if (((!$ThreadInfo['IsLocked'] && $LoggedUser['Class'] >= $Forums[$ForumID]['MinClassWrite']) && ($AuthorID == $LoggedUser['ID']) || check_perms('site_moderate_forums'))) { ?>				- <a href="#post<?=$PostID?>" onclick="Edit_Form('<?=$PostID?>','<?=$Key?>');">[Edit]</a><? }
if (check_perms('site_moderate_forums')){ ?>				- <a href="#post<?=$PostID?>" onclick="Delete('<?=$PostID?>');">[Delete]</a> <? } ?>


			</span>
			<span id="bar<?=$PostID?>" style="float:right;">
				<a href="reports.php?action=report&amp;type=post&amp;id=<?=$PostID?>">[Report Post]</a>
				&nbsp;
				<a href="#">&uarr;</a>
			</span>
		</td>
	</tr>
	<tr>
<? if(empty($HeavyInfo['DisableAvatars'])) { ?>
		<td class="avatar" valign="top">
	<? if ($Avatar) { ?>
			<img src="<?=$Avatar?>" width="150" alt="<?=$Username ?>'s avatar" />
	<? } else { ?>
			<img src="<?=STATIC_SERVER?>common/avatars/default.png" width="150" alt="Default avatar" />
	<? } ?>
	</td>
<? } ?>
		<td class="body" valign="top"<? if(!empty($HeavyInfo['DisableAvatars'])) { echo ' colspan="2"'; } ?>>
			<div id="content<?=$PostID?>">
<?=$Text->full_format($Body)?>
<? if($EditedUserID){ ?>
				<br /><br />Last edited by
				<?=format_username($EditedUserID, $EditedUsername) ?> <?=strtolower(time_diff($EditedTime))?>
<? } ?>
			</div>
		</td>
	</tr>
</table>
<?	} ?>
<div class="linkbox">
	<?=$Pages?>
</div>
<?
if((!$ThreadInfo['IsLocked']  || $ThreadInfo['IsSticky']) && $LastRead<$LastPost){
	$DB->query("INSERT INTO forums_last_read_topics
		(UserID, TopicID, PostID) VALUES
		('$LoggedUser[ID]', '".$ThreadID ."', '".db_string($LastPost)."')
		ON DUPLICATE KEY UPDATE PostID='$LastPost'");
}

if(!$ThreadInfo['IsLocked'] || check_perms('site_moderate_forums')) {
	if($Forums[$ForumID]['MinClassWrite'] <= $LoggedUser['Class'] && !$LoggedUser['DisablePosting']) {
	//TODO: Preview, come up with a standard, make it look like post or just a block of formatted bbcode, but decide and write some proper html
?>
			<br />
			<h3>Post reply</h3>
			<div class="box pad">
				<table id="quickreplypreview" class="forum_post box vertical_margin hidden" id="postpreview" style="text-align:left;">
					<tr class="colhead_dark">
						<td colspan="2">
							<span style="float:left;"><a href='#postpreview'>#XXXXXX</a>
								by <strong><?=format_username($LoggedUser['ID'], $LoggedUser['Username'], $LoggedUser['Donor'], $LoggedUser['Warned'], $LoggedUser['Enabled'] == 2 ? false : true, $LoggedUser['PermissionID'])?></strong> <? if (!empty($LoggedUser['Title'])) { echo '('.$LoggedUser['Title'].')'; }?>
							Just now
							</span>
							<span id="barpreview" style="float:right;">
								<a href="#postpreview">[Report Post]</a>
								&nbsp;
								<a href="#">&uarr;</a>
							</span>
						</td>
					</tr>
					<tr>
						<td class="avatar" valign="top">
				<? if (!empty($LoggedUser['Avatar'])) { ?>
							<img src="<?=$LoggedUser['Avatar']?>" width="150" alt="<?=$LoggedUser['Username']?>'s avatar" />
				<? } else { ?>
							<img src="<?=STATIC_SERVER?>common/avatars/default.png" width="150" alt="Default avatar" />
				<? } ?>
						</td>
						<td class="body" valign="top">
							<div id="contentpreview" style="text-align:left;"></div>
						</td>
					</tr>
				</table>
				<form id="quickpostform" action="" method="post" style="display: block; text-align: center;">
					<div id="quickreplytext">
						<input type="hidden" name="action" value="reply" />
						<input type="hidden" name="thread" value="<?=$ThreadID?>" />
						<textarea id="quickpost" style="width: 95%;" onkeyup="resize('quickpost');" name="body" cols="90" rows="8"></textarea> <br />
					</div>
					<div id="quickreplybuttons">
<? if(!in_array($ThreadID, $UserSubscriptions)) { ?>
						<input id="subscribebox" type="checkbox" name="subscribe"<?=!empty($HeavyInfo['AutoSubscribe'])?' checked="checked"':''?> />
						<label for="subscribebox">Subscribe to topic</label>
<?
}
	if($ThreadInfo['LastPostAuthorID']==$LoggedUser['ID'] && check_perms('site_forums_double_post')) {
?>
						<input id="mergebox" type="checkbox" name="merge" checked="checked" />
						<label for="mergebox">Merge</label>
<? } ?>
						<div id="quickreplybuttonstoggle" style="display:inline;">
							<input type="button" value="Preview" onclick="Quick_Preview();" />
							<input type="submit" value="Submit reply" />
						</div>
					</div>
				</form>
			</div>
<?
	}
}

if(check_perms('site_moderate_forums')) {
?>
	<br />
	<h3>Edit thread</h3>
	<form action="forums.php" method="post">
		<div>
		<input type="hidden" name="action" value="mod_thread" />
		<input type="hidden" name="threadid" value="<?=$ThreadID?>" />
		<input type="hidden" name="page" value="<?=$Page?>" />
		</div>
		<table cellpadding="6" cellspacing="1" border="0" width="100%" class="border">
			<tr>
				<td class="label">Sticky</td>
				<td>
					<input type="checkbox" name="sticky"<? if($ThreadInfo['IsSticky']) { echo ' checked="checked"'; } ?> />
				</td>
			</tr>
			<tr>
				<td class="label">Locked</td>
				<td>
					<input type="checkbox" name="locked"<? if($ThreadInfo['IsLocked']) { echo ' checked="checked"'; } ?> />
				</td>
			</tr>
			<tr>
				<td class="label">Title</td>
				<td>
					<input type="text" name="title" size="50" value="<?=display_str($ThreadInfo['Title'])?>" />
				</td>
			</tr>
			<tr>
				<td class="label">Move thread</td>
				<td>
					<select name="forumid">
<? 
$OpenGroup = false;
$LastCategoryID=-1;

foreach ($Forums as $Forum) {
	if ($Forum['MinClassRead'] > $LoggedUser['Class']) {
		continue;
	}

	if ($Forum['CategoryID'] != $LastCategoryID) {
		$LastCategoryID = $Forum['CategoryID'];
		if($OpenGroup) { ?>
					</optgroup>
<?		} ?>
					<optgroup label="<?=$ForumCats[$Forum['CategoryID']]?>">
<?		$OpenGroup = true;
	}
?>
						<option value="<?=$Forum['ID']?>"<? if($ThreadInfo['ForumID'] == $Forum['ID']) { echo ' selected="selected"';} ?>><?=$Forum['Name']?></option>
<? } ?>
					</optgroup>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">Delete thread</td>
				<td>
					<input type="checkbox" name="delete" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="center">
					<input type="submit" value="Edit thread" />
				</td>
			</tr>

		</table>
	</form>
<?
} // If user is moderator
?>
</div>
<? show_footer();
