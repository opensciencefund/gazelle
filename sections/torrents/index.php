<?

//Function used for pagination of peer/snatch/download lists on details.php
function js_pages($Action, $TorrentID, $NumResults, $CurrentPage) {
	$NumPages = ceil($NumResults/100);
	$PageLinks = array();
	for($i = 1; $i<=$NumPages; $i++) {
		if($i == $CurrentPage) {
			$PageLinks[]=$i;
		} else {
			$PageLinks[]='<a href="#" onclick="'.$Action.'('.$TorrentID.', '.$i.')">'.$i.'</a>';
		}
	}
	return implode(' | ',$PageLinks);
}

if(!empty($_REQUEST['action'])) {
	switch($_REQUEST['action']){
		case 'edit':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/edit.php');
			break;
		
		case 'editgroup':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/editgroup.php');
			break;
		
		case 'editgroupid':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/editgroupid.php');
			break;
		
		case 'takeedit':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/takeedit.php');
			break;
		
		case 'newgroup':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/takenewgroup.php');
			break;
		
		case 'peerlist':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/peerlist.php');
			break;
		
		case 'snatchlist':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/snatchlist.php');
			break;
	
		case 'downloadlist':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/downloadlist.php');
			break;
	
		case 'redownload':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/redownload.php');
			break;
	
		case 'revert':
		case 'takegroupedit':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/takegroupedit.php');
			break;
		
		case 'nonwikiedit':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/nonwikiedit.php');
			break;
		
		case 'rename':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/rename.php');
			break;
		
		case 'merge':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/merge.php');
			break;
			
		case 'add_alias':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/add_alias.php');
			break;
			
		case 'delete_alias':
			enforce_login();
			authorize();
			include(SERVER_ROOT.'/sections/torrents/delete_alias.php');
			break;
			
			
		case 'history':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/history.php');
			break;
		
		case 'delete':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/delete.php');
			break;
		
		case 'takedelete':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/takedelete.php');
			break;
	
		case 'masspm':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/masspm.php');
			break;
	
		case 'takemasspm':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/takemasspm.php');
			break;
	 	
		case 'vote_tag':
			enforce_login();
			authorize();
			include(SERVER_ROOT.'/sections/torrents/vote_tag.php');
			break;
		
		case 'add_tag':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/add_tag.php');
			break;
		
		case 'delete_tag':
			enforce_login();
			authorize();
			include(SERVER_ROOT.'/sections/torrents/delete_tag.php');
			break;
	
		case 'notify':
			enforce_login();
			include(SERVER_ROOT.'/sections/torrents/notify.php');
			break;

		case 'notify_clear':
			enforce_login();
			authorize();
			if(!check_perms('site_torrents_notify')) { 
			 	$DB->query("DELETE FROM users_notify_filters WHERE UserID='$LoggedUser[ID]'");
			}
			$DB->query("DELETE FROM users_notify_torrents WHERE UserID='$LoggedUser[ID]'");
			$Cache->delete_value('notifications_new_'.$LoggedUser['ID']);
			header('Location: '.$_SERVER['HTTP_REFERER']);
			break;

		case 'notify_cleargroup':
			enforce_login();
			authorize();
			if(!isset($_GET['filterid']) || !is_number($_GET['filterid'])) {
				error(0);
			}
			if(!check_perms('site_torrents_notify')) { 
			 	$DB->query("DELETE FROM users_notify_filters WHERE UserID='$LoggedUser[ID]'");
			}
			$DB->query("DELETE FROM users_notify_torrents WHERE UserID='$LoggedUser[ID]' AND FilterID='$_GET[filterid]'");
			$Cache->delete_value('notifications_new_'.$LoggedUser['ID']);
			header('Location: '.$_SERVER['HTTP_REFERER']);
			break;
		
		case 'notify_clearitem':
			enforce_login();
			authorize();
			if(!isset($_GET['torrentid']) || !is_number($_GET['torrentid'])) {
				error(0);
			}
			if(!check_perms('site_torrents_notify')) { 
			 	$DB->query("DELETE FROM users_notify_filters WHERE UserID='$LoggedUser[ID]'");
			}
			$DB->query("DELETE FROM users_notify_torrents WHERE UserID='$LoggedUser[ID]' AND TorrentID='$_GET[torrentid]'");
			$Cache->delete_value('notifications_new_'.$LoggedUser['ID']);
			break;
			
		case 'download':
			if (!isset($_REQUEST['authkey']) || !isset($_REQUEST['torrent_pass'])) {
				enforce_login();
				$TorrentPass = $LoggedUser['torrent_pass'];
				$DownloadAlt = $LoggedUser['DownloadAlt'];
			} else {
				$UserInfo = $Cache->get_value('user_'.$_REQUEST['torrent_pass']);
				if(!is_array($UserInfo)) {
					$DB->query("SELECT 
						ID,
						DownloadAlt
						FROM users_main AS m 
						INNER JOIN users_info AS i ON i.UserID=m.ID 
						WHERE m.torrent_pass='".db_string($_REQUEST['torrent_pass'])."' 
						AND m.Enabled='1'");
					$UserInfo = $DB->next_record();
					$Cache->cache_value('user_'.$_REQUEST['torrent_pass'], $UserInfo, 0);
				}
				$UserInfo = array($UserInfo);
				list($UserID,$DownloadAlt)=array_shift($UserInfo);
				if(!$UserID) { error(403); }
				$TorrentPass = $_REQUEST['torrent_pass'];
			}
			require(SERVER_ROOT.'/classes/class_torrent.php');
			
			$TorrentID = $_REQUEST['id'];
			
			if (!is_number($TorrentID)){ error(0); }
			
			$Info = $Cache->get_value('torrent_download_'.$TorrentID);
			if(!is_array($Info)) {
				$DB->query("SELECT
					t.Media,
					t.Format,
					t.Encoding,
					IF(t.RemasterYear=0,tg.Year,t.RemasterYear),
					tg.ID AS GroupID,
					tg.Name,
					tg.WikiImage,
					tg.CategoryID
					FROM torrents AS t
					INNER JOIN torrents_group AS tg ON tg.ID=t.GroupID
					WHERE t.ID='".db_string($TorrentID)."'");
				if($DB->record_count() < 1) {
					header('Location: log.php?search='.$TorrentID);
					die();
				}
				$Info = array($DB->next_record(MYSQLI_NUM, array(4,5,6)));
				$Info['Artists'] = display_artists(get_artist($Info[0][4]), false, true);
				$Cache->cache_value('torrent_download_'.$TorrentID, $Info, 0);
			}
			if(!is_array($Info[0])) {
				error(404);
			}
			list($Media,$Format,$Encoding,$Year,$GroupID,$Name,$Image, $CategoryID) = array_shift($Info); // used for generating the filename
			$Artists = $Info['Artists'];
			
			//Stupid Recent Snatches On User Page
			if($CategoryID == '1' && $Image != "") {
				$RecentSnatches = $Cache->get_value('recent_snatches_'.$UserID);
				if(is_array($RecentSnatches)) {
					array_pop($RecentSnatches);
					array_unshift($RecentSnatches, array('ID'=>$GroupID,'Name'=>$Name,'Artist'=>$Artists,'WikiImage'=>$Image));
				} else {
					$RecentSnatches = array(array('ID'=>$GroupID,'Name'=>$Name,'Artist'=>$Artists,'WikiImage'=>$Image));
				}
				$Cache->cache_value('recent_snatches_'.$UserID, $RecentSnatches, 0);
			}
			
			// Fucking btjunkie piece of shit
			if(!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'btjunkie.org')) {
				$DB->query("UPDATE users_main SET Cursed='1' WHERE ID='$UserID'");
				$DB->query("UPDATE users_info SET AdminComment=CONCAT('".sqltime()." - Account cursed at $LoggedUser[BytesDownloaded] bytes downloaded for accessing the site from ".db_string($_SERVER['HTTP_REFERER'])."
			
			', AdminComment) WHERE UserID='$LoggedUser[ID]'");
			
			}
			
			$DB->query("INSERT INTO users_downloads (UserID, TorrentID, Time) VALUES ('$UserID', '$TorrentID', '".sqltime()."') ON DUPLICATE KEY UPDATE Time=VALUES(Time)");
			
			$DB->query("SELECT File FROM torrents_files WHERE TorrentID='$TorrentID'");
			list($Contents) = $DB->next_record(MYSQLI_NUM, array(0));
			$Contents = unserialize(base64_decode($Contents));
			$Tor = new TORRENT($Contents, true); // New TORRENT object
			// Set torrent announce URL
			$Tor->set_announce_url(ANNOUNCE_URL.'/'.$TorrentPass.'/announce');
			// Remove multiple trackers from torrent
			unset($Tor->Val['announce-list']);
			// Remove web seeds (put here for old torrents not caught by previous commit
			unset($Tor->Val['url-list']);
			// Torrent name takes the format of Artist - Album - YYYY (Media - Format - Encoding)
			
			$TorrentName='';
			$TorrentInfo='';
			
			$TorrentName = $Artists;
			
			$TorrentName.=$Name;
			
			if ($Year>0) { $TorrentName.=' - '.$Year; }
			
			if ($Media!='') { $TorrentInfo.=$Media; }
			
			if ($Format!='') {
				if ($TorrentInfo!='') { $TorrentInfo.=' - '; }
				$TorrentInfo.=$Format;
			}
			
			if ($Encoding!='') {
				if ($TorrentInfo!='') { $TorrentInfo.=' - '; }
				$TorrentInfo.=$Encoding;
			}
			
			if ($TorrentInfo!='') { $TorrentName.=' ('.$TorrentInfo.')'; }
			
			if(!empty($_GET['mode']) && $_GET['mode'] == 'bbb'){
				$TorrentName = $Artists.' -- '.$Name;
			}
			
			if (!$TorrentName) { $TorrentName="No Name"; }
			
			if($DownloadAlt) {
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.file_string($TorrentName).'.txt"');
				//header('Content-Length: '.strlen($Tor->enc()));
				echo $Tor->enc();
				
			} elseif(!$DownloadAlt || $Failed) {
				header('Content-Type: application/x-bittorrent');
				header('Content-Disposition: inline; filename="'.file_string($TorrentName).'.torrent"');
				//header('Content-Length: '.strlen($Tor->enc()));
				echo $Tor->enc();
			}
			
			break;
		case 'reply':
			enforce_login();
			if (!isset($_POST['groupid']) || !is_number($_POST['groupid']) || empty($_POST['body'])) { 
				error(0);
			}
			if($LoggedUser['DisablePosting']) {
				error('Your posting rights have been removed.');
			}
			
			$GroupID = $_POST['groupid'];
			if(!$GroupID) { error(404); }
		
			$DB->query("SELECT CEIL((SELECT COUNT(ID)+1 FROM torrents_comments AS tc WHERE tc.GroupID='".db_string($GroupID)."')/".TORRENT_COMMENTS_PER_PAGE.") AS Pages");
			list($Pages) = $DB->next_record();
		
			$DB->query("INSERT INTO torrents_comments (GroupID,AuthorID,AddedTime,Body) VALUES (
				'".db_string($GroupID)."', '".db_string($LoggedUser['ID'])."','".sqltime()."','".db_string($_POST['body'])."')");
			$PostID=$DB->inserted_id();
		
			$CatalogueID = floor((TORRENT_COMMENTS_PER_PAGE*$Pages-TORRENT_COMMENTS_PER_PAGE)/THREAD_CATALOGUE);
			$Cache->begin_transaction('torrent_comments_'.$GroupID.'_catalogue_'.$CatalogueID);
			$Post = array(
				'ID'=>$PostID,
				'AuthorID'=>$LoggedUser['ID'],
				'AddedTime'=>sqltime(),
				'Body'=>$_POST['body'],
				'EditedUserID'=>0,
				'EditedTime'=>'0000-00-00 00:00:00',
				'Username'=>''
				);
			$Cache->insert('', $Post);
			$Cache->commit_transaction(0);
			$Cache->increment('torrent_comments_'.$GroupID);
			
			header('Location: torrents.php?id='.$GroupID.'&page='.$Pages);
			break;
		
		case 'get_post':
			enforce_login();
			if (!$_GET['post'] || !is_number($_GET['post'])) { error(0); }
			$DB->query("SELECT Body FROM torrents_comments WHERE ID='".db_string($_GET['post'])."'");
			list($Body) = $DB->next_record(MYSQLI_NUM,false);
		
			echo trim($Body);
			break;
		
		case 'takeedit_post':
			enforce_login();
			include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
			$Text = new TEXT;
		
			// Quick SQL injection check
			if(!$_POST['post'] || !is_number($_POST['post'])) { error(0); }
			
			// Mainly
			$DB->query("SELECT
				tc.Body,
				tc.AuthorID,
				tc.GroupID,
				tc.AddedTime
				FROM torrents_comments AS tc
				WHERE tc.ID='".db_string($_POST['post'])."'");
			list($OldBody, $AuthorID,$GroupID,$AddedTime)=$DB->next_record();
			
			$DB->query("SELECT ceil(COUNT(ID) / ".POSTS_PER_PAGE.") AS Page FROM torrents_comments WHERE GroupID = $GroupID AND ID <= $_POST[post]");
			list($Page) = $DB->next_record();
			
			if ($LoggedUser['ID']!=$AuthorID && !check_perms('site_moderate_forums')) { error(404); }
			if ($DB->record_count()==0) { error(404); }
		
			// Perform the update
			$DB->query("UPDATE torrents_comments SET
				Body = '".db_string($_POST['body'])."',
				EditedUserID = '".db_string($LoggedUser['ID'])."',
				EditedTime = '".sqltime()."'
				WHERE ID='".db_string($_POST['post'])."'");
		
			// Update the cache
			$CatalogueID = floor((TORRENT_COMMENTS_PER_PAGE*$Page-TORRENT_COMMENTS_PER_PAGE)/THREAD_CATALOGUE);
			$Cache->begin_transaction('torrent_comments_'.$GroupID.'_catalogue_'.$CatalogueID);
			
			$Cache->update_row($_POST['key'], array(
				'ID'=>$_POST['post'],
				'AuthorID'=>$AuthorID,
				'AddedTime'=>$AddedTime,
				'Body'=>$_POST['body'],
				'EditedUserID'=>db_string($LoggedUser['ID']),
				'EditedTime'=>sqltime(),
				'Username'=>$LoggedUser['Username']
			));
			$Cache->commit_transaction(0);
		
			$DB->query("INSERT INTO comments_edits (Page, PostID, EditUser, EditTime, Body)
									VALUES ('torrents', ".db_string($_POST['post']).", ".db_string($LoggedUser['ID']).", '".sqltime()."', '".db_string($OldBody)."')");
			
			// This gets sent to the browser, which echoes it in place of the old body
			echo $Text->full_format($_POST['body']);
			break;
		
		case 'delete_post':
			enforce_login();
			authorize();
		
			// Quick SQL injection check
			if (!$_GET['postid'] || !is_number($_GET['postid'])) { error(0); }
		
			// Make sure they are moderators
			if (!check_perms('site_moderate_forums')) { error(403); }
		
			// Get topicid, forumid, number of pages
			$DB->query("SELECT DISTINCT
				GroupID,
				CEIL((SELECT COUNT(tc1.ID) FROM torrents_comments AS tc1 WHERE tc1.GroupID=tc.GroupID)/".TORRENT_COMMENTS_PER_PAGE.") AS Pages,
				CEIL((SELECT COUNT(tc2.ID) FROM torrents_comments AS tc2 WHERE tc2.ID<'".db_string($_GET['postid'])."')/".TORRENT_COMMENTS_PER_PAGE.") AS Page
				FROM torrents_comments AS tc
				WHERE tc.GroupID=(SELECT GroupID FROM torrents_comments WHERE ID='".db_string($_GET['postid'])."')");
			list($GroupID,$Pages,$Page)=$DB->next_record();
		
			// $Pages = number of pages in the thread
			// $Page = which page the post is on
			// These are set for cache clearing.
		
			$DB->query("DELETE FROM torrents_comments WHERE ID='".db_string($_GET['postid'])."'");
		
			//We need to clear all subsequential catalogues as they've all been bumped with the absence of this post
			$ThisCatalogue = floor((POSTS_PER_PAGE*$Page-POSTS_PER_PAGE)/THREAD_CATALOGUE);
			$LastCatalogue = floor((POSTS_PER_PAGE*$Pages-POSTS_PER_PAGE)/THREAD_CATALOGUE);
			for($i=$ThisCatalogue;$i<=$LastCatalogue;$i++) {
				$Cache->delete('thread_'.$TopicID.'_catalogue_'.$i);
			}
			
			// Delete thread info cache (eg. number of pages)
			$Cache->delete('torrentcomments_count_'.$GroupID);
			break;
		default:
			enforce_login();
		
			if(!empty($_GET['id'])) {
				include(SERVER_ROOT.'/sections/torrents/details.php');
			} elseif(isset($_GET['torrentid']) && is_number($_GET['torrentid'])) {
				$DB->query("SELECT GroupID FROM torrents WHERE ID=".$_GET['torrentid']);
				list($GroupID) = $DB->next_record();
				if($GroupID) {
					header("Location: torrents.php?id=".$GroupID."&torrentid=".$_GET['torrentid']);
				}
			} else {
				include(SERVER_ROOT.'/sections/torrents/browse2.php');
			}
			break;
	}
} else {
	enforce_login();

	if(!empty($_GET['id'])) {
		include(SERVER_ROOT.'/sections/torrents/details.php');
	} elseif(isset($_GET['torrentid']) && is_number($_GET['torrentid'])) {
		$DB->query("SELECT GroupID FROM torrents WHERE ID=".$_GET['torrentid']);
		list($GroupID) = $DB->next_record();
		if($GroupID) {
			header("Location: torrents.php?id=".$GroupID."&torrentid=".$_GET['torrentid']);
		} else {
			header("Location: log.php?search=Torrent+".$_GET['torrentid']);
		}
	} elseif(!empty($_GET['type'])) {
		include(SERVER_ROOT.'/sections/torrents/user.php');
	} else {
		include(SERVER_ROOT.'/sections/torrents/browse2.php');
	}
	
}
?>
