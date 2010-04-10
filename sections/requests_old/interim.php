<?
if(!isset($_GET['id']) || !is_number($_GET['id'])) { error(404); }
if($LoggedUser['ID'] != $UserID && $LoggedUser['ID'] != $FillerID && !check_perms('site_moderate_requests')) { error(403); }

$Action = $_GET['action'];
if($Action != "unfill" && $Action != "delete") {
	error(404);
}

show_header();
?>
<div class="thin center">
	<div class="box" style="width:600px; margin:0px auto;">
		<div class="head colhead">
			<?=ucwords($Action)?> Request
		</div>
		<div class="pad">
			<form action="requests.php" method="post">
				<input type="hidden" name="action" value="take<?=$Action?>" />
				<input type="hidden" name="id" value="<?=$_GET['id']?>" />
				<strong>Reason:</strong>
				<input type="text" name="reason" size="30" />
				<input value="<?=ucwords($Action)?>" type="submit" />
			</form>
		</div>
	</div>
</div>
<?
show_footer();
?>