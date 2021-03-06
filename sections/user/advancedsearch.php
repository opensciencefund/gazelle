<?
/**********************************************************************
 *>>>>>>>>>>>>>>>>>>>>>>>>>>> User search <<<<<<<<<<<<<<<<<<<<<<<<<<<<*
 * Best viewed with a wide screen monitor							 *
 **********************************************************************/
if (!empty($_GET['search'])) {
	if (preg_match("/^".IP_REGEX."$/", $_GET['search'])) {
		$_GET['ip'] = $_GET['search'];
	} elseif (preg_match("/^".EMAIL_REGEX."$/i", $_GET['search'])) {
		$_GET['email'] = $_GET['search'];
	} elseif (preg_match('/^[a-z0-9_?]{1,20}$/iD',$_GET['search'])) {
		$DB->query("SELECT ID FROM users_main WHERE Username='".db_string($_GET['search'])."'");
		if (list($ID) = $DB->next_record()) {
			header('Location: user.php?id='.$ID);
			die();
		}
		$_GET['username'] = $_GET['search'];
	} else {
		$_GET['comment'] = $_GET['search'];
	}
}
 
define('USERS_PER_PAGE', 30);

function wrap($String, $ForceMatch = ''){
	if(!$ForceMatch){
		global $Match;
	} else {
		$Match = $ForceMatch;
	}
	if($Match == ' REGEXP '){
		if(strpos($String, '\'') || preg_match('/^.*\\\\$/i', $String)){
			error('Regex contains illegal characters.');
		}
	} else {
		$String = db_string($String);
	}
	if($Match == ' LIKE '){
		// Fuzzy search
		// Stick in wildcards at beginning and end of string unless string starts or ends with |
		if($String[0] != '|'){
			$String = '%'.$String;
		} else {
			$String = substr($String, 1, strlen($String));
		}

		if(substr($String, -1, 1) != '|'){
			$String = $String.'%';
		} else {
			$String = substr($String, 0, -1);
		}

	}
	$String="'$String'";
	return $String;
}

function date_compare($Field, $Operand, $Date1, $Date2 = ''){
	$Date1 = db_string($Date1);
	$Date2 = db_string($Date2);
	$Return = array();

	switch($Operand){
		case 'on':
			$Return []= " $Field>='$Date1 00:00:00' ";
			$Return []= " $Field<='$Date1 23:59:59' ";
			break;
		case 'before':
			$Return []= " $Field<'$Date1 00:00:00' ";
			break;
		case 'after':
			$Return []= " $Field>'$Date1 23:59:59' ";
			break;
		case 'between':
			$Return []= " $Field>='$Date1 00:00:00' ";
			$Return []= " $Field<='$Date2 00:00:00' ";
			break;
	}

	return $Return;
}


function num_compare($Field, $Operand, $Num1, $Num2 = ''){
	
	if($Num1!=0){
		$Num1 = db_string($Num1);
	}
	if($Num2!=0){
		$Num2 = db_string($Num2);
	}
	
	$Return = array();

	switch($Operand){
		case 'equal':
			$Return []= " $Field='$Num1' ";
			break;
		case 'above':
			$Return []= " $Field>'$Num1' ";
			break;
		case 'below':
			$Return []= " $Field<'$Num1' ";
			break;
		case 'between':
			$Return []= " $Field>'$Num1' ";
			$Return []= " $Field<'$Num2' ";
			break;
		default:
			print_r($Return);
			die();
	}
	return $Return;
}

// Arrays, regexes, and all that fun stuff we can use for validation, form generation, etc

$DateChoices = array('inarray'=>array('on', 'before', 'after', 'between'));
$SingleDateChoices = array('inarray'=>array('on', 'before', 'after'));
$NumberChoices = array('inarray'=>array('equal', 'above', 'below', 'between', 'buffer'));
$YesNo = array('inarray'=>array('any', 'yes', 'no'));
$OrderVals = array('inarray'=>array('Username', 'Ratio', 'IP', 'Email', 'Joined', 'Last Seen', 'Uploaded', 'Downloaded', 'Invites', 'Snatches'));
$WayVals = array('inarray'=>array('Ascending', 'Descending'));

