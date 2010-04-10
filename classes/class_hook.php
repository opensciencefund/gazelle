<?php
	/**
		Hook system for plugins. Plugins shall listen for events by registering their interest
		in this class (->register()). 
	*/
	class HOOKS {
		/**
			List of listening plugins registered with this class. Each event string is a 
			key, and its value is a list of functions to be called when the event is raised.
		*/
		var $hooks = array();
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
				$f($arguments);
			}
		}
		
		/**
			Register a function with this class, causing it to be called when
			ever the $event is raised.
			
			@param $event What event to listen for.
			@param $func	Static function receiving exaclty one argument, an associative array.
		*/
		function register($event, $func) {
			$hooks[$event][] = $func;
		}
	}
?>