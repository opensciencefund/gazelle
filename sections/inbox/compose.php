<?
if(empty($Return)) {
	$ToID = $_GET['to'];
	if($ToID == $LoggedUser['ID']) {
		error_message("You cannot start a conversation with yourself!");
		header('Location: inbox.php');
	}
}

if(!$ToID || !is_number($ToID)) { error(404); }
$DB->query("SELECT Username FROM users_main WHERE ID='$ToID'");
list($Username) = $DB->next_record();
if(!$Username) { error(404); }
show_header('Compose', 'inbox');
show_message();
?>
<div class="thin">
	<h2>Send a message to <?=$Username?></h2>
	<form action="inbox.php" method="post" id="messageform">
		<div class="box pad">
			<input type="hidden" name="action" value="takecompose" />
			<input type="hidden" name="toid" value="<?=$ToID?>" />
			<div id="quickpost">
				<h3>Subject</h3>
				<input type="text" name="subject" size="95" value="<?=(!empty($Subject) ? $Subject : '')?>"/><br />
				<h3>Body</h3>
				<textarea id="body" name="body"  cols="95"  rows="10"><?=(!empty($Body) ? $Body : '')?></textarea>
			</div>
			<div id="preview" class="hidden"></div>
			<div id="buttons" class="center">
				<input type="button" value="Preview" onclick="Quick_Preview();" /> 
				<input type="submit" value="Send message" />
			</div>
		</div>
	</form>
</div>

<?
show_footer();
?>
