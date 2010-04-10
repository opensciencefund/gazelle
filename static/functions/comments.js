var username;
var postid;

function Quote(post, user) {
	username = user;
	postid = post;
	ajax.get("?action=get_post&post=" + postid, function(response){
		if ($('#quickpost').raw().value !== '') {
			$('#quickpost').raw().value = $('#quickpost').raw().value + "\n\n";
		}
		$('#quickpost').raw().value = $('#quickpost').raw().value + "[quote="+username+"]" + 
			//response.replace(/(img|aud)(\]|=)/ig,'url$2').replace(/\[url\=(https?:\/\/[^\s\[\]<>"\'()]+?)\]\[url\](.+?)\[\/url\]\[\/url\]/gi, "[url]$1[/url]")
			response
		+ "[/quote]";
		resize('quickpost');
	});
}

function Edit_Form(post,key) {
	postid = post;
	if (location.href.match(/torrents\.php/)) {
		boxWidth="50";
	} else {
		boxWidth="80";
	}
	$('#bar' + postid).raw().cancel = $('#content' + postid).raw().innerHTML;
	$('#bar' + postid).raw().oldbar = $('#bar' + postid).raw().innerHTML;
	$('#content' + postid).raw().innerHTML = "<div id=\"preview" + postid + "\"></div><form id=\"form" + postid + "\" method=\"post\"><input type=\"hidden\" name=\"key\" value=\"" + key + "\" /><input type=\"hidden\" name=\"post\" value=\"" + postid + "\" /><textarea id=\"editbox" + postid + "\" onkeyup=\"resize('editbox" + postid + "');\" name=\"body\" cols=\""+boxWidth+"\" rows=\"10\"></textarea></form>";
	$('#bar' + postid).raw().innerHTML = "<input type=\"button\" value=\"Preview\" onclick=\"Preview_Edit(" + postid + ");\" /><input type=\"button\" value=\"Post\" onclick=\"Save_Edit(" + postid + ")\" /><input type=\"button\" value=\"Cancel\" onclick=\"Cancel_Edit(" + postid + ");\" />";
	ajax.get("?action=get_post&post=" + postid, function(response){
		$('#editbox' + postid).raw().value = response;
		resize('editbox' + postid);
	});
}

function Cancel_Edit(postid) {
	$('#bar' + postid).raw().innerHTML = $('#bar' + postid).raw().oldbar;
	$('#content' + postid).raw().innerHTML = $('#bar' + postid).raw().cancel;
}

function Preview_Edit(postid) {
	$('#bar' + postid).raw().innerHTML = "<input type=\"button\" value=\"Editor\" onclick=\"Cancel_Preview(" + postid + ");\" /><input type=\"button\" value=\"Post\" onclick=\"Save_Edit(" + postid + ")\" /><input type=\"button\" value=\"Cancel\" onclick=\"Cancel_Edit(" + postid + ");\" />";
	ajax.post("ajax.php?action=preview","form" + postid, function(response){
		$('#preview' + postid).raw().innerHTML = response;
		$('#editbox' + postid).hide();	
	});
}

function Cancel_Preview(postid) {
	$('#bar' + postid).raw().innerHTML = "<input type=\"button\" value=\"Preview\" onclick=\"Preview_Edit(" + postid + ");\" /><input type=\"button\" value=\"Post\" onclick=\"Save_Edit(" + postid + ")\" /><input type=\"button\" value=\"Cancel\" onclick=\"Cancel_Edit(" + postid + ");\" />";
	$('#preview' + postid).raw().innerHTML = "";
	$('#editbox' + postid).show();
}

function Save_Edit(postid) {
	$('#bar' + postid).raw().innerHTML = "";
	if (location.href.match(/forums\.php/)) {
		ajax.post("forums.php?action=takeedit","form" + postid, function (response) {
			$('#preview' + postid).raw().innerHTML = response;
			$('#editbox' + postid).hide();
		});
	} else if (location.href.match(/collages\.php/)) {
		ajax.post("collages.php?action=takeedit_comment","form" + postid, function (response) {
			$('#preview' + postid).raw().innerHTML = response;
			$('#editbox' + postid).hide();
		});
	} else if (location.href.match(/requests\.php/)) {
		ajax.post("requests.php?action=takeedit_comment","form" + postid, function (response) {
			$('#preview' + postid).raw().innerHTML = response;
			$('#editbox' + postid).hide();
		});
	} else {
		ajax.post("torrents.php?action=takeedit_post","form" + postid, function (response) {
			$('#preview' + postid).raw().innerHTML = response;
			$('#editbox' + postid).hide();
		});
	}
}

function Delete(post) {
	postid = post;
	if (confirm('Are you sure you wish to delete this post?') == true) {
		if (location.href.match(/forums\.php/)) {
			ajax.get("forums.php?action=delete&auth=" + authkey + "&postid=" + postid, function () {
				$('#post' + postid).hide();
			});
		} else if (location.href.match(/collage\.php/)) {
			ajax.get("collage.php?action=delete_comment&auth=" + authkey + "&postid=" + postid, function () {
				$('#post' + postid).hide();
			});
		} else if (location.href.match(/requests\.php/)) {
			ajax.get("requests.php?action=delete_comment&auth=" + authkey + "&postid=" + postid, function () {
				$('#post' + postid).hide();
			});
		} else {
			ajax.get("torrents.php?action=delete_post&auth=" + authkey + "&postid=" + postid, function () {
				$('#post' + postid).hide();
			});
		}
	}
}

function Quick_Preview() {
	var quickreplybuttons;
	if(window.location.pathname.indexOf('forums.php') == -1) {
		quickreplybuttons = $('#quickreplybuttons');
	} else {
		quickreplybuttons = $('#quickreplybuttonstoggle');
	}
	quickreplybuttons.raw().innerHTML = "<input type=\"button\" value=\"Editor\" onclick=\"Quick_Edit();\" /><input type=\"submit\" value=\"Submit\" />";
	ajax.post("ajax.php?action=preview","quickpostform", function(response){
		$('#quickreplypreview').show();
		$('#contentpreview').raw().innerHTML = response;
		$('#quickreplytext').hide();
	});
}

function Quick_Edit() {
	var quickreplybuttons;
	if(window.location.pathname.indexOf('forums.php') == -1) {
		quickreplybuttons = $('#quickreplybuttons');
	} else {
		quickreplybuttons = $('#quickreplybuttonstoggle');
	}
	quickreplybuttons.raw().innerHTML = "<input type=\"button\" value=\"Preview\" onclick=\"Quick_Preview();\" /><input type=\"submit\" value=\"Submit\" />";
	$('#quickreplypreview').hide();
	$('#quickreplytext').show();
}
