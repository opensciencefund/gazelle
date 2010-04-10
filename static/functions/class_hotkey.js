listener = {
	events: {},
	handle: function (e) {
		if (isset(names[e.keyCode]) {
		
		}
	},
	add: function(keycode,callback) {
		try { document.addEventListener('keydown', this.handle, false); } catch (err) { document.attachEvent('onkeydown', this.handle); }
		return this.events[keycode]  = callback;
	}/*,
	remove: function(id) {
		try { document.removeEventListener('keydown', this.keys[id], false); } catch (err) { document.detachEvent('onkeydown', this.keys[id]); }
		delete this.keys[id];
	}*/
};
/*
hotkey = {
	map: {
		//Misc
		8 : 'backspace',
		9 : 'tab',
		13 : 'enter',
		16 : 'shift',
		17 : 'control',
		18 : 'alt',
		19 : 'break',
		20 : 'capslock',
		27 : 'esc',
		32 : 'space',
		
		//Movers
		33 : 'pageup',
		34 : 'pagedown',
		35 : 'end',
		36 : 'home',
		37 : 'left',
		38 : 'up',
		//39 : '\'',
		39 : 'right',
		40 : 'down',
		/*
		44 : 'printscreen',
		44 : ',',
		45 : 'insert',
		45 : '-',		
		46 : 'delete',
		46 : '.', 
		* /
		47 : '/',

		//Num
		48 : '0',
		49 : '1',
		50 : '2',
		51 : '3',
		52 : '4',
		53 : '5',
		54 : '6',
		55 : '7',
		56 : '8',
		57 : '9',
		
		59 : ';',
		61 : '=',
		
		//Alpha
		65 : 'a',
		66 : 'b',
		67 : 'c',
		68 : 'd',
		69 : 'e',
		70 : 'f',
		71 : 'g',
		72 : 'h',
		73 : 'i',
		74 : 'j',
		75 : 'k',
		76 : 'l',
		77 : 'm',
		78 : 'n',
		79 : 'o',
		80 : 'p',
		81 : 'q',
		82 : 'r',
		83 : 's',
		84 : 't',
		85 : 'u',
		86 : 'v',
		87 : 'w',
		88 : 'x',
		89 : 'y',
		90 : 'z',
		91 : '[',
		92 : '\\',
		93 : ']',
		//Num pad
		//96 : '`',
		//96 : '0',
		97 : '1',
		98 : '2',
		99 : '3',
		100 : '4',
		101 : '5',
		102 : '6',
		103 : '7',
		104 : '8',
		105 : '9',
		106 : '*',
		107 : '+',
		109 : '-',
		110 : '.',
		111 : '/',
		
		//Function keys
		112 : 'f1',
		113 : 'f2',
		114 : 'f3',
		115 : 'f4',
		116 : 'f5',
		117 : 'f6',
		118 : 'f7',
		119 : 'f8',
		120 : 'f9',
		121 : 'f10',
		122 : 'f11',
		123 : 'f12',
		
		144 : 'numlock',
		145 : 'scrolllock',
		
		186 : ';',
		187 : '=',
		188 : ',',
		189 : '-',
		190 : '.',
		191 : '/',
		192 : '`',
		
		219 : '[',
		220 : '\\',
		221 : ']',
		222 : '\''
	},
	add: function(trigger,callback) {
		keys = trigger.toLowerCase().split(" ");
		var handle = function(e) {
			/*if(keys.length == 1) {
				if(document.activeElement.tagName == 'INPUT' || document.activeElement.tagName == 'TEXTAREA') { return; }
			}* /
			var charachter = hotkey.map[e.keyCode];
			if(!isset(charachter)) { return; }
			console.log(charachter);
			m = {'key':'','ctrl':false,'shift':false,'alt':false,'meta':false};
			for (k in keys) {
				switch (keys[k]) {
					case 'ctrl':	m.ctrl = true;	break;
					case 'shift':	m.shift = true;	break;
					case 'alt':		m.alt = true;	break;
					default: m.key = keys[k];
				}
			}
			
			if(e.ctrlKey == m.ctrl && e.shiftKey == m.shift && e.altKey == m.alt && m.key == charachter) {
				try {
					e.stopPropagation();
					e.preventDefault();
				} catch (err) {
					e.cancelBubble = true;
					e.returnValue = false;
				}
				callback(e);
			}
		};
		listener.add(document,'keydown',handle,trigger);
	},
	remove: function(keys) {
		listener.remove(keys.toLowerCase());
	}
};
