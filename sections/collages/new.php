<?
show_header('Create a collage');
show_message();
?>
<div class="thin">
	<form action="collages.php" method="post">
		<table id="new_collage">
			<tr>
				<td class="label"><strong>Name</strong></td>
				<td>
					<input type="hidden" name="action" value="new_handle" />
					<input type="text" id="name" name="name" size="60" />
				</td>
			</tr>
			<tr>
				<td class="label">Description</td>
				<td>
					<textarea name="description" id="description" cols="60" rows="10"></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="center">
					<strong>Please ensure your collage will be allowed under the <a href="rules.php?p=collages">rules</a></strong>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="center"><input type="submit" value="Create collage" /></td>
			</tr>
		</table>
	</form>
</div>
<? show_footer(); ?>
