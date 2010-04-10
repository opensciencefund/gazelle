<?
define('COLLAGES_PER_PAGE', 25);

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;

list($Page,$Limit) = page_limit(COLLAGES_PER_PAGE);


$OrderVals = array('Time', 'Name', 'Torrents');
$WayVals = array('Ascending', 'Descending');
$OrderTable = array('Time'=>'ID', 'Name'=>'c.Name', 'Torrents'=>'NumTorrents');
$WayTable = array('Ascending'=>'ASC', 'Descending'=>'DESC');

// Are we searching in bodies, or just names?
if(!empty($_GET['type'])) {
	$Type = $_GET['type'];
	if(!in_array($Type, array('c.name', 'description'))) {
		$Type = 'c.name';
	}
} else {
	$Type = 'c.name';
}

if(!empty($_GET['search'])) {
	// What are we looking for? Let's make sure it isn't dangerous.
	$Search = strtr(db_string(trim($_GET['search'])),$SpecialChars);
	// Break search string down into individual words
	$Words = explode(' ', $Search);
}



// Ordering
if(!empty($_GET['order'])) {
	$Order = $OrderTable[$_GET['order']];
} else {
	$Order = 'ID';
}

if(!empty($_GET['way'])) {
	$Way = $WayTable[$_GET['way']];
} else {
	$Way = 'DESC';
}

$SQL = "SELECT SQL_CALC_FOUND_ROWS 
	c.ID, 
	c.Name, 
	c.NumTorrents,
	c.UserID,
	um.Username 
	FROM collages AS c 
	LEFT JOIN users_main AS um ON um.ID=c.UserID 
	WHERE Deleted = '0'";

if(!empty($Search)) {
	$SQL .= " AND $Type LIKE '%";
	$SQL .= implode("%' AND $Type LIKE '%", $Words);
	$SQL .= "%' ";
}

$SQL.=" ORDER BY $Order $Way LIMIT $Limit ";
$DB->query($SQL);
$Collages = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();


show_header('Browse collages');
?>
<div class="thin">
	<h2>Browse collages</h2>
	<? show_message(); // Typically "your collage has been deleted" ?>
	<div class="center">
		<form action="" method="get">
			<div><input type="hidden" name="action" value="search" /></div>
			<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
				<tr>
					<td class="label"><strong>Search for:</strong></td>
					<td colspan="3">
						<input type="text" name="search" size="70" value="<?=(!empty($_GET['search']) ? display_str($_GET['search']) : '')?>" />
					</td>
				</tr>
				<tr>
					<td class="label"><strong>Search in:</strong></td>
					<td>
						<input type="radio" name="type" value="c.name" <? if($Type == 'c.name') { echo 'checked="checked" '; }?>/> Names
						<input type="radio" name="type" value="description" <? if($Type == 'description') { echo 'checked="checked" '; }?>/> Descriptions
					</td>
					<td class="label"><strong>Order by:</strong></td>
					<td>
						<select name="order">
						<?
							foreach($OrderVals as $Cur){ ?>
							<option value="<?=$Cur?>"<? if(isset($_GET['order']) && $_GET['order'] == $Cur || (!isset($_GET['order']) && $Cur == 'Time')) { echo ' selected="selected"'; } ?>><?=$Cur?></option>
						<?	}?>
						</select>
						<select name="way">
						<?	foreach($WayVals as $Cur){ ?>
							<option value="<?=$Cur?>"<? if(isset($_GET['way']) && $_GET['way'] == $Cur || (!isset($_GET['way']) && $Cur == 'Descending')) { echo ' selected="selected"'; } ?>><?=$Cur?></option>
						<?	}?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="4" class="center">
						<input type="submit" value="Search" />
					</td>
				</tr>
			</table>	
		</form>
	</div>
	<br />
	<div class="linkbox">
<? if (check_perms('site_collages_create')) { ?>
		<a href="collages.php?action=new">[New collage]</a> <br /><br />
<? } 
	if (check_perms('site_collages_recover')) { ?>
		<a href="collages.php?action=recover">[Recover collage]</a> <br /><br />
<? } ?>
<?
$Pages=get_pages($Page,$NumResults,COLLAGES_PER_PAGE,9);
echo $Pages;
?>
	</div>
<table width="100%">
	<tr class="colhead">
		<td>Collage</td>
		<td>Torrents</td>
		<td>Author</td>
	</tr>
<?
$Row = 'a'; // For the pretty colours
foreach ($Collages as $Collage) {
	list($ID, $Name, $NumTorrents, $UserID, $Username) = $Collage;
	$Row = ($Row == 'a') ? 'b' : 'a';
	// Print renults
?>
	<tr class="row<?=$Row?>">
		<td><a href="collages.php?id=<?=$ID?>"><?=$Name?></a></td>
		<td><?=$NumTorrents?></td>
		<td><?=format_username($UserID, $Username)?></td>
	</tr>
<? } ?>
</table>
	<div class="linkbox"><?=$Pages?></div>
</div>
<?
show_footer();
?>
