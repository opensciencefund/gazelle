var str = '';
var recomend = '';
var value = '';
var id = '';

alert('AJAX used here is depreciated. Not to mention this needs a bit of a rewrite.');

function suggest(idname,e) {
	if(!navigator.userAgent.match(/Opera/i)) {
		id = $('#' + idname);
		if (e.keyCode == 8) { //Backspace
			time = 1180; //1.18s
			cache = false;
		} else {
			time = 220; //0.22s
			cache = true;
		}
		value = id.value;
		if (value.length < 2) {
			recomend = '';
		}
		if (id.selectionStart != 0) {
			if (value > 1 && value.toLowerCase() == recomend.substr(0,value.length).toLowerCase() && cache == true) {
				str = value;
				ajax.handle();
			} else {
				setTimeout('get_suggest(value)', time);
			}
		}
	}
}

function get_suggest(old) {
	if (old.length > 1 && old === id.value) {
		str = old;
		ajax.get("autocomplete.php?name=" + str,function (recomend) {
			if (recomend != '') {
				id.value = recomend;
				if (id.createTextRange) { // Damn IE
					range = id.createTextRange();
					range.findText(recomend.substr(str.length));
					range.select();
				} else {
					id.setSelectionRange(str.length,recomend.length);
				}
			}
		});
	}
}
