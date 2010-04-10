<?
// perform the back end of subscribing to topics
authorize();
if(!is_number($_GET['topicid'])) {
	echo 'HAX ATTEMPT!'.$_GET['topicid'];
	die();
}

$DB->query('SELECT MinClassRead FROM forums WHERE forums.ID = (SELECT ForumID FROM forums_topics WHERE ID = '.db_string($_GET['topicid']).')');
list($MinClassRead) = $DB->next_record();
if($MinClassRead>$LoggedUser['Class']) {
	die();
}

if(!$UserSubscriptions = $Cache->get_value('subscriptions_user_'.$LoggedUser['ID'])) {
	$DB->query('SELECT TopicID FROM users_subscriptions WHERE UserID = '.db_string($LoggedUser['ID']));
	$UserSubscriptions = $DB->collect(0);
	$Cache->cache_value('subscriptions_user_'.$LoggedUser['ID'],$UserSubscriptions,0);
}

if(($Key = array_search($_GET['topicid'],$UserSubscriptions)) !== FALSE) {
	$DB->query('DELETE FROM users_subscriptions WHERE UserID = '.db_string($LoggedUser['ID']).' AND TopicID = '.db_string($_GET['topicid']));
	if(($key = array_search($_GET['topicid'], $UserSubscriptions)) !== FALSE) {
		unset($UserSubscriptions[$key]);
	}
} else {
	$DB->query("INSERT INTO users_subscriptions VALUES ($LoggedUser[ID], ".db_string($_GET['topicid']).")");
	array_push($UserSubscriptions, $_GET['topicid']);
}
$Cache->replace_value('subscriptions_user_'.$LoggedUser['ID'], $UserSubscriptions, 0);
