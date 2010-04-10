<?
enforce_login();
show_header('Staff');

if (!$Support = $Cache->get_value('staff')) {
	$DB->query("SELECT
		m.ID,
		m.Username,
		m.Paranoia,
		m.LastAccess,
		i.SupportFor
		FROM users_info AS i
		JOIN users_main AS m ON m.ID=i.UserID
		WHERE i.SupportFor!=''");
	$FrontLineSupport = $DB->to_array();
	$DB->query("SELECT
		m.ID,
		p.Level,
		p.Name,
		m.Username,
		m.Paranoia,
		m.LastAccess
		FROM users_main AS m
		JOIN permissions AS p ON p.ID=m.PermissionID
		WHERE p.DisplayStaff='1'
		ORDER BY p.Level, m.LastAccess ASC");
	$Staff = $DB->to_array();
	$Cache->cache_value('staff',array($FrontLineSupport,$Staff),180);
} else { list($FrontLineSupport,$Staff) = $Support; }
?>
<div class="thin">
	<h2><?=SITE_NAME?> Staff</h2>
	<div class="box pad" style="padding:0px 10px 10px 10px;">
		<h3>First-line Support</h3>
		<p><strong>These users are not official staff members</strong> - they're users who have volunteered their time to help people in need. Please treat them with respect and read <a href="wiki.php?action=article&amp;id=260">this</a> before contacting them. </p>
		<table width="100%">
			<tr class="colhead">
				<td style="width:130px;">Username</td>
				<td style="width:130px;">Last seen</td>
				<td><strong>Support for</strong></td>
			</tr>
<?
	$Row = 'a';
	foreach($FrontLineSupport as $Support) {
		list($ID, $Username, $Paranoia, $LastAccess, $SupportFor) = $Support;
		$Row = ($Row == 'a') ? 'b' : 'a';
?>
			<tr class="row<?=$Row?>">
				<td class="nobr">
					<?=format_username($ID, $Username)?>
				</td>
				<td class="nobr">
					<? if ($Paranoia < 5) { echo time_diff($LastAccess); } else { echo 'Hidden by user'; }?>
				</td>
				<td class="nobr">
					<?=$SupportFor?>
				</td>
			</tr>
<?	} ?>
		</table>
	</div>
<??>
	<div class="box pad" style="padding:0px 10px 10px 10px;">

<?
	$CurClass = 0;
	$CloseTable = false;
	foreach ($Staff as $StaffMember) {	
		list($ID, $Class, $ClassName, $Username, $Paranoia, $LastAccess) = $StaffMember;
		if($Class!=$CurClass) { // Start new class of staff members
			$Row = 'a';
			if($CloseTable) {
				$CloseTable = false;
				echo "\t</table>";
			}
			$CurClass = $Class;
			$CloseTable = true;
			echo '<h3>'.$ClassName.'s</h3>';
?>
		<table width="100%">
			<tr class="colhead">
				<td style="width:150px;">Username</td>
				<td style="width:400px;">Last seen</td>
			</tr>
<?
		} // End new class header
		
		// Display staff members for this class
		$Row = ($Row == 'a') ? 'b' : 'a';
?>
			<tr class="row<?=$Row?>">
				<td class="nobr">
					<?=format_username($ID, $Username)?>
				</td>
				<td class="nobr">
					<? if ($Paranoia < 5) { echo time_diff($LastAccess); } else { echo 'Hidden by staff member'; }?>
				</td>
			</tr>
<?	} ?>
		</table>
		
	</div>
</div>
<?
show_footer();
?>