if(count($_GET)){

	$DateRegex = array('regex'=>'/\d{4}-\d{2}-\d{2}/');

	$ClassIDs = array();
	foreach ($Classes as $ClassID => $Value) {
		$ClassIDs[]=$ClassID;
	}

	$Val->SetFields('comment','0','string','Comment is too long.', array('maxlength'=>512));
	$Val->SetFields('disabled_invites', '0', 'inarray', 'Invalid disabled_invites field', $YesNo);


	$Val->SetFields('joined', '0', 'inarray', 'Invalid joined field', $DateChoices);
	$Val->SetFields('join1', '0', 'regex', 'Invalid join1 field', $DateRegex);
	$Val->SetFields('join2', '0', 'regex', 'Invalid join2 field', $DateRegex);

	$Val->SetFields('lastactive', '0', 'inarray', 'Invalid lastactive field', $DateChoices);
	$Val->SetFields('lastactive1', '0', 'regex', 'Invalid lastactive1 field', $DateRegex);
	$Val->SetFields('lastactive2', '0', 'regex', 'Invalid lastactive2 field', $DateRegex);

	$Val->SetFields('ratio', '0', 'inarray', 'Invalid ratio field', $NumberChoices);
	$Val->SetFields('uploaded', '0', 'inarray', 'Invalid uploaded field', $NumberChoices);
	$Val->SetFields('downloaded', '0', 'inarray', 'Invalid downloaded field', $NumberChoices);
	$Val->SetFields('snatched', '0', 'inarray', 'Invalid snatched field', $NumberChoices);

	$Val->SetFields('matchtype', '0', 'inarray', 'Invalid matchtype field', array('inarray'=>array('strict', 'fuzzy', 'regex')));


	$Val->SetFields('enabled', '0', 'inarray', 'Invalid enabled field', array('inarray'=>array('', 0, 1, 2)));
	$Val->SetFields('class', '0', 'inarray', 'Invalid class', array('inarray'=>$ClassIDs));
	$Val->SetFields('donor', '0', 'inarray', 'Invalid donor field', $YesNo);
	$Val->SetFields('warned', '0', 'inarray', 'Invalid warned field', $YesNo);
	$Val->SetFields('disabled_uploads', '0', 'inarray', 'Invalid disabled_uploads field', $YesNo);

	$Val->SetFields('order', '0', 'inarray', 'Invalid ordering', $OrderVals);
	$Val->SetFields('way', '0', 'inarray', 'Invalid way', $WayVals);
	
	$Val->SetFields('passkey', '0', 'string', 'Invalid passkey', array('maxlength'=>32));

	$Err = $Val->ValidateForm($_GET);

	if(!$Err){
		// Passed validation. Let's rock.
		$RunQuery = false; // if we should run the search

		if(isset($_GET['matchtype']) && $_GET['matchtype'] == 'strict'){
			$Match = ' = ';
		} elseif(isset($_GET['matchtype']) && $_GET['matchtype'] == 'regex') {
			$Match = ' REGEXP ';
		} else {
			$Match = ' LIKE ';
		}

		$OrderTable = array('Username'=>'um1.Username', 'Joined'=>'ui1.JoinDate', 'Email'=>'um1.Email', 'IP'=>'um1.IP', 'Last Seen'=>'um1.LastAccess', 'Uploaded'=>'um1.Uploaded', 'Downloaded'=>'um1.Downloaded', 'Ratio'=>'(um1.Uploaded/um1.Downloaded)', 'Invites'=>'um1.Invites', 'Snatches'=>'Snatches');

		$WayTable = array('Ascending'=>'ASC', 'Descending'=>'DESC');

		$Where = array();
		$Having = array();
		$Join = array();
		$Group = array();
		$Distinct = '';
		$Order = '';


		$SQL = 'SQL_CALC_FOUND_ROWS
			um1.ID,
			um1.Username,
			um1.Uploaded,
			um1.Downloaded,
			(SELECT COUNT(uid) FROM xbt_snatched AS xs WHERE xs.uid=um1.ID) AS Snatches,
			um1.PermissionID,
			um1.Email,
			um1.Enabled,
			um1.IP,
			um1.Invites,
			ui1.DisableInvites,
			ui1.Warned,
			ui1.Donor,
			ui1.JoinDate,
			um1.LastAccess
			FROM users_main AS um1 JOIN users_info AS ui1 ON ui1.UserID=um1.ID ';


		if(!empty($_GET['username'])){
			$Where[]='um1.Username'.$Match.wrap($_GET['username']);
		}

		if(!empty($_GET['email'])){
			if(isset($_GET['email_history'])){
				$Distinct = 'DISTINCT ';
				$Join[]=' JOIN users_history_emails AS he ON he.UserID=um1.ID ';
				$Where[]= ' he.Email '.$Match.wrap($_GET['email']);
			} else {
				$Where[]='um1.Email'.$Match.wrap($_GET['email']);
			}
		}

		if(!empty($_GET['ip'])){
			if(isset($_GET['ip_history'])){
				$Distinct = 'DISTINCT ';
				$Join[]=' JOIN users_history_ips AS hi ON hi.UserID=um1.ID ';
				$Where[]= ' hi.IP '.$Match.wrap($_GET['ip']);
			} else {
				$Where[]='um1.IP'.$Match.wrap($_GET['ip']);
			}
		}

		if(!empty($_GET['tracker_ip'])){
				$Distinct = 'DISTINCT ';
				$Join[]=' JOIN xbt_files_users AS xfu ON um1.ID=xfu.uid ';
				$Where[]= ' xfu.ipa '.$Match.wrap((int) ip2long($_GET['tracker_ip']));
		}
		
		
		if(!empty($_GET['comment'])){
			$Where[]='ui1.AdminComment'.$Match.wrap($_GET['comment']);
		}
		
		
		if(strlen($_GET['invites1'])){
			$Invites1 = round($_GET['invites1']);
			$Invites2 = round($_GET['invites2']);
			$Where[]=implode(' AND ', num_compare('Invites', $_GET['invites'], $Invites1, $Invites2));
		}

		if($_GET['disabled_invites'] == 'yes'){
			$Where[]='ui1.DisableInvites=\'1\'';
		} elseif($_GET['disabled_invites'] == 'no'){
			$Where[]='ui1.DisableInvites=\'0\'';
		}

		if($_GET['disabled_uploads'] == 'yes'){
			$Where[]='ui1.DisableUpload=\'1\'';
		} elseif($_GET['disabled_uploads'] == 'no'){
			$Where[]='ui1.DisableUpload=\'0\'';
		}

		if($_GET['join1']){
			$Where[]=implode(' AND ', date_compare('ui1.JoinDate', $_GET['joined'], $_GET['join1'], $_GET['join2']));
		}

		if($_GET['lastactive1']){
			$Where[]=implode(' AND ', date_compare('um1.LastAccess', $_GET['lastactive'], $_GET['lastactive1'], $_GET['lastactive2']));
		}

		if($_GET['ratio1']){
			$Decimals = strlen(array_pop(explode('.', $_GET['ratio1'])));
			if(!$Decimals){ $Decimals = 0; }

			$Where[]=implode(' AND ', num_compare("ROUND(Uploaded/Downloaded,$Decimals)", $_GET['ratio'], $_GET['ratio1'], $_GET['ratio2']));
		}

		if(strlen($_GET['uploaded1'])){
			$Upload1 = round($_GET['uploaded1']);
			$Upload2 = round($_GET['uploaded2']);
			if($_GET['uploaded']!='buffer'){
				$Where[]=implode(' AND ', num_compare('ROUND(Uploaded/1024/1024/1024)', $_GET['uploaded'], $Upload1, $Upload2));
			} else {
				$Where[]=implode(' AND ', num_compare('ROUND((Uploaded/1024/1024/1024)-(Downloaded/1024/1024/1023))', 'between', $Upload1*0.9, $Upload1*1.1));
			}
		}

		if(strlen($_GET['downloaded1'])){
			$Download1 = round($_GET['downloaded1']);
			$Download2 = round($_GET['downloaded2']);
			$Where[]=implode(' AND ', num_compare('ROUND(Downloaded/1024/1024/1024)', $_GET['downloaded'], $Download1, $Download2));
		}
		
		if(strlen($_GET['snatched1'])){
			$Snatched1 = round($_GET['snatched1']);
			$Snatched2 = round($_GET['snatched2']);
			$Having[]=implode(' AND ', num_compare('Snatches', $_GET['snatched'], $Snatched1, $Snatched2));
		}
		
		if($_GET['enabled']!=''){
			$Where[]='um1.Enabled='.wrap($_GET['enabled'], '=');
		}

		if($_GET['class']!=''){
			$Where[]='um1.PermissionID='.wrap($_GET['class'], '=');
		}

		if($_GET['donor'] == 'yes'){
			$Where[]='ui1.Donor=\'1\'';
		} elseif($_GET['donor'] == 'no'){
			$Where[]='ui1.Donor=\'0\'';
		}

		if($_GET['warned'] == 'yes'){
			$Where[]='ui1.Warned!=\'0000-00-00 00:00:00\'';
		} elseif($_GET['warned'] == 'no'){
			$Where[]='ui1.Warned=\'0000-00-00 00:00:00\'';
		}

		if($_GET['disabled_ip']){
			$Distinct = 'DISTINCT ';
			if($_GET['ip_history']){
				if(count($Join) == 0){
					$Join[]=' JOIN users_history_ips AS hi ON hi.UserID=um1.ID ';
				}
				$Join[]=' JOIN users_history_ips AS hi2 ON hi2.IP=hi.IP ';
				$Join[]=' JOIN users_main AS um2 ON um2.ID=hi2.UserID AND um2.Enabled=\'2\' ';
			} else {
				$Join[]=' JOIN users_main AS um2 ON um2.IP=um1.IP AND um2.Enabled=\'2\' ';
			}
		}
		
		if(!empty($_GET['passkey'])){
			$Where[]='um1.torrent_pass'.$Match.wrap($_GET['passkey']);
		}

		if($OrderTable[$_GET['order']] && $WayTable[$_GET['way']]){
			$Order = ' ORDER BY '.$OrderTable[$_GET['order']].' '.$WayTable[$_GET['way']].' ';
		}

		//---------- Finish generating the search string

		$SQL = 'SELECT '.$Distinct.$SQL;
		$SQL .= implode(' ', $Join);
		
		if(count($Where)){
			$SQL .= ' WHERE '.implode(' AND ', $Where);
		}
		
		if(count($Having)){
			$SQL .= ' HAVING '.implode(' AND ', $Having);
		}
		$SQL .= $Order;
		
		if(count($Where)>0 || count($Join)>0 || count($Having)>0){
			$RunQuery = true;
		}

		list($Page,$Limit) = page_limit(USERS_PER_PAGE);
		$SQL.=" LIMIT $Limit";
	} else { error($Err); }

}
show_header('User search');
?>
<div class="thin">
	<form action="user.php" method="get">
		<input type="hidden" name="action" value="search" />
		<table>
			<tr>
				<td class="label nobr">Username:</td>
				<td width="24%">
					<input type="text" name="username" size="20" value="<?=display_str($_GET['username'])?>" />
				</td>
				<td class="label nobr">Joined:</td>
				<td width="24%" class="nobr">
					<select name="joined">
						<option value="on"<? if($_GET['joined']==='on'){echo ' selected="selected"';}?>>On</option>
						<option value="before"<? if($_GET['joined']==='before'){echo ' selected="selected"';}?>>Before</option>
						<option value="after"<? if($_GET['joined']==='after'){echo ' selected="selected"';}?>>After</option>
						<option value="between"<? if($_GET['joined']==='between'){echo ' selected="selected"';}?>>Between</option>
					</select>
					<input type="text" name="join1" size="6" value="<?=display_str($_GET['join1'])?>" />
					<input type="text" name="join2" size="6" value="<?=display_str($_GET['join2'])?>" />
				</td>
				<td class="label nobr">Enabled:</td>
				<td>
					<select name="enabled">
						<option value="" <? if($_GET['enabled']==='') {echo ' selected="selected"';}?>>Any</option>
						<option value="0"<? if($_GET['enabled']==='0'){echo ' selected="selected"';}?>>Unconfirmed</option>
						<option value="1"<? if($_GET['enabled']==='1'){echo ' selected="selected"';}?>>Enabled</option>
						<option value="2"<? if($_GET['enabled']==='2'){echo ' selected="selected"';}?>>Disabled</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label nobr">Email:</td>
				<td>
					<input type="text" name="email" size="20" value="<?=display_str($_GET['email'])?>" />
				</td>
				<td class="label nobr">Last active:</td>
				<td width="30%">
					<select name="lastactive">
						<option value="on"<? if($_GET['lastactive']==='on'){echo ' selected="selected"';}?>>On</option>
						<option value="before"<? if($_GET['lastactive']==='before'){echo ' selected="selected"';}?>>Before</option>
						<option value="after"<? if($_GET['lastactive']==='after'){echo ' selected="selected"';}?>>After</option>
						<option value="between"<? if($_GET['lastactive']==='between'){echo ' selected="selected"';}?>>Between</option>
					</select>
					<input type="text" name="lastactive1" size="6" value="<?=display_str($_GET['lastactive1'])?>" />
					<input type="text" name="lastactive2" size="6" value="<?=display_str($_GET['lastactive2'])?>" />
				</td>
				<td class="label nobr">Class:</td>
				<td>
					<select name="class">
						<option value="" <? if($_GET['class']==='') {echo ' selected="selected"';}?>>Any</option>
