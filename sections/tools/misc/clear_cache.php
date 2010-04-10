<?
if(!check_perms('admin_clear_cache')) {
	error(403);
}

show_header('Clear a cache key');

//Make sure the form was sent
if(!empty($_GET['key']) && $_GET['type'] == "clear") {
	$Cache->delete_value($_GET['key']);
	echo '<div class="save_message">Key '.display_str($_GET['key']).' cleared!</div>';

}
?>
	<h2>Clear a cache key</h2>
	
	<form method="get" action="" name="clear_cache">
		<input type="hidden" name="action" value="clear_cache" />
		<table cellpadding="2" cellspacing="1" border="0" align="center">
			<tr valign="top">
				<td align="right">Key</td>
				<td align="left">
					<input type="text" name="key" id="key" class="inputtext" />
					<select name="type">
						<option value="view">View</option>
						<option value="clear">Clear</option>
					</select>
					<input type="submit" value="key" class="submit" />
				</td>
			</tr>
		</table>
	</form>
<?

if(!empty($_GET['key']) && $_GET['type'] == "view") {
	$Value = $Cache->get_value($_GET['key']);
	if(is_array($Value)) {
?>
	<div><?=display_array($Value)?></div>
<?
	} else if(!empty($Value)) {
?>
	<div><?=display_str($Value)?></div>
<?
	} else {
?>
	<div>Empty key or not found!</div>
<?
	}
}

//Show the footer
show_footer();

?>
