<?
authorize();
// Quick SQL injection check
if(!isset($_GET['postid']) || !is_number($_GET['postid'])) { error(0); }
$PostID = $_GET['postid'];

// Make sure they are moderators
if(!check_perms('site_moderate_forums')) {
	error(403);
}

// Get topicid, forumid, number of pages
$DB->query("SELECT 
	DISTINCT
	TopicID,
	ForumID,
	CEIL(
		(SELECT COUNT(ID) 
		FROM forums_posts 
		WHERE TopicID=p.TopicID)/".POSTS_PER_PAGE."
	) AS Pages,
	CEIL(
		(SELECT COUNT(ID) 
		FROM forums_posts 
		WHERE ID<'$PostID'
		AND TopicID=p.TopicID)/".POSTS_PER_PAGE."
	) AS Page
	FROM forums_posts AS p
	JOIN forums_topics AS t ON t.ID=p.TopicID
	WHERE p.TopicID=(SELECT TopicID FROM forums_posts WHERE ID='$PostID')");
list($TopicID, $ForumID, $Pages, $Page) = $DB->next_record();

// $Pages = number of pages in the thread
// $Page = which page the post is on
// These are set for cache clearing.

$DB->query("DELETE FROM forums_posts WHERE ID='$PostID'");

$DB->query("SELECT MAX(ID) FROM forums_posts WHERE TopicID='$TopicID'");
list($LastID) = $DB->next_record();
$DB->query("SELECT AuthorID, AddedTime FROM forums_posts WHERE ID='$LastID'");
list($LastAuthorID, $LastTime) = $DB->next_record();

$DB->query("UPDATE forums SET NumPosts=NumPosts-1 WHERE ID='$ForumID'");
$DB->query("UPDATE forums_topics SET NumPosts=NumPosts-1, LastPostID='$LastID', LastPostAuthorID='$LastAuthorID', LastPostTime='$LastTime' WHERE ID='$TopicID'");
$DB->query("UPDATE forums SET LastPostID='$LastID', LastPostAuthorID='$LastAuthorID', LastPostTime='$LastTime' WHERE ID='$ForumID' AND LastPostTopicID='$TopicID'");

//We need to clear all subsequential catalogues as they've all been bumped with the absence of this post
$ThisCatalogue = floor((POSTS_PER_PAGE*$Page-POSTS_PER_PAGE)/THREAD_CATALOGUE);
$LastCatalogue = floor((POSTS_PER_PAGE*$Pages-POSTS_PER_PAGE)/THREAD_CATALOGUE);
for($i=$ThisCatalogue;$i<=$LastCatalogue;$i++) {
	$Cache->delete('thread_'.$TopicID.'_catalogue_'.$i);
}

$Cache->begin_transaction('thread_'.$TopicID.'_info');
$Cache->update_row(false, array('Posts'=>'-1','LastPostAuthorID'=>$LastAuthorID));
$Cache->commit_transaction();

$Cache->delete('forums_'.$ForumID);
?>
