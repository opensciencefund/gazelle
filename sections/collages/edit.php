<?
$CollageID = $_GET['collageid'];
if(!is_number($CollageID)) { error(0); }

$DB->query("SELECT Name, Description FROM collages WHERE ID='$CollageID'");
list($Name, $Description) = $DB->next_record();
show_header('Edit collage');
?>
<div class="thin">
	<h2>Edit collage <a href="collages.php?id=<?=$CollageID?>"><?=$Name?></a></h2>
	<form action="collages.php" method="post">
		<table id="edit_collage">
<? if (check_perms('site_collages_delete')) { ?>
			<tr>
				<td class="label">Name</td>
				<td><input type="text" name="name" value="<?=$Name?>" /></td>
			</tr>
<? } ?>
			<tr>
				<td class="label">Description</td>
				<td>
					<input type="hidden" name="action" value="edit_handle" />
					<input type="hidden" name="collageid" value="<?=$CollageID?>" />
					<textarea name="description" id="description" cols="60" rows="10"><?=$Description?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="center"><input type="submit" value="Edit collage" /></td>
			</tr>
		</table>
	</form>
</div>
<? show_footer(); ?>
