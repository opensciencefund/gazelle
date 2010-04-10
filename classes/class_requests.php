<?
class REQUESTS {
	var $SQL = ''; // The main query - run to get data, and md5'd for the page key
	var $Title = ''; // Title of the page
	var $TagNames = array(); // List of tags the user has searched for - used for the search bar 
	
	var $Results = 0; // The number of results the query returns
	var $Requests = array(); // The result set - in array form, so it can be cached
	
	var $Big = true; // $Big is false on artist pages, creates a smaller table
	var $Cache = true; // Do we want to cache the page?
	var $CacheArray = array(); // This array is stored in cache. Combined of the result set and $this->Results
	
	//---------- Functions to set properties
	function set_sql($SQL){
		$this->SQL = $SQL;
	}
	function set_tag_names($TagNames){
		$this->TagNames = $TagNames;
	}
	function set_title($Title) {
		$this->Title = $Title;
	}
	function set_big($Big){
		$this->Big = $Big;
	}
	function use_cache($ShouldCache){
		$this->Cache = $ShouldCache;
	}

	// Set everything up, run queries
	function create_page(){
		global $DB, $Cache;
		// Try to get everything from cache
		if($this->Cache){
			if(isset($_SESSION['clearnext'])){ // If we're meant to be clearing the cache (set by vote.php)
				$Cache->delete_value('Requests_'.md5($this->SQL));
				unset($_SESSION['clearnext']);
			} else {
				$this->CacheArray = $Cache->get_value('Requests_'.md5($this->SQL));
			}
		}
		if(!$this->CacheArray){
			// Couldn't get it from cache, hit the database
			
			//Main query
			$DB->query($this->SQL);
			$this->Requests = $DB->to_array(); // Store as an array instead of a result set, so we can cache it
			$this->CacheArray['Requests'] = $this->Requests;
			
			// Number of results
			$DB->query('SELECT FOUND_ROWS()');
			list($this->Results) = $DB->next_record();
			$this->CacheArray['Results'] = $this->Results;
			
			// Cache $this->CacheArray for 5 minutes
			$Cache->cache_value('Requests_'.md5($this->SQL), $this->CacheArray, 60*5);
			
		} else { // Result is cached
			$this->Requests = $this->CacheArray['Requests'];
			$this->Results = $this->CacheArray['Results'];
		}
	}
	
