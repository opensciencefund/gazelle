"use strict";

/* Prototypes */
String.prototype.trim = function () {
	return this.replace(/^\s+|\s+$/g, '');	
};

/* Site wide functions */

//PHP ports
function isset(variable) {
	return (typeof(variable) === 'undefined') ? false : true;
}

function is_array(input) {
	return typeof(input) === 'object' && input instanceof Array;
}

function function_exists(function_name) {
	return (typeof this.window[function_name] === 'function');
}

function get_size(size) {
	steps = 0;
	while(size>=1024) {
		steps++;
		size=size/1024;
	}
	var ext;
	switch(steps) {
		case 1: ext = ' B';
				break;
		case 1: ext = ' KB';
				break;
		case 2: ext = ' MB';
				break;
		case 3: ext = ' GB';
				break;
		case 4: ext = ' TB';
				break;
		case 5: ext = ' PB';
				break;
		case 6: ext = ' EB';
				break;
		case 7: ext = ' ZB';
				break;
		case 8: ext = ' EB';
				break;
		default: "0.00 MB";
	}
	return (size.toFixed(2) + ext);
}

function get_ratio_color(ratio) {
	if (ratio < 0.1) { return 'r00'; }
	if (ratio < 0.2) { return 'r01'; }
	if (ratio < 0.3) { return 'r02'; }
	if (ratio < 0.4) { return 'r03'; }
	if (ratio < 0.5) { return 'r04'; }
	if (ratio < 0.6) { return 'r05'; }
	if (ratio < 0.7) { return 'r06'; }
	if (ratio < 0.8) { return 'r07'; }
	if (ratio < 0.9) { return 'r08'; }
	if (ratio < 1) { return 'r09'; }
	if (ratio < 2) { return 'r10'; }
	if (ratio < 5) { return 'r20'; }
	return 'r50';
}

function ratio(dividend, divisor, color) {
	if(!color) {
		color = true;
	}
	if(divisor == 0 && dividend == 0) {
		return '--';
	} else if(divisor == 0) {
		return '<span class="r99">∞</span>';
	} else if(dividend == 0 && divisor > 0) {
		return '<span class="r00">-∞</span>';
	}
	rat = ((dividend/divisor)-0.005).toFixed(2); //Subtract .005 to floor to 2 decimals
	if(color) {
		col = get_ratio_color(rat);
		if(col) {
			rat = '<span class="'+col+'">'+rat+'</span>';
		}
	}
	return rat;
}


function set_message(message) {
	var messageDiv = document.createElement("div");
	messageDiv.className = "error_message";
	messageDiv.innerHTML = message;
	$("#content").raw().insertBefore(messageDiv,$("#content").raw().firstChild);
}

//returns key if true, and false if false better than the php funciton
function in_array(needle, haystack, strict) {
	if (strict === undefined) {
		strict = false;
	}
	for (var key in haystack) {
		if ((haystack[key] == needle && strict === false) || haystack[key] === needle) {
			return true;
		}
	}
	return false;
}

function array_search(needle, haystack, strict) {
	if (strict === undefined) {
		strict = false;
	}
	for (var key in haystack) {
		if ((strict === false && haystack[key] == needle) || haystack[key] === needle) {
			return key;
		}
	}
	return false;
}

var util = function (selector, context) {
	return new util.fn.init(selector, context);
}

util.fn = util.prototype = {
	objects: new Array(),
	init: function (selector, context) {
		this.objects = Sizzle(selector, context);
		return this;
	},
	results: function () {
		return this.objects.length;
	},
	show: function () {
		return this.remove_class('hidden');
	},
	hide: function () {
		return this.add_class('hidden');
	},
	toggle: function () {
		//Should we interate and invert all entries, or just go by the first?
		if (!in_array('hidden', this.objects[0].className.split(' '))) {
			this.add_class('hidden');
		} else {
			this.remove_class('hidden');
		}
		return this;
	},
	listen: function (event, callback) {
		for (var i=0,il=this.objects.length;i<il;i++) {
			var object = this.objects[i];
			if (document.addEventListener) {
				object.addEventListener(event, callback, false);
			} else {
				object.attachEvent('on' + event, callback);
			}
		}
		return this;
	},
	remove: function () {
		for (var i=0,il=this.objects.length;i<il;i++) {
			var object = this.objects[i];
			object.parentNode.removeChild(object);
		}
		return this;
	},
	add_class: function (class_name) {
		for (var i=0,il=this.objects.length;i<il;i++) {
			var object = this.objects[i];
			if (object.className === '') {
				object.className = class_name;
			} else if (!in_array(class_name, object.className.split(' '))) {
				object.className = object.className + ' ' + class_name;
			}
		}
		return this;
	},
	remove_class: function (class_name) {
		for (var i=0,il=this.objects.length;i<il;i++) {
			var object = this.objects[i];
			var classes = object.className.split(' ');
			var result = array_search(class_name, classes);
			if (result === false) { 
				return this;
			}
			delete classes[result];
			object.className = classes.join(' ');
		}
		return this;
	},
	html : function (html) {
		for (var i=0,il=this.objects.length;i<il;i++) {
			this.objects[i].innerHTML = html;
		}
		return this;
	},
	raw: function (number) {
		if (number === undefined) {
			number = 0;
		}
		return this.objects[number];
	}
}

util.fn.init.prototype = util.fn;
var $ = util;
