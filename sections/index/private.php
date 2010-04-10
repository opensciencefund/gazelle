<?
include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

show_header('News');
show_message();
?>
<div class="thin">
	<div class="sidebar">
		<div class="box">
			<div class="head colhead_dark"><strong>Stats</strong></div>
			<ul class="stats nobullet">
<? if (USER_LIMIT>0) { ?>
				<li>Maximum Users: <?=number_format(USER_LIMIT) ?></li>

<?
}
if(!$UserCount = $Cache->get_value('stats_user_count')){
	$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1'");
	list($UserCount) = $DB->next_record();
	$Cache->cache_value('stats_user_count', $UserCount, 0); //inf cache
}
$UserCount = (int)$UserCount;
?>
				<li>Enabled Users: <?=number_format($UserCount)?> [<a href="stats.php?action=users">Details</a>]</li>
<?
if (!$UserStats = $Cache->get_value('stats_users')) {
	$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1' AND LastAccess>'".time_minus(3600*24)."'");
	list($UserStats['Day']) = $DB->next_record();

	$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1' AND LastAccess>'".time_minus(3600*24*7)."'");
	list($UserStats['Week']) = $DB->next_record();

	$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1' AND LastAccess>'".time_minus(3600*24*30)."'");
	list($UserStats['Month']) = $DB->next_record();

	$Cache->cache_value('stats_users',$UserStats,0);
}	
?>
				<li>Users active today: <?=number_format($UserStats['Day'])?> (<?=number_format($UserStats['Day']/$UserCount*100,2)?>%)</li>
				<li>Users active this week: <?=number_format($UserStats['Week'])?> (<?=number_format($UserStats['Week']/$UserCount*100,2)?>%)</li>
				<li>Users active this month: <?=number_format($UserStats['Month'])?> (<?=number_format($UserStats['Month']/$UserCount*100,2)?>%)</li>
<?
if(!$TorrentCount = $Cache->get_value('stats_torrent_count')){
	$DB->query("SELECT COUNT(ID) FROM torrents");
	list($TorrentCount) = $DB->next_record();
	$Cache->cache_value('stats_torrent_count', $TorrentCount, 0); //inf cache
}

if(!$AlbumCount = $Cache->get_value('stats_album_count')){
	$DB->query("SELECT COUNT(ID) FROM torrents_group WHERE CategoryID='1'");
	list($AlbumCount) = $DB->next_record();
	$Cache->cache_value('stats_album_count', $AlbumCount, 0); //inf cache
}

if(!$ArtistCount = $Cache->get_value('stats_artist_count')){
	$DB->query("SELECT COUNT(ArtistID) FROM artists_group");
	list($ArtistCount) = $DB->next_record();
	$Cache->cache_value('stats_artist_count', $ArtistCount, 0); //inf cache
}
?>
				<li>Torrents: <?=number_format($TorrentCount)?></li>
				<li>Albums: <?=number_format($AlbumCount)?></li>
				<li>Artists: <?=number_format($ArtistCount)?></li>
<?
//End Torrent Stats

if (!$RequestStats = $Cache->get_value('stats_requests')) {
	$DB->query("SELECT COUNT(ID) FROM requests");
	list($RequestCount) = $DB->next_record();
	$DB->query("SELECT COUNT(ID) FROM requests WHERE FillerID > 0");
	list($FilledCount) = $DB->next_record();
	$Cache->cache_value('stats_requests',array($RequestCount,$FilledCount),11280);
} else { list($RequestCount,$FilledCount) = $RequestStats; }

?>
				<li>Requests: <?=number_format($RequestCount)?> (<?=number_format($FilledCount/$RequestCount*100, 2)?>% filled)</li>
<?

if ($SnatchStats = $Cache->get_value('stats_snatches')) {
?>
				<li>Snatches: <?=number_format($SnatchStats)?></li>
<?
}
$PeerStats = $Cache->get_value('stats_peers');
	if(!$PeerStats && check_perms('admin_clear_cache')) {
		$DB->query("SELECT COUNT(uid) FROM xbt_files_users WHERE remaining>0 AND active>0");
		list($LeecherCount) = $DB->next_record();
		if(!$LeecherCount) { $LeecherCount = 0; }
		$DB->query("SELECT COUNT(uid) FROM xbt_files_users WHERE remaining=0 AND active>0");
		list($SeederCount) = $DB->next_record();
		if(!$SeederCount) { $SeederCount = 0; }
		$Cache->cache_value('stats_peers',array($LeecherCount,$SeederCount),0);
	} else {
		list($LeecherCount,$SeederCount) = $PeerStats;
	}
	$Ratio = ratio($SeederCount, $LeecherCount);
	$PeerCount = $SeederCount + $LeecherCount;
