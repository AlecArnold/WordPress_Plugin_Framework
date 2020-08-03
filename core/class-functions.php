<?php
/**
 * Includes the class for managing functions.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use Plugin_Name;

/**
 * Handles the plugin functions.
 */
class Functions {

	/**
	 * Stores all of the files that contain functions for this plugin.
	 *
	 * @var array An array containing all of functions for this plugin.
	 */
	protected static $plugin_function_files;

	/**
	 * Stores whether the plugin function files have been set.
	 *
	 * @var bool Whether the plugin function files have been set.
	 */
	protected static $are_plugin_function_files_set = false;

	/**
	 * Sets the files that contain functions for this plugin.
	 *
	 * @param array $plugin_function_files An array containing all of functions for this plugin.
	 */
	public static function set_plugin_function_files( $plugin_function_files ) {
		self::$plugin_function_files         = $plugin_function_files;
		self::$are_plugin_function_files_set = true;
	}

	/**
	 * Retrieves all of the files containing functions within this plugin.
	 *
	 * @return array An array of all the files containing functions within this plugin.
	 */
	public static function get_plugin_function_files() {
		if ( ! self::$are_plugin_function_files_set ) {
			self::set_plugin_function_files( glob( Plugin_Name::get_plugin_path( '/functions/functions-*.php' ) ) );
		}
		return self::$plugin_function_files;
	}

	/**
	 * Loads all of the functions defined within this plugin.
	 */
	public static function load_plugin_functions() {
		foreach ( self::get_plugin_function_files() as $function_file ) {
			require_once $function_file;
		}
	}

}
