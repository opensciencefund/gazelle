<?php
	/**
		Hook system for plugins. Plugins shall listen for events by registering their interest
		in this class (->register()). 
	*/
	class HOOK {
		/**
			List of listening plugins registered with this class. Each event string is a 
			key, and its value is a list of functions to be called when the event is raised.
		*/
		var $hooks = array();
		
		function _import_plugin($path, $xml) {
			$hooks = $xml->getElementsByTagName("hooks");
			if($hooks->length == 0)
				return false;
			$hooks = $hooks->item(0);
			
			foreach($hooks->childNodes as $hook) {
				$hooks[$hook["event"]] = array("file" => $path . $hook["file"], "function" => $hook["function"]);
			}
		}
		
		/**
			Looks through the plugins/ folder and registers hooks.
		*/
		function scan_plugins() {
			$files = scandir(SERVER_ROOT."plugins/");
			foreach($files as $file) {
				if($file == "." || $file == "..") continue;
				
				if(is_dir(SERVER_ROOT."plugins/".$file)) {
					if(file_exists(SERVER_ROOT."plugins/$file/config.xml")) {
						$xml = new DOMDocument;
						if(!$xml->load(SERVER_ROOT."plugins/$file/config.xml")) {
							continue; // Not loading an invalid config file.
						}
						_import_plugin(SERVER_ROOT."plugins/$file/", $xml);
					}
				}
			}
		}
		
		/**
			Raises an event, causing all of the plugins listening to the
			event to trigger. 
			
			@param $event 	What event was raised.
			@param $arguments	Arguments to pass to listening functions, in the form of an associative array.
		*/
		function raise($event, $arguments = array()) {
			if(!isset($hooks[$event]))
				return;
				
			foreach($hooks[$event] as $f) {
				require_once($f["file"]);
				$func = $f["function"];
				$func($arguments);
			}
		}
		
		/**
			Register a function with this class, causing it to be called when
			ever the $event is raised. This is called internally in scan_plugins().
			
			@param $event What event to listen for.
			@param $func	Static function receiving exaclty one argument, an associative array.
		*/
		function _register($event, $func) {
			$hooks[$event][] = $func;
		}
	}
?>