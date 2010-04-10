<?
$Rippy = false;
define('FOOTER_FILE', SERVER_ROOT.'/design/privatefooter.php');
$HTTPS = ($_SERVER['SERVER_PORT'] == 443) ? 'ssl_' : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title><?=display_str($PageTitle)?></title>
	<meta http-equiv="X-UA-Compatible" content="chrome=1;IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="favicon.ico" />
<??>
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=feed_news&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - News" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=feed_blog&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Blog" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_notify_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - P.T.N." />
<? if(isset($LoggedUser['Notify'])) {
	foreach($LoggedUser['Notify'] as $Filter) {
		list($FilterID, $FilterName) = $Filter;
?>
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_notify_<?=$FilterID?>_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>&amp;name=<?=urlencode($FilterName)?>" title="<?=SITE_NAME?> - <?=display_str($FilterName)?>" />
<? 	}
}?>
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_all&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - All Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_music&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Music Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_apps&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Application Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_ebooks&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - E-Book Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_abooks&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Audiobooks Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_evids&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - E-Learning Video Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_comedy&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Comedy Torrents" />
		<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_comics&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Comic Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_mp3&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - MP3 Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_flac&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - FLAC Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_vinyl&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Vinyl Sourced Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_lossless&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Lossless Torrents" />
	<link rel="alternate" type="application/rss+xml" href="/feeds.php?feed=torrents_lossless24&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - 24bit Lossless Torrents" />
<? if (empty($LoggedUser['StyleURL'])) { ?>
	<link href="<?=STATIC_SERVER?>styles/<?=$LoggedUser['StyleName'] ?>/style.css" title="<?=$LoggedUser['StyleName']?>" rel="stylesheet" type="text/css" media="screen" />
<? } else { ?>
	<link href="<?=$LoggedUser['StyleURL']?>" title="External CSS" rel="stylesheet" type="text/css" media="screen" />
<? } ?>
	<link href="<?=STATIC_SERVER?>styles/global.css?v=<?=filemtime(SERVER_ROOT.'/static/styles/global.css')?>" rel="stylesheet" type="text/css" />
	<script src="<?=STATIC_SERVER?>functions/sizzle.js" type="text/javascript"></script>
	<script src="<?=STATIC_SERVER?>functions/script_start.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/script_start.js')?>" type="text/javascript"></script>
	<script src="<?=STATIC_SERVER?>functions/class_ajax.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/class_ajax.js')?>" type="text/javascript" async="async"></script>
	<script type="text/javascript">
		var authkey = "<?=$LoggedUser['AuthKey']?>";
		var userid = <?=$LoggedUser['ID']?>;
	</script>
	<script src="<?=STATIC_SERVER?>functions/global.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/global.js')?>" type="text/javascript"></script>
<? if($Rippy) { ?>
 	<script src="<?=STATIC_SERVER?>functions/rippy.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/rippy.js')?>" type="text/javascript" defer="defer"></script>
<? }
//</strip>
if(!empty($_SESSION['checksuper'])) {
?>
	<script src="<?=STATIC_SERVER?>functions/class_storage.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/class_storage.js')?>" type="text/javascript" async="async"></script>
	<script type="text/javascript">
		var oldid = local.get('phpsession_id');
		if(oldid!=null && oldid!=userid) {
			ajax.get("test.php?action=super&userid="+userid+"&oldid="+oldid, function(){return;});
			local.del('phpsession_id');
		}
	</script>
<?
	//unset($_SESSION['checksuper']);
}

//</strip>
$Scripts=explode(',',$JSIncludes);
foreach ($Scripts as $Script) {
if (empty($Script)) { continue; }
?>
	<script src="<?=STATIC_SERVER?>functions/<?=$Script?>.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/'.$Script.'.js')?>" type="text/javascript"></script>
<? } ?>
</head>
<body id="<?=$Document?>" <?= (($Rippy) ? 'onload="say()"' : '') ?>>
<div id="wrapper">
<h1 class="hidden"><?=SITE_NAME?></h1>

<div id="header">
	<div id="logo"><a href="index.php"></a></div>
	<div id="userinfo">
		<ul id="userinfo_username">
			<li><a href="user.php?id=<?=$LoggedUser['ID']?>" class="username"><?=$LoggedUser['Username']?></a></li>
			<li class="brackets"><a href="user.php?action=edit&amp;userid=<?=$LoggedUser['ID']?>">Edit</a></li>
			<li class="brackets"><a href="logout.php?auth=<?=$LoggedUser['AuthKey']?>">Logout</a></li>
		</ul>
		<ul id="userinfo_major">
			<li class="brackets"><strong><a href="upload.php">Upload</a></strong></li>
