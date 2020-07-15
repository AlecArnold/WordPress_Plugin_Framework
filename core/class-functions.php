<?php

namespace Plugin_Name\Core;

use Plugin_Name;

/**
 *
 *
 * @package Plugin_Name
 */
class Functions {

	/**
	 *
	 */
	public static function load_plugin_functions() {
		foreach ( self::get_plugin_function_files() as $function_file ) {
			require_once $function_file;
		}
	}

	/**
	 *
	 */
	public static function get_plugin_function_files() {
		return glob( Plugin_Name::get_plugin_path( '/functions/functions-*.php' ) );
	}

}
