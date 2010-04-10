<?
include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

$ConvID = $_GET['id'];
if(!$ConvID || !is_number($ConvID)) { error(404); }



$UserID = $LoggedUser['ID'];
$DB->query("SELECT UserID FROM pm_conversations_users WHERE UserID='$UserID' AND ConvID='$ConvID'");
if($DB->record_count() == 0) {
	error(403);
}


// Get information on the conversation
$DB->query("SELECT
	c.Subject,
	cu1.Sticky,
	cu1.UnRead,
	cu1.UserID AS u1ID,
	um1.Username AS u1Username,
	um1.PermissionID AS u1Class,
	um1.Enabled AS u1Enabled,
	ui1.Donor AS u1Donor,
	ui1.Warned AS u1Warned,
	cu2.UserID AS u2ID,
	um2.Username AS u2Username,
	um2.PermissionID AS u2Class,
	um2.Enabled AS u2Enabled,
	ui2.Donor AS u2Donor,
	ui2.Warned AS u2Warned
	FROM pm_conversations AS c
	JOIN pm_conversations_users AS cu1 ON cu1.UserID='$UserID' AND cu1.ConvID=c.ID
	LEFT JOIN users_main AS um1 ON um1.ID='$UserID'
	LEFT JOIN users_info AS ui1 ON ui1.UserID='$UserID'
	LEFT JOIN pm_conversations_users AS cu2 ON cu2.UserID!='$UserID' AND cu2.ConvID=c.ID
	LEFT JOIN users_main AS um2 ON um2.ID=cu2.UserID
	LEFT JOIN users_info AS ui2 ON ui2.UserID=cu2.UserID
	WHERE c.ID='$ConvID'");

$A = $DB->next_record(); // A = Array
$Subject = $A['Subject'];
$Sticky = $A['Sticky'];
$UnRead = $A['UnRead'];

list($User1ID, $User1Name, $User1Class, $User1Enabled, $User1Donor, $User1Warned) = array($A['u1ID'], $A['u1Username'], $A['u1Class'], $A['u1Enabled'], $A['u1Donor'], $A['u1Warned']);
list($User2ID, $User2Name, $User2Class, $User2Enabled, $User2Donor, $User2Warned) = array($A['u2ID'], $A['u2Username'], $A['u2Class'], $A['u2Enabled'], $A['u2Donor'], $A['u2Warned']);

$Users = array();
$Users[$User1ID]['UserStr'] = format_username($User1ID, $User1Name, $User1Donor , $User1Warned, $User1Enabled == 2 ? false : true, $User1Class);
$Users[$User1ID]['Username'] = $User1Name;
$Users[$User2ID]['UserStr'] = format_username($User2ID, $User2Name, $User2Donor , $User2Warned, $User2Enabled == 2 ? false : true, $User2Class);
$Users[$User2ID]['Username'] = $User2Name;

$Users[0]['UserStr'] = 'System'; // in case it's a message from the system
$Users[0]['Username'] = 'System';



if($UnRead=='1') {

	$DB->query("UPDATE pm_conversations_users SET UnRead='0' WHERE ConvID='$ConvID' AND UserID='$UserID'");
	// Clear the caches of the inbox and sentbox
	$Cache->decrement('inbox_new_'.$UserID);
}

show_header('View conversation '.$Subject, 'comments,inbox');

// Get messages
$DB->query("SELECT SentDate, SenderID, Body, ID FROM pm_messages AS m WHERE ConvID='$ConvID' ORDER BY ID");
?>
<div class="thin">
	<h2><?=$Subject?></h2>
	<div class="linkbox">
		<a href="inbox.php">[Back to inbox]</a>
	</div>
<? while(list($SentDate, $SenderID, $Body, $MessageID) = $DB->next_record()) { ?>
	<div class="box vertical_space">
		<div class="head">
			By <strong><?=$Users[$SenderID]['UserStr']?></strong> <?=time_diff($SentDate)?> - <a href="#quickpost" onclick="Quote('<?=$MessageID?>','<?=$Users[$SenderID]['Username']?>');">[Quote]</a>	
		</div>
		<div class="body" id="message<?=$MessageID?>"><?=$Text->full_format($Body)?></div>
	</div>
<? }

?>
	<h2>Reply</h2>
	<form action="inbox.php" method="post" id="messageform">
		<div class="box pad">
			<input type="hidden" name="action" value="takecompose" />
			<input type="hidden" name="toid" value="<?=$User2ID?>" />
			<input type="hidden" name="convid" value="<?=$ConvID?>" />
			<textarea id="quickpost" name="body" cols="90" rows="10"></textarea> <br />
			<div id="preview" class="box vertical_space body hidden"></div>
			<div id="buttons" class="center">
				<input type="button" value="Preview" onclick="Quick_Preview();" /> 
				<input type="submit" value="Send message" />
			</div>
		</div>
	</form>
	<h2>Manage conversation</h2>
	<form action="inbox.php" method="post">
		<div class="box pad">
			<input type="hidden" name="action" value="takeedit" />
			<input type="hidden" name="convid" value="<?=$ConvID?>" />

			<table width="100%">
				<tr>
					<td class="label">Sticky</td>
					<td>
						<input type="checkbox" name="sticky"<? if($Sticky) { echo ' checked="checked"'; } ?> />
					</td>
					<td class="label">Mark as unread</td>
					<td>
						<input type="checkbox" name="mark_unread" />
					</td>
					<td class="label">Delete conversation</td>
					<td>
						<input type="checkbox" name="delete" />
					</td>

				</tr>
				<tr>
					<td class="center" colspan="6"><input type="submit" value="Manage conversation" /></td>
				</tr>
			</table>
		</div>
	</form>
<?

//And we're done!
?>
</div>
<?
show_footer();
?>