<?	foreach($ClassLevels as $Class){ ?>
					<option value="<?=$Class['ID'] ?>" <? if($_GET['class']===$Class['ID']) {echo ' selected="selected"';}?>><?=cut_string($Class['Name'], 10, 1, 1).' ('.$Class['Level'].')'?></option>
<?	} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label nobr">IP:</td>
				<td>
					<input type="text" name="ip" size="20" value="<?=display_str($_GET['ip'])?>" />
				</td>
				<td class="label nobr">Ratio:</td>
				<td width="30%">
					<select name="ratio">
						<option value="equal"<? if($_GET['ratio']==='equal'){echo ' selected="selected"';}?>>Equal</option>
						<option value="above"<? if($_GET['ratio']==='above'){echo ' selected="selected"';}?>>Above</option>
						<option value="below"<? if($_GET['ratio']==='below'){echo ' selected="selected"';}?>>Below</option>
						<option value="between"<? if($_GET['ratio']==='between'){echo ' selected="selected"';}?>>Between</option>
					</select>
					<input type="text" name="ratio1" size="6" value="<?=display_str($_GET['ratio1'])?>" />
					<input type="text" name="ratio2" size="6" value="<?=display_str($_GET['ratio2'])?>" />
				</td>
				<td class="label nobr">Donor:</td>
				<td>
					<select name="donor">
						<option value="" <? if($_GET['donor']==='') {echo ' selected="selected"';}?>>Any</option>
						<option value="yes" <? if($_GET['donor']==='yes') {echo ' selected="selected"';}?>>Yes</option>
						<option value="no" <? if($_GET['donor']==='no') {echo ' selected="selected"';}?>>No</option>
					</select>
				</td>

			</tr>
			<tr>
				<td class="label nobr">Comment:</td>
				<td>
					<input type="text" name="comment" size="20" value="<?=display_str($_GET['comment'])?>" />
				</td>
				<td class="label nobr">Uploaded:</td>
				<td width="30%">
					<select name="uploaded">
						<option value="equal"<? if($_GET['uploaded']==='equal'){echo ' selected="selected"';}?>>Equal</option>
						<option value="above"<? if($_GET['uploaded']==='above'){echo ' selected="selected"';}?>>Above</option>
						<option value="below"<? if($_GET['uploaded']==='below'){echo ' selected="selected"';}?>>Below</option>
						<option value="between"<? if($_GET['uploaded']==='between'){echo ' selected="selected"';}?>>Between</option>
						<option value="buffer"<? if($_GET['uploaded']==='buffer'){echo ' selected="selected"';}?>>Buffer</option>
					</select>
					<input type="text" name="uploaded1" size="6" value="<?=display_str($_GET['uploaded1'])?>" />
					<input type="text" name="uploaded2" size="6" value="<?=display_str($_GET['uploaded2'])?>" />
				</td>
				<td class="label nobr">Warned:</td>
				<td>
					<select name="warned">
						<option value="" <? if($_GET['warned']==='') {echo ' selected="selected"';}?>>Any</option>
						<option value="yes" <? if($_GET['warned']==='yes') {echo ' selected="selected"';}?>>Yes</option>
						<option value="no" <? if($_GET['warned']==='no') {echo ' selected="selected"';}?>>No</option>
					</select>
				</td>

			</tr>
			<tr>
				<td class="label nobr">Invites:</td>
				<td>
					<select name="invites">
						<option value="equal"<? if($_GET['invites']==='equal'){echo ' selected="selected"';}?>>Equal</option>
						<option value="above"<? if($_GET['invites']==='above'){echo ' selected="selected"';}?>>Above</option>
						<option value="below"<? if($_GET['invites']==='below'){echo ' selected="selected"';}?>>Below</option>
						<option value="between"<? if($_GET['invites']==='between'){echo ' selected="selected"';}?>>Between</option>
					</select>
					<input type="text" name="invites1" size="6" value="<?=display_str($_GET['invites1'])?>" />
					<input type="text" name="invites2" size="6" value="<?=display_str($_GET['invites2'])?>" />

				</td>
				<td class="label nobr">Downloaded:</td>
				<td width="30%">
					<select name="downloaded">
						<option value="equal"<? if($_GET['downloaded']==='equal'){echo ' selected="selected"';}?>>Equal</option>
						<option value="above"<? if($_GET['downloaded']==='above'){echo ' selected="selected"';}?>>Above</option>
						<option value="below"<? if($_GET['downloaded']==='below'){echo ' selected="selected"';}?>>Below</option>
						<option value="between"<? if($_GET['downloaded']==='between'){echo ' selected="selected"';}?>>Between</option>
					</select>
					<input type="text" name="downloaded1" size="6" value="<?=display_str($_GET['downloaded1'])?>" />
					<input type="text" name="downloaded2" size="6" value="<?=display_str($_GET['downloaded2'])?>" />
				</td>
				<td class="label nobr">Disabled IP:</td>
				<td>
					<input type="checkbox" name="disabled_ip" <? if($_GET['disabled_ip']){ echo ' checked="checked"'; }?> />
				</td>

			</tr>
			<tr>
				<td class="label nobr">Disabled invites</td>
				<td>
					<select name="disabled_invites">
						<option value="" <? if($_GET['disabled_invites']==='') {echo ' selected="selected"';}?>>Any</option>
						<option value="yes" <? if($_GET['disabled_invites']==='yes') {echo ' selected="selected"';}?>>Yes</option>
						<option value="no" <? if($_GET['disabled_invites']==='no') {echo ' selected="selected"';}?>>No</option>
					</select>
				</td>
				<td class="label nobr">Snatched:</td>
				<td width="30%">
					<select name="snatched">
						<option value="equal"<? if(isset($_GET['snatched']) && $_GET['snatched']==='equal'){echo ' selected="selected"';}?>>Equal</option>
						<option value="above"<? if(isset($_GET['snatched']) && $_GET['snatched']==='above'){echo ' selected="selected"';}?>>Above</option>
						<option value="below"<? if(isset($_GET['snatched']) && $_GET['snatched']==='below'){echo ' selected="selected"';}?>>Below</option>
						<option value="between"<? if(isset($_GET['snatched']) && $_GET['snatched']==='between'){echo ' selected="selected"';}?>>Between</option>
					</select>
					<input type="text" name="snatched1" size="6" value="<?=display_str($_GET['snatched1'])?>" />
					<input type="text" name="snatched2" size="6" value="<?=display_str($_GET['snatched2'])?>" />
				</td>
				<td class="label nobr">Disabled uploads</td>
				<td>
					<select name="disabled_uploads">
						<option value="" <? if(isset($_GET['disabled_uploads']) && $_GET['disabled_uploads']==='') {echo ' selected="selected"';}?>>Any</option>
						<option value="yes" <? if(isset($_GET['disabled_uploads']) && $_GET['disabled_uploads']==='yes') {echo ' selected="selected"';}?>>Yes</option>
						<option value="no" <? if(isset($_GET['disabled_uploads']) && $_GET['disabled_uploads']==='no') {echo ' selected="selected"';}?>>No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label nobr">Passkey:</td>
				<td>
					<input type="text" name="passkey" size="20" value="<?=display_str($_GET['passkey'])?>" />
				</td>
				<td class="label nobr">Tracker IP:</td>
				<td>
					<input type="text" name="tracker_ip" size="20" value="<?=display_str($_GET['tracker_ip'])?>" />
				</td>
				<td class="label nobr">Extra</td>
				<td>
					<input type="checkbox" name="ip_history" id="ip_history"<? if($_GET['ip_history']){ echo ' checked="checked"'; }?> />
					<label for="ip_history">IP History</label>

					<input type="checkbox" name="email_history" id="email_history"<? if($_GET['email_history']){ echo ' checked="checked"'; }?> />
					<label for="email_history">Email History</label>
				</td>
			</tr>
			<tr>
				<td class="label nobr">Type</td>
				<td>
					Strict <input type="radio" name="matchtype" value="strict"<? if($_GET['matchtype'] == 'strict' || !$_GET['matchtype']){ echo ' checked="checked"'; } ?> /> |
					Fuzzy <input type="radio" name="matchtype" value="fuzzy"<? if($_GET['matchtype'] == 'fuzzy' || !$_GET['matchtype']){ echo ' checked="checked"'; } ?> /> |
					Regex <input type="radio" name="matchtype" value="regex"<? if($_GET['matchtype'] == 'regex'){ echo ' checked="checked"'; } ?> />
				</td>
				<td class="label nobr">Order:</td>
				<td class="nobr">
					<select name="order">
					<?
						foreach(array_shift($OrderVals) as $Cur){ ?>
						<option value="<?=$Cur?>"<? if(isset($_GET['order']) && $_GET['order'] == $Cur || (!isset($_GET['order']) && $Cur == 'Joined')) { echo ' selected="selected"'; } ?>><?=$Cur?></option>
					<?	}?>
					</select>
					<select name="way">
					<?	foreach(array_shift($WayVals) as $Cur){ ?>
						<option value="<?=$Cur?>"<? if(isset($_GET['way']) && $_GET['way'] == $Cur || (!isset($_GET['way']) && $Cur == 'Descending')) { echo ' selected="selected"'; } ?>><?=$Cur?></option>
					<?	}?>
					</select>
				</td>
				<td class="label nobr"></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="6" class="center">
					<input type="submit" value="Search users" />
				</td>
			</tr>
		</table>
	</form>