?>
				<li>Peers: <?=number_format($PeerCount) ?></li>
				<li>Seeders: <?=number_format($SeederCount) ?></li>
				<li>Leechers: <?=number_format($LeecherCount) ?></li>
				<li>Seeder/Leecher Ratio: <?=$Ratio?></li>
<?

?>
			</ul>
		</div>
		<div class="box">
			<div class="head colhead_dark"><a href=blog.php>Latest blog posts</a></div>
<?
if (!$Blog = $Cache->get_value('blog')) {
	$DB->query("SELECT
		b.ID,
		um.Username,
		b.Title,
		b.Body,
		b.Time,
		b.ThreadID
		FROM blog AS b LEFT JOIN users_main AS um ON b.UserID=um.ID
		ORDER BY Time DESC
		LIMIT 20");
	$Blog = $DB->to_array();
	$Cache->cache_value('blog',$Blog,1209600);
}
?>
			<ul class="stats nobullet">
<?
if(count($Blog) < 5) {
	$Limit = count($Blog);
} else {
	$Limit = 5;
}
for($i = 0; $i < $Limit; $i++) {
	list($BlogID, $Author, $Title, $Body, $BlogTime, $ThreadID) = $Blog[$i];
?>
				<li>
					<?=($i + 1)?>. <a href="blog.php#blog<?=$BlogID?>"><?=$Title?></a>
				</li>
<? 
}
?>
			</ul>
		</div>
<?
	if (!$TopicID = $Cache->get_value('polls_featured')) {
		$DB->query("SELECT TopicID FROM forums_polls ORDER BY Featured DESC LIMIT 1");
		list($TopicID) = $DB->next_record();
		$Cache->cache_value('polls_featured',$TopicID,0);
	}
	if($TopicID) {
		if (!list($Question,$Answers,$Votes,$Featured,$Closed) = $Cache->get_value('polls_'.$TopicID)) {
			$DB->query("SELECT Question, Answers, Featured, Closed FROM forums_polls WHERE TopicID='".$TopicID."'");
			list($Question, $Answers, $Featured, $Closed) = $DB->next_record(MYSQLI_NUM, array(1));
			$Answers = unserialize($Answers);
			$DB->query("SELECT Vote, COUNT(UserID) FROM forums_polls_votes WHERE TopicID='$TopicID' AND Vote <> '0' GROUP BY Vote");
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
			$Cache->cache_value('polls_'.$TopicID, array($Question,$Answers,$Votes,$Featured,$Closed), 0);
		}
		
		if (!empty($Votes)) {
			$TotalVotes = array_sum($Votes);
			$MaxVotes = max($Votes);
		} else {
			$TotalVotes = 0;
			$MaxVotes = 0;
		}
		
		$DB->query("SELECT Vote FROM forums_polls_votes WHERE UserID='".$LoggedUser['ID']."' AND TopicID='$TopicID'");
		list($UserResponse) = $DB->next_record();
		if (!empty($UserResponse) && $UserResponse != 0) {
			$Answers[$UserResponse] = '&raquo; '.$Answers[$UserResponse];
		}
	
?>
		<div class="box">
			<div class="head colhead_dark"><strong>Poll<? if ($Closed) { echo ' [Closed]'; } ?></strong></div>
			<div class="pad">
				<p><strong><?=display_str($Question)?></strong></p>
<? 		if ($UserResponse !== null || $Closed) { ?>
				<ul class="poll nobullet">
<?			for ($i = 1, $il = count($Answers); $i <= $il; $i++) {
				if ($TotalVotes > 0) {
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
						<span id="center_poll" style="width:<?=round($Ratio*140)?>px;"></span>
						<span id="right_poll"></span>
					</li><br />
<?			} ?>
				</ul>
				<strong>Votes:</strong> <?=number_format($TotalVotes)?><br />
<? 		} else { ?>
				<div id="poll_results">
				<form id="polls">
					<input type="hidden" name="action" value="poll"/>
					<input type="hidden" name="topicid" value="<?=$TopicID?>" />
<? 			for ($i = 1, $il = count($Answers); $i <= $il; $i++) { ?>
					<input type="radio" name="vote" id="answer_<?=$i?>" value="<?=$i?>" />
					<label for="answer_<?=$i?>"><?=display_str($Answers[$i])?></label><br />
<? 			} ?>
					<br /><input type="radio" name="vote" id="answer_0" value="0" /> <label for="answer_0">Blank - Show the results!</label><br /><br />
					<input type="button" onclick="ajax.post('index.php','polls',function(response){$('#poll_results').raw().innerHTML = response});" value="Vote">
				</form>
				</div>
<? 		} ?>
				<br /><strong>Topic:</strong> <a href="forums.php?action=viewthread&amp;threadid=<?=$TopicID?>">Visit</a>
			</div>
		</div>
<?
	}
//polls();
?>
	</div>
	<div class="main_column">
<?

$Recommend = $Cache->get_value('recommend');
$Recommend_artists = $Cache->get_value('recommend_artists');

if (!is_array($Recommend) || !is_array($Recommend_artists)) {
	$DB->query("SELECT
		tr.GroupID,
		tr.UserID,
		u.Username,
		tg.Name
		FROM torrents_recommended AS tr
		JOIN torrents_group AS tg ON tg.ID=tr.GroupID
		LEFT JOIN users_main AS u ON u.ID=tr.UserID
		ORDER BY tr.Time DESC LIMIT 10
		");
	$Recommend = $DB->to_array();
	$Cache->cache_value('recommend',$Recommend,1209600);
	
	$Recommend_artists = get_artists($DB->collect('GroupID'));
	$Cache->cache_value('recommend_artists',$Recommend_artists,1209600);
}

if (count($Recommend) >= 4) {
$Cache->increment('usage_index');
?>
	<script type="text/javascript">
		var runonce = false;
		function log_hit() {
			if (runonce) { return; }
			runonce = true;
			ajax.get('index.php?action=browsers');
		}
	</script>
	<div class="box" id="recommended">
		<div class="head colhead_dark">
			<strong>Latest vanity house additions</strong>
			<a href="#" onclick="$('#vanityhouse').toggle();log_hit();return false;">(View)</a>
		</div>

		<table class="hidden" id="vanityhouse">
<?
	foreach($Recommend as $Recommendations) {
		list($GroupID, $UserID, $Username, $GroupName) = $Recommendations;
?>
			<tr>
				<td><?= display_artists($Recommend_artists[$GroupID], true, false) ?></td>
				<td><a href="torrents.php?id=<?=$GroupID?>"><?=$GroupName?></a> (by <?=format_username($UserID, $Username)?>)</td>
			</tr>
<?	  } ?>
		</table>
	</div>
<!-- END recommendations section -->
<?
}
if (!$News = $Cache->get_value('news')) {
	$DB->query("SELECT
		ID,
		Title,
		Body,
		Time
		FROM news
		ORDER BY Time DESC
		LIMIT 5");
	$News = $DB->to_array();
	$Cache->cache_value('news',$News,1209600);
}

foreach ($News as $NewsItem) {
	list($NewsID,$Title,$Body,$NewsTime) = $NewsItem;
?>
		<div id="<?=$NewsID?>" class="box">
			<div class="head">
				<strong><?=$Title?></strong> - posted <?=time_diff($NewsTime);?>
			</div>
			<div class="pad"><?=$Text->full_format($Body)?></div>
		</div>
<? } ?>
	</div>
</div>
<?
show_footer(array('disclaimer'=>true));

function contest() {
	global $DB, $Cache, $LoggedUser;

	list($Contest, $TotalPoints) = $Cache->get_value('contest');
	if(!$Contest) {
		$DB->query("SELECT 
			UserID,
			SUM(Points),
			Username
			FROM users_points AS up
			JOIN users_main AS um ON um.ID=up.UserID
			GROUP BY UserID 
			ORDER BY SUM(Points) DESC 
			LIMIT 20");
		$Contest = $DB->to_array();
		
		$DB->query("SELECT SUM(Points) FROM users_points");
		list($TotalPoints) = $DB->next_record();
		
		$Cache->cache_value('contest', array($Contest,$TotalPoints), 600);
	}

?>
<!-- Contest Section -->
		<div class="box">
			<div class="head colhead_dark"><strong>Quality time scoreboard</strong></div>
			<div class="pad">
				<ol style="padding-left:5px;">
<?
	foreach ($Contest as $User) {
		list($UserID, $Points, $Username) = $User;
?>
					<li><?=format_username($UserID, $Username)?> (<?=number_format($Points)?>)</li>
<?
	}
?>
				</ol>
				Total uploads: <?=$TotalPoints?><br />
				<a href="index.php?action=scoreboard">Full scoreboard</a>
			</div>
		</div>
	<!-- END contest Section -->
<? } // contest()

?>
