<?
//DEPRECIATED: This file is not deleted in case it needs to be referenced for bugs, but is no longer in use, if you're reading this, and it's past Jan 20th '10 delete it. 
//TODO: Remove all the stupid queries that could get their information just as easily from the cache
/*********************************************************************\
//--------------Take Post--------------------------------------------//

This page takes a forum post submission, validates it (TODO), and
enters it into the database. The user is then redirected to their 
post.

$_POST['action'] is what the user is trying to do. It can be:

'reply' if the user is replying to a thread
	It will be accompanied with:
	$_POST['thread']
	$_POST['body']

'new' if the user is creating a new thread
	It will be accompanied with:
	$_POST['forum']
	$_POST['title']
	$_POST['body']

	and optionally include:
	$_POST['question']
	$_POST['answers']
	the latter of which is an array


\*********************************************************************/

// Quick SQL injection checks

if (isset($LoggedUser['PostsPerPage'])) {
	$PerPage = $LoggedUser['PostsPerPage'];
} else {
	$PerPage = POSTS_PER_PAGE;
}

if(isset($_POST['thread']) && !is_number($_POST['thread'])) {
	error(404);
}
if(isset($_POST['forum']) && !is_number($_POST['forum'])) {
	error(404);
}
// End injection checks

//What are we doing?
$Action = $_POST['action'];
$Body = $_POST['body'];

if(empty($Body)) {
	error(0);
}

if($LoggedUser['DisablePosting']) {
	error('Your posting rights have been removed');
}