<?
if(check_perms('site_send_unlimited_invites')) {
	$Invites = ' (∞)';
} elseif ($LoggedUser['Invites']>0) {
	$Invites = ' ('.$LoggedUser['Invites'].')';
} else {
	$Invites = '';
}
?>
			<li class="brackets"><strong><a href="user.php?action=invite">Invite<?=$Invites?></a></strong></li>
			<li class="brackets"><strong><a href="donate.php">Donate</a></strong></li>
		</ul>
		<ul id="userinfo_stats">
			<li id="stats_seeding"><a href="torrents.php?type=seeding&amp;userid=<?=$LoggedUser['ID']?>">Up</a>: <span class="stat"><?=get_size($LoggedUser['BytesUploaded'])?></span></li>
			<li id="stats_leeching"><a href="torrents.php?type=leeching&amp;userid=<?=$LoggedUser['ID']?>">Down</a>: <span class="stat"><?=get_size($LoggedUser['BytesDownloaded'])?></span></li>
			<li id="stats_ratio">Ratio: <span class="stat"><?=ratio($LoggedUser['BytesUploaded'], $LoggedUser['BytesDownloaded'])?></span></li>
<?	if(!empty($LoggedUser['RequiredRatio'])) {?>
			<li id="stats_required"><a href="rules.php?p=ratio">Required</a>: <span class="stat"><?=number_format($LoggedUser['RequiredRatio'], 2)?></span></li>
<?	} ?>
		</ul>
		<ul id="userinfo_minor">
			<li><a onmousedown="Stats('inbox');" href="inbox.php">Inbox</a></li>
			<li><a onmousedown="Stats('uploads');" href="torrents.php?type=uploaded&amp;userid=<?=$LoggedUser['ID']?>">Uploads</a></li>
			<li><a onmousedown="Stats('bookmarks');" href="bookmarks.php">Bookmarks</a></li>
<? if (check_perms('site_torrents_notify')) { ?>
			<li><a onmousedown="Stats('notifications');" href="user.php?action=notify">Notifications</a></li>
<? } ?>
<!--			<li><a href="userhistory.php?action=posts&amp;userid=<?=$LoggedUser['ID']?>">Posts</a></li>-->
			<li><a onmousedown="Stats('subscriptions');" href="userhistory.php?action=subscriptions">Subscriptions</a></li>
			<li><a onmousedown="Stats('comments');" href="comments.php">Comments</a></li>
			<li><a onmousedown="Stats('friends');" href="friends.php">Friends</a></li>
		</ul>
	</div>
	<div id="menu">
		<h4 class="hidden">Site Menu</h4>
		<ul>
			<li id="nav_index"><a href="index.php">Home</a></li>
			<li id="nav_torrents"><a href="torrents.php">Torrents</a></li>
			<li id="nav_collages"><a href="collages.php">Collages</a></li>
			<li id="nav_requests"><a href="requests.php">Requests</a></li>
			<li id="nav_forums"><a href="forums.php">Forums</a></li>
			<li id="nav_irc"><a href="chat.php">IRC</a></li>
			<li id="nav_top10"><a href="top10.php">Top&nbsp;10</a></li>
			<li id="nav_rules"><a href="rules.php">Rules</a></li>
			<li id="nav_wiki"><a href="wiki.php">Wiki</a></li>
			<li id="nav_staff"><a href="staff.php">Staff</a></li>
		</ul>
	</div>
<?
//Start handling alert bars
$Alerts = array();
$ModBar = array();

$NewMessages = $Cache->get_value('inbox_new_'.$LoggedUser['ID']);
if ($NewMessages === false) {
	$DB->query("SELECT COUNT(UnRead) FROM pm_conversations_users WHERE UserID='".$LoggedUser['ID']."' AND UnRead = '1'");
	list($NewMessages) = $DB->next_record();
	$Cache->cache_value('inbox_new_'.$LoggedUser['ID'], $NewMessages, 0);
}

if ($NewMessages > 0) {
	$Alerts[] = '<a href="inbox.php">You have '.$NewMessages.' new message'.(($NewMessages > 1)? 's' : '').'</a>';
}

if($LoggedUser['RatioWatch']){
	$Alerts[] = '<a href="rules.php?p=ratio">Ratio Watch</a>: You have '.time_diff($LoggedUser['RatioWatchEnds'], 3).' to get your ratio over your required ratio.';
}

if (check_perms('site_torrents_notify')) {
	$NewNotifications = $Cache->get_value('notifications_new_'.$LoggedUser['ID']);
	if ($NewNotifications === false) {
		$DB->query("SELECT COUNT(UserID) FROM users_notify_torrents WHERE UserID='$LoggedUser[ID]' AND UnRead='1'");
		list($NewNotifications) = $DB->next_record();
		/* if($NewNotifications && !check_perms('site_torrents_notify')) {
			$DB->query("DELETE FROM users_notify_torrents WHERE UserID='$LoggedUser[ID]'");
			$DB->query("DELETE FROM users_notify_filters WHERE UserID='$LoggedUser[ID]'");
		} */
		$Cache->cache_value('notifications_new_'.$LoggedUser['ID'], $NewNotifications, 0);
	}
	if ($NewNotifications > 0) {
		$Alerts[] = '<a href="torrents.php?action=notify">You have '.$NewNotifications.' new torrent notification'.(($NewNotifications > 1)? 's' : '').'</a>';
	}
}

