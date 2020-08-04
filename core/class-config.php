<?php
/**
 * Includes the class for managing config options.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use Plugin_Name;
use function Plugin_Name\Functions\Array_Utils\array_build_traversable_path;
use function Plugin_Name\Functions\Array_Utils\array_traverse;

/**
 * Handles the plugin config variables.
 */
class Config {

	/**
	 * The config variables used within the plugin.
	 *
	 * @var array The plugin config variables.
	 */
	protected static $config = array();

	/**
	 * The path to the config files used to retrieve information from.
	 *
	 * @var string $config_path The path to the config files.
	 */
	protected static $config_path;

	/**
	 * Stores whether the config directory path has been set.
	 *
	 * @var bool Whether the config directory path has been set.
	 */
	protected static $is_config_path_set = false;

	/**
	 * Set the path to the location where the config files are stored.
	 *
	 * @param string $config_path The path to the config files.
	 */
	public static function set_config_path( $config_path ) {
		self::$config_path        = $config_path;
		self::$is_config_path_set = true;
	}

	/**
	 * Retrieves the default directory that contains the config files and directories for this plugin.
	 *
	 * @return string The default directory that contains the config files and directories.
	 */
	public static function get_default_config_path() {
		return Plugin_Name::get_plugin_path( 'config/' );
	}

	/**
	 * Get the path to the location where the config files are stored.
	 *
	 * @param string $path The path to append to the set config path.
	 *
	 * @return string The path to the config files.
	 */
	public static function get_config_path( $path = '' ) {
		if ( ! self::$is_config_path_set ) {
			self::set_config_path( self::get_default_config_path() );
		}
		return self::$config_path . $path;
	}

	/**
	 * Sets a config option.
	 *
	 * @param string $name   The name of the config to set.
	 * @param mixed  $config The content of the config.
	 */
	public static function set_config( $name, $config ) {
		self::$config[ $name ] = $config;
	}

	/**
	 * Determines whether a config load been loaded.
	 *
	 * @param string $name The name of the config to check for.
	 *
	 * @return bool Whether the requested config has been loaded.
	 */
	public static function is_config_loaded( $name ) {
		return isset( self::$config[ $name ] );
	}

	/**
	 * Loads a requested config from within the config directory.
	 *
	 * @param string $name The name of the config to load.
	 */
	protected static function load_config( $name ) {
		$config           = array();
		$config_file      = self::get_config_path( $name . '.php' );
		$config_directory = self::get_config_path( $name );

		// Check if the config directory exists.
		if ( is_dir( $config_directory ) ) {

			// Define the anonymous function used for adding config files within a directory and its subdirectories.
			$add_config_directory = function( $config_directory ) use ( &$config, &$add_config_directory ) {

				// Add the classes for the provided directory.
				foreach ( glob( $config_directory . '/*.php' ) as $config_file ) {
					$config = array_replace_recursive( $config, include $config_file );
				}

				// Handle adding subdirectory config file.
				foreach ( glob( $config_directory . '/*', GLOB_ONLYDIR ) as $config_subdirectory ) {
					$add_config_directory( $config_subdirectory );
				}
			};

			// Begin adding config files for the provided directory.
			$add_config_directory( $config_directory );
		}

		// Check if the config file exists.
		if ( file_exists( $config_file ) ) {
			$config = array_replace_recursive( $config, include $config_file );
		}

		// Set the loaded config.
		self::set_config( $name, $config );
	}

	/**
	 * Determines whether a config file has already been loaded.
	 *
	 * @param string $dot_path The path to the variable to check for. Where the first section is the file name and the
	 * remaining sections is the path to an individual item within the config array.
	 *
	 * @return bool Whether the config with the provided ID has been loaded.
	 */
	public static function has_config( $dot_path ) {
		return ! empty( self::get_config( $dot_path ) );
	}

	/**
	 * Handles the retrieval of the config item.
	 *
	 * @param string $dot_path The path to the desired variable. Where the first section is the file name and the
	 * remaining sections is the path to an individual item within the config array.
	 *
	 * @return mixed The config value.
	 */
	public static function get_config( $dot_path ) {
		$path = array_build_traversable_path( $dot_path );
		$name = array_shift( $path );

		// Load the requested config if it hasn't already been loaded.
		if ( ! self::is_config_loaded( $name ) ) {
			self::load_config( $name );
		}
		return array_traverse( self::$config[ $name ], $path );
	}

}
