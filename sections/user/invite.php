<?
if(isset($_GET['userid']) && check_perms('users_view_invites')){
	if(!is_number($_GET['userid'])){ error(403); }
	
	$UserID=$_GET['userid'];
	$Sneaky = true;
} else {
	if(!$UserCount = $Cache->get_value('stats_user_count')){
		$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1'");
		list($UserCount) = $DB->next_record();
		$Cache->cache_value('stats_user_count', $UserCount, 0);
	}
	
	$UserID = $LoggedUser['ID'];
	$Sneaky = false;
}

list($UserID, $Username, $PermissionID) = array_values(user_info($UserID));


$DB->query("SELECT InviteKey, Email, Expires FROM invites WHERE InviterID='$UserID' ORDER BY Expires");
$Pending = 	$DB->to_array();

$DB->query("SELECT
	ID,
	Username,
	Donor,
	Warned,
	Enabled,
	PermissionID,
	Email,
	Uploaded,
	Downloaded,
	JoinDate,
	LastAccess
	FROM users_main as um
	LEFT JOIN users_info AS ui ON ui.UserID=um.ID
	WHERE ui.Inviter='$UserID'");
$Invited = $DB->to_array();

show_header('Invites');
show_message();
?>
<div class="thin">
	<h2><?=format_username($UserID,$Username)?> &gt; Invites</h2>
<?	show_message(); ?>
	<div class="linkbox">
		[<a href="user.php?action=invitetree<? if($Sneaky){ echo '&amp;userid='.$UserID; }?>">Invite tree</a>]
	</div>
<? if ($UserCount >= USER_LIMIT && !check_perms('site_can_invite_always')) { ?>
	<div class="box pad notice">
		<p>Because the user limit has been reached you are unable to send invites at this time.</p>
	</div>
<? }

if(!$Sneaky 
	&& !$LoggedUser['RatioWatch']
	&& empty($LoggedUser['DisableInvites'])
	&& ($LoggedUser['Invites']>0 || check_perms('site_send_unlimited_invites')) 
	&& ($UserCount <= USER_LIMIT || USER_LIMIT == 0 || check_perms('site_can_invite_always'))
	){ ?>
	<div class="box pad">
		<p>Please note that the selling, trading or public giving away of our invitations is strictly forbidden, and will result in you and your entire invite tree being banned. This includes offering to give away our invitations on any forum which is not a class-restricted forum on another private tracker.</p>
	</div>
	<div class="box pad">
		<form action="user.php" method="post">
			<input type="hidden" name="action" value="takeinvite" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
		<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
			<tr>
				<td class="label">Email:</td>
				<td>
					<input type="text" name="email" size="60" />
					<input type="submit" value="Invite" />
				</td>
			</tr>
			</table>
		</form>
	</div>
<?
}

if (!empty($Pending)) {
?>
	<h3>Pending invites</h3>
	<div class="box pad">
		<table width="100%">
			<tr class="colhead">
				<td>Email</td>
				<td>Expires in</td>
				<td>Delete invite</td>
			</tr>
<?
	$Row = 'a';
	foreach ($Pending as $Invite) {
		list($InviteKey, $Email, $Expires) = $Invite;
		$Row = ($Row == 'a') ? 'b' : 'a';
?>
			<tr class="row<?=$Row?>">
				<td><?=display_str($Email)?></td>
				<td><?=time_diff($Expires)?></td>
				<td><a href="user.php?action=deleteinvite&amp;invite=<?=$InviteKey?>&amp;auth=<?=$LoggedUser['AuthKey']?>" onclick="return confirm('Are you sure you want to delete this invite?');">Delete invite</a></td>
			</tr>
<?	} ?> 
		</table>
	</div>
<?
}

?> 
	<h3>Invitee list</h3>
	<div class="box pad">
		<table width="100%">
			<tr class="colhead">
				<td>Username</td>
				<td>Email</td>
				<td>Joined</td>
				<td>Last Seen</td>
				<td>Uploaded</td>
				<td>Downloaded</td>
				<td>Ratio</td>
			</tr>
<?
	$Row = 'a';
	foreach ($Invited as $User) {
		list($ID, $Username, $Donor, $Warned, $Enabled, $Class, $Email, $Uploaded, $Downloaded, $JoinDate, $LastAccess) = $User;
		$Row = ($Row == 'a') ? 'b' : 'a';
?> 
			<tr class="row<?=$Row?>">
				<td><?=format_username($ID, $Username, $Donor, $Warned, $Enabled == 2 ? false : true, $Class)?></td>
				<td><?=display_str($Email)?></td>
				<td><?=time_diff($JoinDate,1)?></td>
				<td><?=time_diff($LastAccess,1);?></td>
				<td><?=get_size($Uploaded)?></td>
				<td><?=get_size($Downloaded)?></td>
				<td><?=ratio($Uploaded, $Downloaded)?></td>
			</tr>
<? } ?>
		</table>
	</div>
</div>
<?
show_footer();
