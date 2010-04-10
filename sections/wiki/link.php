<?
show_header('Link an article');
show_message();
?>
<div class="thin">
	<div class="box pad">
		<form action="wiki.php" method="post">
			<div>
				<p>Paste a wiki link into the box bellow to link this search string or article name to the appropriate article.</p>
				<input type="hidden" name="action" value="link" />
				<strong>Link </strong> <input type="text" name="alias" size="20" value="<?=display_str($Alias->convert($_GET['alias']))?>" />
				to <strong>URL</strong> <input type="text" name="url" size="50" maxlength="150" />
				<input type="submit" value="Submit" />
			</div>
		</form>
	</div>
</div>
<? show_footer(); ?>
