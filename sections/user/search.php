<?
/**********************************************************************
 *>>>>>>>>>>>>>>>>>>>>>>>>>>> User search <<<<<<<<<<<<<<<<<<<<<<<<<<<<*
 **********************************************************************/

if (!empty($_GET['search'])) {
	if (preg_match('/^[a-z0-9_?]{1,20}$/iD',$_GET['search'])) {
		$DB->query("SELECT ID, Enabled FROM users_main WHERE Username='".db_string($_GET['search'])."'");
		
	}
	$_GET['username'] = $_GET['search'];
}
 
define('USERS_PER_PAGE', 30);

if(isset($_GET['username'])){
	$_GET['username'] = trim($_GET['username']);
	// form submitted
	$Val->SetFields('username','1','username','Please enter a username.');
	$Err = $Val->ValidateForm($_GET);
	
	if(!$Err){
		// Passed validation. Let's rock.
		list($Page,$Limit) = page_limit(USERS_PER_PAGE);
		$Results = $DB->query("SELECT SQL_CALC_FOUND_ROWS
			ID,
			Username,
			Enabled,
			PermissionID,
			Donor,
			Warned
			FROM users_main AS um
			JOIN users_info AS ui ON ui.UserID=um.ID
			WHERE Username LIKE '%".db_string($_GET['username'])."%'
			ORDER BY Username
			LIMIT $Limit");
		$DB->query('SELECT FOUND_ROWS();');
		list($NumResults) = $DB->next_record();
		$DB->set_query_id($Results);
	}

}
show_header('User search');
?>
<div class="thin">
	<h3>Search results</h3>
	<div class="linkbox">
<?
$Pages=get_pages($Page,$NumResults,USERS_PER_PAGE,9);
echo $Pages;
?>
	</div>
	<form action="user.php" method="get">
	<input type="hidden" name="action" value="search" />
		<table width="100%">
			<tr>
				<td class="label nobr">Username:</td>
				<td>
					<input type="text" name="username" size="60" value="<?=display_str($_GET['username'])?>" />
					&nbsp;
					<input type="submit" value="Search users" />
				</td>
			</tr>
		</table>
	</form>
	<br />
	<div class="box pad center">
		<table style="width:400px;margin:0px auto;">
			<tr class="colhead">
				<td width="50%">Username</td>
				<td>Class</td>
			</tr>
<? while(list($UserID, $Username, $Enabled, $PermissionID, $Donor, $Warned) = $DB->next_record()){ ?>
			<tr>
				<td><?=format_username($UserID, $Username, $Donor, $Warned, $Enabled == 2 ? false : true);?></td>
				<td><?=make_class_string($PermissionID);?></td>
			</tr>
<? } ?>
		</table>
	</div>
	<div class="linkbox">
	<?=$Pages?>
	</div>
</div>




<?
show_footer();
?>
