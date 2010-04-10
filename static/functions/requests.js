
function Vote(amount) {
	if(!amount) {
		amount = parseInt($('#amount').raw().value);
		if(amount == 0) {
			 amount = 20 * 1024 * 1024;
		}
	}
	ajax.get('requests.php?action=takevote&id=' + $('#requestid').raw().value + '&auth=' + $('#auth').raw().value + '&amount=' + amount, function (response) {
			if(response) {
				//No increment
			} else {
				$('#votecount').raw().innerHTML = (parseInt($('#votecount').raw().innerHTML)) + 1;
			}

			totalBounty = parseInt($('#total_bounty').raw().value);
			totalBounty += (amount / 2);
			$('#total_bounty').raw().value = totalBounty;
			$('#formatted_bounty').raw().innerHTML = get_size(totalBounty);

			set_message("Your vote of " + get_size(amount) + ", adding a " + get_size(amount / 2) + " bounty has been added");
			$('#button').raw().disabled = true;
		}
	);
}

function Calculate() {
	var mul = (($('#unit').raw().options[$('#unit').raw().selectedIndex].value == 'mb') ? (1024*1024) : (1024*1024*1024));
	if(($('#amount_box').raw().value * mul) > $('#current_uploaded').raw().value) {
		$('#new_uploaded').raw().innerHTML = "You can't afford that request!";
		$('#new_bounty').raw().innerHTML = "0.00 MB";
		$('#button').raw().disabled = true;
	} else if(!($('#amount_box').raw().value > 0) || isNaN($('#amount_box').raw().value)) {
		$('#new_uploaded').raw().innerHTML = get_size(($('#current_uploaded').raw().value));
		$('#new_bounty').raw().innerHTML = "0.00 MB";
		$('#button').raw().disabled = true;
	} else {
		$('#button').raw().disabled = false;
		$('#amount').raw().value = $('#amount_box').raw().value * mul;
		$('#new_uploaded').raw().innerHTML = get_size(($('#current_uploaded').raw().value) - (mul * $('#amount_box').raw().value));
		$('#new_ratio').raw().innerHTML = ratio($('#current_uploaded').raw().value - (mul * $('#amount_box').raw().value), $('#current_downloaded').raw().value);
		$('#new_bounty').raw().innerHTML = get_size(mul * $('#amount_box').raw().value);
	}
}

function AddArtistField() {
		var ArtistCount = document.getElementsByName("artists[]").length;
		if (ArtistCount >= 100) { return; }
		var ArtistField = document.createElement("input");
		ArtistField.type = "text";
		ArtistField.id = "artist";
		ArtistField.name = "artists[]";
		ArtistField.size = 45;
		
		var ImportanceField = document.createElement("select");
		ImportanceField.id = "importance";
		ImportanceField.name = "importance[]";
		ImportanceField.options[0] = new Option("Main", "1");
		ImportanceField.options[1] = new Option("Guest", "2");
		
		var x = $('#artistfields').raw();
		x.appendChild(document.createElement("br"));
		x.appendChild(ArtistField);
		x.appendChild(ImportanceField);
		ArtistCount++;
}

function RemoveArtistField() {
		var ArtistCount = document.getElementsByName("artists[]").length;
		if (ArtistCount == 1) { return; }
		var x = $('#artistfields').raw();
		
		while(x.lastChild.tagName != "INPUT") { 
			x.removeChild(x.lastChild); 
		}
		x.removeChild(x.lastChild); 
		ArtistCount--;
}

function Categories() {
	var cat = $('#categories').raw().options[$('#categories').raw().selectedIndex].value;
	if(cat == "Music") {
		$('#artist_tr').show();
		$('#releasetypes_tr').show();
		$('#formats_tr').show();
		$('#bitrates_tr').show();
		$('#media_tr').show();
		ToggleLogCue();
		$('#year_tr').show();
		$('#cataloguenumber_tr').show();
	} else if(cat == "Audiobooks" || cat == "Comedy") {
		$('#year_tr').show();
		$('#artist_tr').hide();
		$('#releasetypes_tr').hide();
		$('#formats_tr').hide();
		$('#bitrates_tr').hide();
		$('#media_tr').hide();
		$('#logcue_tr').hide();
		$('#cataloguenumber_tr').hide();
	} else {
		$('#artist_tr').hide();
		$('#releasetypes_tr').hide();
		$('#formats_tr').hide();
		$('#bitrates_tr').hide();
		$('#media_tr').hide();
		$('#logcue_tr').hide();
		$('#year_tr').hide();
		$('#cataloguenumber_tr').hide();
	}
}

function add_tag() {
	if ($('#tags').raw().value == "") {
		$('#tags').raw().value = $('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value;
	} else if ($('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value == "---") {
	} else {
		$('#tags').raw().value = $('#tags').raw().value + ", " + $('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value;
	}
}

function Toggle(id, disable) {
	var array = document.getElementsByName(id + '[]');
	var master = $('#toggle_' + id).raw().checked;
	for (var x in array) {
		array[x].checked = master;
		if(disable == 1) {
			array[x].disabled = master;
		}
	}
	
	if(id == "formats") {
		ToggleLogCue();
	}
}

function ToggleLogCue() {
	var array = document.getElementsByName('formats[]');
	var flac = false;
	
	if(array[1].checked) {
		flac = true;
	}
	
	if(flac) {
		$('#logcue_tr').show();
	} else {
		$('#logcue_tr').hide();
	}
	ToggleLogScore();
}

function ToggleLogScore() {
	if($('#needlog').raw().checked) {
		$('#minlogscore_span').show();
	} else {
		$('#minlogscore_span').hide();
	}
}
