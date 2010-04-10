<?
//TODO: Rewrite this whole damn thing, it's awful.
/*
Problems:
Performs a query PER TAG in a search, as opposed to wrapping it all into a single query
Caches data without scruitany, we're wasting ram with the entire TagName* cache key set.
Impossible to follow the flow of it because outputs split between a class and this.
Breaks new standard of classes containing only functions or tools. Mininmal to no output in the class files.
*/

// Requests class
include(SERVER_ROOT.'/classes/class_requests.php');
$Req = new REQUESTS;

// SQL limit
if(isset($_GET['page']) && is_number($_GET['page'])) {
	$Limit=REQUESTS_PER_PAGE*$_GET['page']-REQUESTS_PER_PAGE. ', ' . REQUESTS_PER_PAGE;
} else {
	$Limit=REQUESTS_PER_PAGE;
}

// Array of search words, if they're searching for something
if(isset($_GET['search']) && !empty($_GET['search'])) {
	$Words = explode(' ', $_GET['search']);
} else {
	$Words = array();
}

// Array of tag names - used for the search bar
$TagNames = array();

// $_GET['tag'] is a single tag ID
// This code fetches the name of the tag (for the search bar)
// Then stores it in $TagNames
if(isset($_GET['tag']) && !empty($_GET['tag'])) {
	$TagID = $_GET['tag'];
	if(!is_number($TagID)) { error(0); }
	
	// Try to get name of tag from cache, so we don't hit the database
	$TagNames[0] = $Cache->get_value('TagName'.$TagID);
	if(!$TagNames[0]) {
		// Couldn't get the cached name. Database time.
		$DB->query('SELECT Name FROM tags WHERE ID=\''.$TagID.'\'');
		list($TagNames[0]) = $DB->next_record();
		// Cache the name so we don't have to hit the database again
		$Cache->cache_value('TagName'.$TagID, $TagNames[0], 3600*24);
	}
}

// $_GET['tags'] is a comma-separated list of tags
// It gets set if someone enters one or more tags into the search bar. 
elseif(isset($_GET['tags']) && !empty($_GET['tags'])){
	// Sanatize them, for consistency and database usage
	$Tags = explode(',', $_GET['tags']);
	$DBTags = array(); // Escaped list of tag names for database usage
	foreach ($Tags as $Tag){
		$Tag = trim($Tag);
		$Tag = strtolower($Tag);
		$Tag = str_replace(' ', '.', $Tag);
		if(strlen($Tag)>0){ // If the tag isn't null
			$TagNames[]=$Tag; // Add it to the list of tags, for the search bar
			$DBTags[]='\''.db_string($Tag).'\''; // Database-ready tag
		}
	}
	unset($Tags); // Replaced by $DBTags
}

// If they're searching for something
if(count($Words)>0 && !(count($Words) == 1 && $Words[0] == '')) { // make sure that $Words isn't just null
	$SearchWords = array();
	foreach($Words AS $Word) {
		// Array for SQL WHERE. Searches the artist name and title
		$SearchWords[]="(r.name LIKE '%".db_string($Word)."%' OR ag.Name LIKE '%".db_string($Word)."%') ";
	}
}
unset($Words); // Replaced by $SearchWords

if(isset($_GET['order']) && $_GET['order'] == 'name') {
	// Since the page displays the name as "artist - title", this is the order users expect.
	$Order = 'CONCAT(ag.Name, r.Name) '; 
}
// Make sure they aren't trying some tricky ordering
elseif(isset($_GET['order']) && in_array($_GET['order'], array('votes', 'bounty', 'id', 'filler.username', 'u.username'))){
	$Order = $_GET['order'].' ';
	if($Order == 'filler.username ') {
		$Order = 'IF(filler.username IS NULL,1,0),filler.username ';
	} elseif($Order == 'u.username') {
		$Order = 'IF(u.username IS NULL,1,0),u.username ';
	}
}
// If there's no order, or they're trying something tricky
else {
	$Order = 'ID ';
}