	// Search bars and top pagination
	function create_header(){
		global $LoggedUser;

?>
<div class="thin">
	<h2><?=$this->Title?></h2>
	<div class="linkbox">
<?   	if(check_perms('site_submit_requests')){ ?> 
		<a href="requests.php?action=new">[New request]</a>
		<a href="requests.php?type=created">[My requests]</a>
<?   	} 
		if(check_perms('site_vote')){?> 
		<a href="requests.php?type=voted">[Requests I've voted on]</a>
<?   	} ?> 
	</div>
	<div class="center">
		<form action="" method="get">
			<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
				<tr>
					<td class="label">Search terms:</td>
					<td>
						<input type="text" name="search" size="75" value="<?if(isset($_GET['search'])) { echo display_str($_GET['search']); } ?>" />
					</td>
				</tr>
				<tr>
					<td class="label">Tags (comma-separated):</td>
					<td>
						<input type="text" name="tags" size="75" value="<?=display_str(implode(', ', $this->TagNames))?>" />
					</td>
				</tr>
<?		if(check_perms('site_see_old_requests')){ ?> 
				<tr>
					<td class="label">Include filled:</td>
					<td>
						<input type="checkbox" name="showall" <? if($_GET['showall']) {?>checked="checked"<? } ?> />
					</td>
				</tr>
				<tr>
					<td class="label">Requested by:</td>
					<td>
						<input type="text" name="requester" size="75" value="<?=display_str($_GET['requester'])?>" />
					</td>
				</tr>
<?		} ?> 
				<tr>
					<td colspan="2" class="center">
						<input type="submit" value="Search requests" />
					</td>
				</tr>
			</table>	
		</form>
	</div>
	
	<div class="linkbox">
<?
//---------------------------------------------------
		list($Page,$Limit) = page_limit(REQUESTS_PER_PAGE);
		echo get_pages($Page,$this->Results,REQUESTS_PER_PAGE, 11);
//-------------------------------------------------------
?>
	</div>
<?
	}
	function requests_table(){
		// The actual table of requests
		
		global $DB, $LoggedUser, $Time;
?>
	<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
	<tr class="colhead_dark">
		<td style="width:<?=($this->Big == true)?'38':'48';?>%;">
			<a href="requests.php?order=name&amp;sort=<?=((isset($_GET['order']) && $_GET['order'] == 'name' && $_GET['sort'] == 'asc')? 'desc' : 'asc').get_url()?>"><strong>Request Name</strong></a>
		</td>
		<td><strong>
<?   	if($this->Big){?> 
			<a href="requests.php?order=votes&amp;sort=<?=((isset($_GET['order']) && $_GET['order'] == 'votes' && $_GET['sort'] == 'desc')? 'asc' : 'desc').get_url()?>">Vote (20MB)</a>
<?   	} else { echo 'Vote'; }?> 
		</strong></td>
		<td>
			<a href="requests.php?order=bounty&amp;sort=<?=((isset($_GET['order']) && $_GET['order'] == 'bounty' && $_GET['sort'] == 'desc')? 'asc' : 'desc').get_url()?>"><strong>Bounty</strong></a>
		</td>
<?   	if($this->Big){?>
		<td><strong>Filled</strong></td>
		<td>
			<a href="requests.php?order=filler.username&amp;sort=<?=((isset($_GET['order']) && $_GET['order'] == 'filler.username' && $_GET['sort'] == 'asc')? 'desc' : 'asc').get_url()?>"><strong>Filled by</strong></a>
		</td>
		<td>
			<a href="requests.php?order=u.username&amp;sort=<?=((isset($_GET['order']) && $_GET['order'] == 'u.username' && $_GET['sort'] == 'asc')? 'desc' : 'asc').get_url()?>"><strong>Requested by</strong></a>
		</td>
<?   	}?>
		<td>
			<a href="requests.php?order=id&amp;sort=<?=((isset($_GET['order']) && $_GET['order'] == 'id' && $_GET['sort'] == 'desc')? 'asc' : 'desc').get_url()?>"><strong>Added</strong></a>
		</td>
	</tr>

<? //---------------------------------------------------
		if($this->Results == 0){
			echo '<tr class="rowb"><td colspan="7">Nothing found!</td></tr>';
		}
		else {
			
			$Row = 'a';
			foreach ($this->Requests as $Request) {
				list($ID, $Name, $ArtistID, $ArtistName, $TagIDs, $TagNames, $TimeAdded, $Votes, $FillerID, $FillerName, $Filled, $Bounty, $UserID, $Username) = $Request;
				$TagIDs = explode('|', $TagIDs);
				$TagNames = explode('|', $TagNames);
				
				$Row = ($Row == 'a') ? 'b' : 'a';
				
//--------------------------------------------------------?>
		<tr class="row<?=$Row?>">
			<td>
<?   			if($ArtistID){ ?> 
				<a href="artist.php?id=<?=$ArtistID?>" title="View Artist"><?=$ArtistName?></a> - 
<?   			} ?> 
				<a href="requests.php?action=viewrequest&amp;id=<?=$ID?>" title="View Request"><?=$Name?></a>
				<div class="tags">
<?
				$TagList = array();
				$i = 0;
				foreach($TagIDs as $TagID){
					$TagList []= '<a href="requests.php?tag='.$TagID.'">'.$TagNames[$i].'</a>';
					$i++;
				}
				$TagList = implode(', ', $TagList);
				echo $TagList;
				unset($TagList);
?>
				</div>
			</td>
			<td>
				<?=$Votes?>
<?   			if(!$Filled && check_perms('site_vote')){
					if($this->Big){?>&nbsp;&nbsp;<? }?>
					<a href="requests.php?action=vote&amp;id=<?=$ID?>&amp;auth=<?=$LoggedUser['AuthKey']?>"><strong>(+)</strong></a>
<?   			}?> 
			</td>
			<td>
				<?=get_size($Bounty)?>
			</td>
<?   			if($this->Big){ // Who filled the request, who requested it?>
			<td>
<?   			if($Filled > 0){ ?>
				<a href="torrents.php?id=<?=$Filled?>"><strong>Yes</strong></a>
<?   			} else { ?>
				<strong>No</strong>
<?   			} ?>
			</td>
			<td>
<?   				if($FillerID > 0){ ?>
				<a href="user.php?id=<?=$FillerID?>"><?=$FillerName?></a>
<?   				} else { ?>
				--
<?   				} ?>
			</td>
			<td>
				<a href="user.php?id=<?=$UserID?>"><?=$Username?></a>
			</td>
<?   			}?>
			<td>
				<?=time_diff($TimeAdded)?>
			</td>
		</tr>
<? //---------------------------------------------------
			} // while
		} // else
?>
	</table>
<?
	} // Function
	function create_footer(){
	// Bottom pagination
?>
	<div class="linkbox">
<?
//---------------------------------------------------
		list($Page,$Limit) = page_limit(REQUESTS_PER_PAGE);
		echo get_pages($Page,$this->Results,REQUESTS_PER_PAGE, 11);
//--------------------------------------------------------
?>
	</div>
</div>
<?
	} // create_footer
} // class