if($Action == 'reply' && isset($_POST['thread'])) {
	$TopicID = $_POST['thread'];
	if(!$ThreadInfo = $Cache->get_value('thread_'.$TopicID.'_info')) {
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
			WHERE t.ID = '$TopicID'
			GROUP BY fp.TopicID");
		if($DB->record_count()==0) { error(404); }
		$ThreadInfo = $DB->next_record(MYSQLI_ASSOC);
		$Cache->cache_value('thread_'.$TopicID.'_info', $ThreadInfo, 0);
	}
	$ForumID = $ThreadInfo['ForumID'];
	if($LoggedUser['Class'] < $Forums[$ForumID]['MinClassRead'] || !$ForumID) { error(404); }
	if($LoggedUser['Class'] < $Forums[$ForumID]['MinClassWrite'] || $LoggedUser['DisablePosting'] || $ThreadInfo['IsLocked'] == "1" && !check_perms('site_moderate_forums')) { error(403); }
	if($ThreadInfo['LastPostAuthorID']==$LoggedUser['ID']) {
		$Action = 'append';
	}
	if (!isset($_POST['merge']) && check_perms('site_forums_double_post')) {
		$Action = 'reply';
	}
} elseif($Action == 'new') {
	$ThreadInfo = array();
	$ThreadInfo['IsLocked'] = 0;
	$ThreadInfo['IsSticky'] = 0;
	
	$Title = cut_string(trim($_POST['title']), 150, 1, 0);
	if($Title == '') { error(0); }
	$ForumID = $_POST['forum'];
	$DB->query("SELECT 
			MinClassWrite,
			MinClassCreate
			FROM forums 
			WHERE ID='$ForumID'");
	list($MinClassWrite, $MinClassCreate) = $DB->next_record();
	if($LoggedUser['Class'] < $MinClassWrite || $LoggedUser['Class'] < $MinClassCreate || $DB->record_count() == 0) {
		error(403); 
	}
} else { // If $Action ins't what we expect
	error(0);
}

/*Begin SQL insert queries*/


if($Action == 'new') {
	$DB->query("INSERT INTO forums_topics
		(Title, AuthorID, ForumID, LastPostTime, LastPostAuthorID)
		Values
		('".db_string($Title)."', '".$LoggedUser['ID']."', '$ForumID', '".sqltime()."', '".$LoggedUser['ID']."')");
	$TopicID = $DB->inserted_id(); // Set $TopicID for if it's a new post ($TopicID is set earlier for replies)
	$Posts = 1;
} else {
	$Posts = $ThreadInfo['Posts'];
	$Title = $ThreadInfo['Title'];	
}

if($Action == 'append') {
	$DB->query("SELECT ID FROM forums_posts WHERE TopicID='$TopicID' AND AuthorID='$LoggedUser[ID]' ORDER BY ID DESC LIMIT 1");
	list($PostID) = $DB->next_record();
	$DB->query("UPDATE forums_posts SET
	Body = CONCAT(Body,'"."\n\n".db_string($Body)."'),
	EditedUserID = '".$LoggedUser['ID']."',
	EditedTime = '".sqltime()."'
	WHERE ID='$PostID'");
	$Key = ($Posts%THREAD_CATALOGUE)-1;
	$CatalogueID = floor((POSTS_PER_PAGE*ceil($Posts/POSTS_PER_PAGE)-POSTS_PER_PAGE)/THREAD_CATALOGUE);
	$Cache->begin_transaction('thread_'.$TopicID.'_catalogue_'.$CatalogueID);
	$Cache->update_row($Key, array(
			'ID'=>$Cache->MemcacheDBArray[$Key]['ID'],
			'AuthorID'=>$Cache->MemcacheDBArray[$Key]['AuthorID'],
			'AddedTime'=>$Cache->MemcacheDBArray[$Key]['AddedTime'],
			'Body'=>$Cache->MemcacheDBArray[$Key]['Body']."\n\n".$Body,
			'EditedUserID'=>$LoggedUser['ID'],
			'EditedTime'=>sqltime(),
			'Username'=>$LoggedUser['Username']
			));
	$Cache->commit_transaction(0);
} else {
	$DB->query("INSERT INTO forums_posts
			(TopicID, AuthorID, AddedTime, Body)
			VALUES
			('$TopicID', '".$LoggedUser['ID']."', '".sqltime()."', '".db_string($Body)."')");

	$PostID = $DB->inserted_id();

	if($Action == 'reply') {
		$DB->query("UPDATE forums SET
				NumPosts		  = NumPosts+1, 
				LastPostID		= '$PostID',
				LastPostAuthorID  = '".$LoggedUser['ID']."',
				LastPostTopicID   = '$TopicID',
				LastPostTime	  = '".sqltime()."'
				WHERE ID = '$ForumID'");
	} elseif($Action == 'new') {
		$DB->query("UPDATE forums SET
				NumPosts		  = NumPosts+1, 
				NumTopics		 = NumTopics+1, 
				LastPostID		= '$PostID',
				LastPostAuthorID  = '".$LoggedUser['ID']."',
				LastPostTopicID   = '$TopicID',
				LastPostTime	  = '".sqltime()."'
				WHERE ID = '$ForumID'");
	}
	$DB->query("UPDATE forums_topics SET
			NumPosts		  = NumPosts+1, 
			LastPostID		= '$PostID',
			LastPostAuthorID  = '".$LoggedUser['ID']."',
			LastPostTime	  = '".sqltime()."'
			WHERE ID = '$TopicID'");

	// Bump this topic to head of the cache
	list($Forum, $TopicIDs,,$Stickies) = $Cache->get_value('forums_'.$ForumID);
	if($Forum){
		if(array_key_exists($TopicID,$Forum)){
			$Thread = $Forum[$TopicID];
			unset($Forum[$TopicID]);
		
			$Thread['NumPosts'] = $Thread['NumPosts']+1; //Increment post count
			$Thread['LastPostID'] = $PostID; //Set postid for read/unread
			$Thread['LastPostTime'] = sqltime(); //Time of last post
			$Thread['LastPostAuthorID'] = $LoggedUser['ID']; //Last poster id
			$Thread['LastPostUsername'] = $LoggedUser['Username']; //Last poster username
			$Part1 = array_slice($Forum,0,$Stickies,true); //Stickys
			$Part2 = array($TopicID=>$Thread); //Bumped thread
			$Part3 = array_slice($Forum,$Stickies,TOPICS_PER_PAGE,true); //Rest of page
			if (is_null($Part1)) { $Part1 = array(); }
			if (is_null($Part3)) { $Part3 = array(); }
			if($Thread['IsSticky'] == 1) {
				$Forum = $Part2 + $Part1 + $Part3; //Merge it
			} else {
				$Forum = $Part1 + $Part2 + $Part3; //Merge it
			}
		} else {
			if (count($Forum) == TOPICS_PER_PAGE) {
				unset($Forum[(count($Forum)-1)]);
			}
			$DB->query("SELECT f.AuthorID, f.IsLocked, f.IsSticky, f.NumPosts, u.Username, ISNULL(p.TopicID) AS NoPoll FROM forums_topics AS f INNER JOIN users_main AS u ON u.ID=f.AuthorID LEFT JOIN forums_polls AS p ON p.TopicID=f.ID WHERE f.ID ='$TopicID'");
			list($AuthorID,$IsLocked,$IsSticky,$NumPosts,$AuthorName,$NoPoll) = $DB->next_record();
			$Part1 = array_slice($Forum,0,$Stickies,true); //Stickys
			$Part2 = array(
				$TopicID=>array(
					'ID' => $TopicID,
					'Title' => $Title,
					'AuthorID' => $AuthorID,
					'AuthorUsername' => $AuthorName,
					'IsLocked' => $IsLocked,
					'IsSticky' => $IsSticky,
					'NumPosts' => $NumPosts,
					'LastPostID' => $PostID,
					'LastPostTime' => sqltime(),
					'LastPostAuthorID' => $LoggedUser['ID'],
					'LastPostUsername' => $LoggedUser['Username'],
					'NoPoll' => $NoPoll
					)
				); //Bumped thread
			$Part3 = array_slice($Forum,$Stickies,TOPICS_PER_PAGE,true); //Rest of page
			if (is_null($Part1)) { $Part1 = array(); }
			if (is_null($Part3)) { $Part3 = array(); }
			$Forum = $Part1 + $Part2 + $Part3;
		}
		$TopicArray=array_keys($Forum);
		$TopicIDs = implode(', ', $TopicArray);
		$Cache->cache_value('forums_'.$ForumID, array($Forum,$TopicIDs,0,$Stickies), 0);
	}

	//Update the forum root
	$Cache->begin_transaction('forums_list');
	$UpdateArray = array(
		'NumPosts'=>'+1', 
		'LastPostID'=>$PostID, 
		'LastPostAuthorID'=>$LoggedUser['ID'], 
		'Username'=>$LoggedUser['Username'], 
		'LastPostTopicID'=>$TopicID, 
		'LastPostTime'=>sqltime(),
		'Title'=>$Title,
		'IsLocked'=>$ThreadInfo['IsLocked'],
		'IsSticky'=>$ThreadInfo['IsSticky']
		);

	if($Action == 'new') {
		$UpdateArray['NumTopics']='+1';
	}
	$Cache->update_row($ForumID, $UpdateArray);
	$Cache->commit_transaction(0);

	$CatalogueID = floor((POSTS_PER_PAGE*ceil($Posts/POSTS_PER_PAGE)-POSTS_PER_PAGE)/THREAD_CATALOGUE);
	$Cache->begin_transaction('thread_'.$TopicID.'_catalogue_'.$CatalogueID);
	$Post = array(
		'ID'=>$PostID,
		'AuthorID'=>$LoggedUser['ID'],
		'AddedTime'=>sqltime(),
		'Body'=>$Body,
		'EditedUserID'=>0,
		'EditedTime'=>'0000-00-00 00:00:00',
		'Username'=>''
		);
	$Cache->insert('', $Post);
	$Cache->commit_transaction(0);

	$Cache->begin_transaction('thread_'.$TopicID.'_info');
	$Cache->update_row(false, array('Posts'=>'+1', 'LastPostAuthorID'=>$LoggedUser['ID']));
	$Cache->commit_transaction(0);
}

if($Action == 'reply') {
	$Posts++;
}

header('Location: forums.php?action=viewthread&threadid='.$TopicID.'&page='.ceil($Posts/$PerPage));
die();
