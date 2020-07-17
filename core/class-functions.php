<?php

namespace Plugin_Name\Core;

use Plugin_Name;

/**
 * Handles the plugin functions.
 *
 * @package Plugin_Name
 */
class Functions {

	/**
	 * Loads all of the functions defined within this plugin.
	 */
	public static function load_plugin_functions() {
		foreach ( self::get_plugin_function_files() as $function_file ) {
			require_once $function_file;
		}
	}

	/**
	 * Retrieves all of the files containing functions within this plugin.
	 *
	 * @return array An array of all the files containing functions within this plugin.
	 */
	public static function get_plugin_function_files() {
		return glob( Plugin_Name::get_plugin_path( '/functions/functions-*.php' ) );
	}

}