</div>
<?
if($RunQuery){
	$Results = $DB->query($SQL);
	$DB->query('SELECT FOUND_ROWS();');
	list($NumResults) = $DB->next_record();
	$DB->set_query_id($Results);
}
?>
<div class="linkbox">
<?
$Pages=get_pages($Page,$NumResults,USERS_PER_PAGE,11);
echo $Pages;
?>
</div>
<div class="box pad center">
	<table width="100%">
		<tr class="colhead">
			<td>Username</td>
			<td>Ratio</td>
			<td>IP</td>
			<td>Email</td>
			<td>Joined</td>
			<td>Last Seen</td>
			<td>Upload</td>
			<td>Download</td>
			<td>Snatched</td>
			<td>Invites</td>
		</tr>
<?
while(list($UserID, $Username, $Uploaded, $Downloaded, $Snatched, $Class, $Email, $Enabled, $IP, $Invites, $DisableInvites, $Warned, $Donor, $JoinDate, $LastAccess) = $DB->next_record()){ ?>
		<tr>
			<td><?=format_username($UserID, $Username, $Donor, $Warned, $Enabled == 2 ? false : true, $Class)?></td>
			<td><?=ratio($Uploaded, $Downloaded)?></td>
			<td><?=display_str($IP)?></td>
			<td><?=display_str($Email)?></td>
			<td><?=time_diff($JoinDate)?></td>
			<td><?=time_diff($LastAccess)?></td>
			<td><?=get_size($Uploaded)?></td>
			<td><?=get_size($Downloaded)?></td>
			<td><?=number_format($Snatched)?></td>
			<td><? if($DisableInvites) { echo 'X'; } else { echo $Invites; } ?></td>
		</tr>
<?
}
?>
	</table>
</div>
<div class="linkbox">
<?=$Pages?>
</div>
<?
show_footer();
?>