// Build SQL query
$sql = "SELECT
	SQL_CALC_FOUND_ROWS 
	r.ID,
	r.Name,
	r.ArtistID,
	ag.Name AS ArtistName,
	GROUP_CONCAT(DISTINCT t.ID SEPARATOR '|') AS TagIDs, 
	GROUP_CONCAT(DISTINCT t.Name SEPARATOR '|') AS TagNames, 
	r.TimeAdded,
	(SELECT COUNT(v.RequestID) FROM requests_votes AS v WHERE v.RequestID=r.ID) AS Votes,
	r.FillerID,
	filler.Username,
	r.Filled,
	r.Bounty,
	r.UserID,
	u.Username
	FROM requests AS r
	LEFT JOIN users_main AS u ON u.ID=UserID
	LEFT JOIN users_main AS filler ON filler.ID=FillerID AND FillerID!=0
	LEFT JOIN artists_group AS ag ON ag.ArtistID=r.ArtistID AND r.ArtistID!=0
	JOIN requests_tags AS rt ON rt.RequestID=r.ID 
	JOIN tags AS t ON t.ID=rt.TagID 
	";
// If a user is viewing their own requests
if(isset($_GET['type']) && $_GET['type']=='voted') {
	$sql.=" JOIN requests_votes AS v ON v.RequestID=r.ID ";
}

// Requests are hidden after 3 days of being filled
if((isset($_GET['showall']) && $_GET['showall'] || isset($_GET['type']) && $_GET['type'] == 'filled') && check_perms('site_see_old_requests')) {
	$sql.="WHERE true ";
} else {
	$sql.="WHERE Visible='1' ";
}

if(isset($_GET['requester']) && !empty($_GET['requester']) && check_perms('site_see_old_requests')) {
	$sql.=" AND u.Username LIKE '".db_string($_GET['requester'])."' ";
}

if(isset($TagID)) {
	$sql.=" AND (SELECT TagID FROM requests_tags WHERE TagID='$TagID' AND RequestID=r.ID) ";
} elseif(isset($DBTags)) {
	foreach($DBTags as $Tag){
		$sql.=" AND (SELECT ID FROM tags JOIN requests_tags ON requests_tags.TagID=tags.ID WHERE Name=$Tag AND RequestID=r.ID) ";
	}
}

// If we're searching artist names and titles
if(isset($SearchWords)) {
	$sql.="AND ".implode("AND ", $SearchWords);
}
// If a user is viewing their own requests
if(isset($_GET['type']) && $_GET['type']=='created') {
	$sql.=" AND r.UserID='$LoggedUser[ID]' ";
}
// If a user is viewing requests they've voted on
if(isset($_GET['type']) && $_GET['type']=='voted') {
	$sql.=" AND v.UserID='$LoggedUser[ID]' ";
}
// If we're viewing a user's filled requests
if(isset($_GET['type']) && $_GET['type']=='filled' && is_number($_GET['userid'])) {
	$sql.=" AND r.FillerID='".db_string($_GET['userid'])."' ";
}

// Because SQL demands a GROUP BY
$sql .= " GROUP BY r.ID ";
// Ordering (the order was sanatized earlier)
$sql .= " ORDER BY $Order ";
// Sorting - make sure they aren't doing anything tricky, sort DESC by default

if(!isset($_GET['sort'])) { $_GET['sort'] = 'desc'; }
$sql .= (in_array($_GET['sort'], array('asc', 'desc'))) ? db_string($_GET['sort']).' ' : ' DESC ';
$sql .= " LIMIT $Limit";

// Page title, used for <title> and <h2>

if(empty($_GET['type'])) { $_GET['type']=''; }
switch($_GET['type']) {
	case 'created':
		$Title = 'My requests';
		break;
	case 'voted':
		$Title = 'Requests I\'ve voted on';
		break;
	default:
		$Title = 'Requests';
}

show_header($Title);

//---------- Start utilizing request class

// Tell it what SQL to use - the class executes the SQL, and uses an md5sum of it as the cache key
$Req->set_sql($sql);

// Used for the <h2>
$Req->set_title($Title);

// List of tag names, for the search box
$Req->set_tag_names($TagNames);

// Set up page (runs SQL)
$Req->create_page();

// <h2>, search boxes and pagination
$Req->create_header();

// Table of requests
$Req->requests_table();

// Pagination
$Req->create_footer();

// And we're done!
show_footer();

?>
