<?

$UserID = $_REQUEST['userid'];
if(!is_number($UserID) || ($UserID != $LoggedUser['ID'] && !check_perms('users_edit_profiles'))) {
	error(403);
}

$DB->query("SELECT 
			m.Username,
			m.Email,
			m.IRCKey,
			m.Paranoia,
			i.Info,
			i.Avatar,
			i.Country,
			i.StyleID,
			i.StyleURL,
			i.SiteOptions
			FROM users_main AS m
			JOIN users_info AS i ON i.UserID = m.ID
			WHERE m.ID = '".db_string($UserID)."'");
list($Username,$Email,$IRCKey,$Paranoia,$Info,$Avatar,$Country,$StyleID,$StyleURL,$SiteOptions)=$DB->next_record(MYSQLI_NUM, array(9));

if (!empty($_POST['email']) && $Email != $_POST['email']) { }

if ($SiteOptions) { 
	$SiteOptions=unserialize($SiteOptions); 
} else { 
	$SiteOptions=array();
}

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;

show_header($Username.' > Settings','validate');
echo $Val->GenerateJS('userform');

show_message();
?>
<div class="thin">
	<h2><?=format_username($UserID,$Username)?> &gt; Settings</h2>
	<form id="userform" name="userform" action="" method="post" onsubmit="return formVal();" autocomplete="off">
		<div>
			<input type="hidden" name="action" value="takeedit" />
			<input type="hidden" name="userid" value="<?=$UserID?>" />
		</div>
		<table cellpadding='6' cellspacing='1' border='0' width='100%' class='border'>
			<tr class="colhead_dark">
				<td colspan="2">
					<strong>Site preferences</strong>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Stylesheet</strong></td>
				<td>
					<select name="stylesheet" id="stylesheet">
<? foreach($Stylesheets as $Style) { ?>
						<option value="<?=$Style['ID']?>"<? if ($Style['ID'] == $StyleID) { ?>selected="selected"<? } ?>><?=$Style['ProperName']?></option>
<? } ?>
					</select>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Or -&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					External CSS: <input type="text" size="40" name="styleurl" id="styleurl" value="<?=display_str($StyleURL)?>" />
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Paranoia Level</strong></td>
				<td>
					<select name="paranoia" id="paranoia">
						<option value="0"<? if ($Paranoia == "0") { ?>selected="selected"<? } ?>>0 - Standard (Nothing Hidden)</option>
						<option value="1"<? if ($Paranoia == "1") { ?>selected="selected"<? } ?>>1 - Hidden: Seeding, Leeching.</option>
						<option value="2"<? if ($Paranoia == "2") { ?>selected="selected"<? } ?>>2 - Hidden: Seeding, Leeching, Snatched.</option>
						<option value="3"<? if ($Paranoia == "3") { ?>selected="selected"<? } ?>>3 - Hidden: Seeding, Leeching, Snatched, Uploaded.</option>
						<option value="4"<? if ($Paranoia == "4") { ?>selected="selected"<? } ?>>4 - Hidden: Seeding, Leeching, Snatched, Uploaded, Stats.</option>
						<option value="5"<? if ($Paranoia == "5") { ?>selected="selected"<? } ?>>5 - Tinfoil Hat (Everything Hidden)</option>
					</select>
					<br/>
					<span class="warning">Note: Paranoia has nothing to do with your security on this site, the only thing affected by this setting is other users ability to see your taste in music.</span>
				</td>
			</tr>
<? if (check_perms('site_advanced_search')) { ?>
			<tr>
				<td class="label"><strong>Default Search Type</strong></td>
				<td>
					<select name="searchtype" id="searchtype">
						<option value="0"<? if ($SiteOptions['SearchType'] == "0") { ?>selected="selected"<? } ?>>Simple</option>
						<option value="1"<? if ($SiteOptions['SearchType'] == "1") { ?>selected="selected"<? } ?>>Advanced</option>
					</select>
				</td>
			</tr>
<? } ?>
			<tr>
				<td class="label"><strong>Torrent Grouping</strong></td>
				<td>
					<select name="disablegrouping" id="disablegrouping">
						<option value="0"<? if ($SiteOptions['DisableGrouping']=="0") { ?>selected="selected"<? } ?>>Group torrents by default</option>
						<option value="1"<? if ($SiteOptions['DisableGrouping']=="1") { ?>selected="selected"<? } ?>>DO NOT Group torrents by default</option>
					</select>&nbsp;
					<select name="torrentgrouping" id="torrentgrouping">
						<option value="0"<? if ($SiteOptions['TorrentGrouping']=="0") { ?>selected="selected"<? } ?>>Groups are open by default</option>
						<option value="1"<? if ($SiteOptions['TorrentGrouping']=="1") { ?>selected="selected"<? } ?>>Groups are closed by default</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Discography View</strong></td>
				<td>
					<select name="discogview" id="discogview">
						<option value="0"<? if ($SiteOptions['DiscogView'] == "0") { ?>selected="selected"<? } ?>>Open by default</option>
						<option value="1"<? if ($SiteOptions['DiscogView'] == "1") { ?>selected="selected"<? } ?>>Closed by default</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Posts per page (Forum)</strong></td>
				<td>
					<select name="postsperpage" id="postsperpage">
						<option value="25"<? if ($SiteOptions['PostsPerPage'] == "25") { ?>selected="selected"<? } ?>>25 (Default)</option>
						<option value="50"<? if ($SiteOptions['PostsPerPage'] == "50") { ?>selected="selected"<? } ?>>50</option>
						<option value="100"<? if ($SiteOptions['PostsPerPage'] == "100") { ?>selected="selected"<? } ?>>100</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Hide release types</strong></td>
				<td>
<?	foreach ($ReleaseTypes as $Key => $Val) {
		if(!empty($SiteOptions['HideTypes']) && in_array($Key, $SiteOptions['HideTypes'])) { 
			$Checked = 'checked="checked"'; 
		} else { 
			$Checked=''; 
		}
?>
		<input type="checkbox" id="hide_type_<?=$Key?>" name="hidetypes[]=" value="<?=$Key?>" <?=$Checked?> />
		<label for="hide_type_<?=$Key?>"><?=$Val?></label>
<?	}?>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Collage album art view</strong></td>
				<td>
					<select name="hidecollage" id="hidecollage">
						<option value="0"<? if ($SiteOptions['HideCollage'] == "0") { ?>selected="selected"<? } ?>>Show album art</option>
						<option value="1"<? if ($SiteOptions['HideCollage'] == "1") { ?>selected="selected"<? } ?>>Hide album art</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Browse Page Tag list</strong></td>
				<td>
					<select name="showtags" id="showtags">
						<option value="1"<? if ($SiteOptions['ShowTags'] == "1") { ?>selected="selected"<? } ?>>Open by default.</option>
						<option value="0"<? if ($SiteOptions['ShowTags'] == "0") { ?>selected="selected"<? } ?>>Closed by default.</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Subscription</strong></td>
				<td>
					<input type="checkbox" name="autosubscribe" id="autosubscribe" <? if (!empty($SiteOptions['AutoSubscribe'])) { ?>checked="checked"<? } ?> />
					<label for="autosubscribe">Subscribe to topics when replying</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Smileys</strong></td>
				<td>
					<input type="checkbox" name="disablesmileys" id="disablesmileys" <? if (!empty($SiteOptions['DisableSmileys'])) { ?>checked="checked"<? } ?> />
					<label for="disablesmileys">Disable smileys</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Avatars</strong></td>
				<td>
					<input type="checkbox" name="disableavatars" id="disableavatars" <? if (!empty($SiteOptions['DisableAvatars'])) { ?>checked="checked"<? } ?> />
					<label for="disableavatars">Disable avatars</label>
				</td>
			</tr>
<?	
?>		
			<tr>
				<td class="label"><strong>Download torrents as text files</strong></td>
				<td>
					<input type="checkbox" name="downloadalt" id="downloadalt" <? if ($LoggedUser['DownloadAlt']) { ?>checked="checked"<? } ?> />
					<label for="downloadalt">For users whose ISP block the downloading of torrent files</label>
				</td>
			</tr>
			<tr class="colhead_dark">
				<td colspan="2">
					<strong>User info</strong>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Avatar URL</strong></td>
				<td>
					<input type="text" size="50" name="avatar" id="avatar" value="<?=display_str($Avatar)?>" />
					<p class="min_padding">Width should be 150 pixels (will be resized if necessary)</p>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Email</strong></td>
				<td><input type="text" size="50" name="email" id="email" value="<?=display_str($Email)?>" />
					<p class="min_padding">If changing this field you must enter your current password in the "Current password" field before saving your changes.</p>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Info</strong></td>
				<td><textarea name="info" cols="50" rows="8"><?=display_str($Info)?></textarea></td>
			</tr>
			<tr>
				<td class="label"><strong>IRCKey</strong></td>
				<td>
					<input type="text" size="50" name="irckey" id="irckey" value="<?=display_str($IRCKey)?>" />
					<p class="min_padding">This field, if set will be used in place of the password in the IRC login.</p>
					<p class="min_padding">Note: This value is stored in plaintext and should not be your password.</p>
					<p class="min_padding">Note: In order to be accepted as correct, your IRCKey must be between 6 and 32 characters.</p>
				</td>
			</tr>
			<tr class="colhead_dark">
				<td colspan="2">
					<strong>Change password</strong>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Current password</strong></td>
				<td><input type="password" size="40" name="cur_pass" id="cur_pass" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>New password</strong></td>
				<td><input type="password" size="40" name="new_pass_1" id="new_pass_1" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>Re-type new password</strong></td>
				<td><input type="password" size="40" name="new_pass_2" id="new_pass_2" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>Reset passkey</strong></td>
				<td>
					<input type="checkbox" name="resetpasskey" />
					<label for="ResetPasskey">Any active torrents must be downloaded again to continue leeching/seeding.</label>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="right">
					<input type="submit" value="Save Profile" />
				</td>
			</tr>
		</table>
	</form>
</div>
<?
show_footer();
?>