if (check_perms('users_mod')) {
	$ModBar[] = '<a href="tools.php">Toolbox</a>';
}

if(check_perms('admin_reports')) {
	$NumTorrentReports = $Cache->get_value('num_torrent_reportsv2');
	if ($NumTorrentReports === false) {
		$DB->query("SELECT COUNT(ID) FROM reportsv2 WHERE Status='New'");
		list($NumTorrentReports) = $DB->next_record();
		$Cache->cache_value('num_torrent_reportsv2', $NumTorrentReports, 0);
	}
	
	//if ($NumTorrentReports > 0) {
		$ModBar[] = '<a href="reportsv2.php">'.$NumTorrentReports.' Report'.(($NumTorrentReports > 1)? 's' : '').'</a>';
	//}
}

if(check_perms('admin_reports')) {
	$NumOtherReports = $Cache->get_value('num_other_reports');
	if ($NumOtherReports === false) {
		$DB->query("SELECT COUNT(ID) FROM reports WHERE Status='New'");
		list($NumOtherReports) = $DB->next_record();
		$Cache->cache_value('num_other_reports', $NumOtherReports, 0);
	}
	
	if ($NumOtherReports > 0) {
		$ModBar[] = '<a href="reports.php">'.$NumOtherReports.' Other Report'.(($NumOtherReports > 1)? 's' : '').'</a>';
	}
}	

if (!empty($Alerts) || !empty($ModBar)) {
?>
	<div id="alerts">
	<? foreach ($Alerts as $Alert) { ?>
		<div class="alertbar"><?=$Alert?></div>
	<? }
	if (!empty($ModBar)) { ?>
		<div class="alertbar blend"><?=implode(' | ',$ModBar)?></div>
	<? } ?>
	</div>
<?
}
//Done handling alertbars

if($Rippy) { ?>
	<div class="rippy">
		<div id="bubble" style="display: none;">
			<span class="rbt"></span>
			<span id="rippy-says" class="rbm"></span>
			<span class="rbb"></span>
		</div>
	</div>
<? } ?>

	<div id="searchbars">
		<ul>
			<li>
				<span class="hidden">Torrents: </span>
				<form action="torrents.php" method="get">
<? if(isset($LoggedUser['SearchType']) && $LoggedUser['SearchType']) { // Advanced search ?> 
					<input type="hidden" name="action" value="advanced" />
<? } ?>
					<input
						accesskey="t"
						onfocus="if (this.value == 'Torrents') this.value='';"
						onblur="if (this.value == '') this.value='Torrents';"
<? if(isset($LoggedUser['SearchType']) && $LoggedUser['SearchType']) { // Advanced search ?> 
						value="Torrents" type="text" name="groupname" size="17"
<? } else { ?>
						value="Torrents" type="text" name="searchstr" size="17"
<? } ?>
					/>
				</form>
			</li>
			<li>
				<span class="hidden">Artist: </span>
				<form action="artist.php" method="get">
					<input 
						accesskey="a" 
						id="artistsearch" 
						onfocus="if (this.value == 'Artist') this.value='';"
						onblur="if (this.value == '') this.value='Artist';"
						value="Artist" type="text" name="artistname" size="17"
					/>
				</form>
			</li>
			<li>
				<span class="hidden">Requests: </span>
				<form action="requests.php" method="get">
					<input
						onfocus="if (this.value == 'Requests') this.value='';"
						onblur="if (this.value == '') this.value='Requests';"
						value="Requests" type="text" name="search" size="17"
					/>
				</form>
			</li>
			<li>
				<span class="hidden">Forums: </span>
				<form action="forums.php" method="get">
					<input value="search" type="hidden" name="action" />
					<input
						onfocus="if (this.value == 'Forums') this.value='';"
						onblur="if (this.value == '') this.value='Forums';"
						value="Forums" type="text" name="search" size="17"
					/>
				</form>
			</li>
<!--
			<li>
				<span class="hidden">Wiki: </span>
				<form action="wiki.php" method="get">
					<input type="hidden" name="action" value="search">
					<input 
						onfocus="if (this.value == 'Wiki') this.value='';"
						onblur="if (this.value == '') this.value='Wiki';"
						value="Wiki" type="text" name="search" size="17"
					/>
				</form>
			</li>
-->
			<li>
				<span class="hidden">Log: </span>
				<form action="log.php" method="get">
					<input
						onfocus="if (this.value == 'Log') this.value='';"
						onblur="if (this.value == '') this.value='Log';"
						value="Log" type="text" name="search" size="17"
					/>
				</form>
			</li>
			<li>
				<span class="hidden">Users: </span>
				<form action="user.php" method="get">
					<input type="hidden" name="action" value="search" />
					<input
						onfocus="if (this.value == 'Users') this.value='';"
						onblur="if (this.value == '') this.value='Users';"
						value="Users" type="text" name="search" size="20"
					/>
				</form>
			</li>
		</ul>
	</div>

</div>
<div id="content">
