<?
if (!check_perms('site_collages_recover')) {
		error(403);
}

if($_POST['collage_id'] && is_number($_POST['collage_id'])) {
	$CollageID = $_POST['collage_id'];
	$DB->query("UPDATE collages SET Deleted = '0' WHERE ID=$CollageID");
	$Cache->delete_value('collage_'.$CollageID);
	write_log("Collage ".$CollageID." was recovered by ".$LoggedUser['Username']);
	save_message("Collage recovered \o/");
	header("Location: collages.php?id=$CollageID");
}
show_header("Collage recovery!");
show_message();
?>
<div class="thin center">
	<div class="box" style="width:600px; margin:0px auto;">
		<div class="head colhead">
			Recover deleted collage
		</div>
		<div class="pad">
			<form action="collages.php" method="post">
				<input type="hidden" name="action" value="recover" />
				<strong>ID: </strong>
				<input type="text" name="collage_id" size="8" />
				<input value="Recover!" type="submit" />
			</form>
		</div>
	</div>
</div>
<?
show_footer();
?>
