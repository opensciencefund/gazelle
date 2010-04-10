<?
enforce_login();

define('LOG_ENTRIES_PER_PAGE', 25);
list($Page,$Limit) = page_limit(LOG_ENTRIES_PER_PAGE);

if(!empty($_GET['search'])) {
	$Search = db_string($_GET['search']);
} else {
	$Search = false;
}
$Words = explode(' ', $Search);
$sql = "SELECT
	SQL_CALC_FOUND_ROWS 
	Message,
	Time
	FROM log ";
if($Search) {
	$sql .= "WHERE Message LIKE '%";
	$sql .= implode("%' AND Message LIKE '%", $Words);
	$sql .= "%' ";
}
if(!check_perms('site_view_full_log')) {
	if($Search) {
		$sql.=" AND "; 
	} else {
		$sql.=" WHERE ";
	}
	$sql .= " Time>'".time_minus(3600*24*28)."' ";
}

$sql .= "ORDER BY ID DESC LIMIT $Limit";

show_header("Site log");
show_message();

$Log = $DB->query($sql);
$DB->query("SELECT FOUND_ROWS()");
list($Results) = $DB->next_record();
$DB->set_query_id($Log);
?>
<div class="thin">
	<h2>Site log</h2>
	<div class="center">
		<form action="" method="get">
			<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
				<tr>
					<td class="label"><strong>Search for:</strong></td>
					<td>
						<input type="text" name="search" size="60"<? if (!empty($_GET['search'])) { echo ' value="'.display_str($_GET['search']).'"'; } ?> />
						&nbsp;
						<input type="submit" value="Search log" />
					</td>
				</tr>
			</table>	
		</form>
	</div>
	
	
	
	<div class="linkbox">
<?
$Pages=get_pages($Page,$Results,LOG_ENTRIES_PER_PAGE,9);
echo $Pages;
?>
	</div>
	
	<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
		<tr class="colhead">
			<td style="width: 180px;"><strong>Time</strong></td>
			<td><strong>Message</strong></td>
		</tr>

<?
if($DB->record_count() == 0) {
	echo '<tr class="nobr"><td colspan="2">Nothing found!</td></tr>';
}
$Row = 'a';
while(list($Message, $LogTime) = $DB->next_record()) {
	if(preg_match('/(Torrent|Collage|Wiki article) \d+ (\(.+\))?( \([^\)]*\))? was (created|uploaded|recovered) by [^ ]+/s', $Message, $Match)){
		//Torrent 955774 (The Beach Boys - 20 Good Vibrations) (111.71 MB) was uploaded by Median
		//Collage 1757 (Wizard TOP100) was created by sotamarsu  		
		$Color = 'green';
	} elseif(preg_match('/(Torrent|Collage|Wiki article)  ?\d+ \(.+\)( \([^\)]*\))? was( automatically)? deleted.*/s', $Message, $Match)){
		//Torrent 50476 ((a) Senile Animal [FLAC/Lossless/CD]) was automatically deleted by sickofjesus, per trump (100% FLAC trumps 90% FLAC: http://what.cd/torrents.php?id=30743&torrentid=952396)
		//Torrent 889878 (Lynda com Adobe Motion Natural Light Effects-iNKiSO) (0.00 MB) was deleted by NightGuard: re upload with other piece size
		//Torrent 751451 (Q. Stone - Q. Stone [MP3 / V0 (VBR)]) was deleted for inactivity (unseeded)
		//Collage 151 (Electronic Music) was deleted by WormsWitchity: Collages must be based on fact and not opinion.
		//Wiki article 270 (test french rules) was deleted by oinkmeup	
		$Color = 'red';
	} elseif(preg_match('/Torrent \d+( \(.*\) in group \d+)? was edited by [^ ]+/s', $Message, $Match)){
		//Torrent 954785 () in group 507459 was edited by Louis70  
		$Color = 'blue';
	} else {
		$Color = false;
	}
	$Row = ($Row == 'a') ? 'b' : 'a';
?>
		<tr class="row<?=$Row?>">
			<td class="nobr">
				<?=time_diff($LogTime)?>
			</td>
			<td>
				<span<? if($Color) { ?> style="color: <?=$Color ?>;"<? } ?>><?=display_str($Message)?></span>
			</td>
		</tr>
<?
}
?>
	</table>
	<div class="linkbox">
		<?=$Pages?>
	</div>
</div>
<?
show_footer() ?>
