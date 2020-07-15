<?php

namespace Plugin_Name\Core;

use Plugin_Name;
use function Plugin_Name\Functions\Array_Utils\array_traverse;

/**
 * Handles the plugin config variables.
 *
 * @package Plugin_Name
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
	 * Handle the construction of the static Config object.
	 */
	public static function __constructStatic() {
		self::set_config_path( Plugin_Name::get_plugin_path( 'config/' ) );
	}

	/**
	 * Set the path to the location where the config files are stored.
	 *
	 * @param string $config_path The path to the config files.
	 */
	public static function set_config_path( $config_path ) {
		self::$config_path = $config_path;
	}

	/**
	 * Get the path to the location where the config files are stored.
	 *
	 * @param string $path The path to append to the set config path.
	 *
	 * @return string The path to the config files.
	 */
	public static function get_config_path( $path = '' ) {
		return self::$config_path . $path;
	}

	/**
	 * Handles the retrieval of the config item.
	 *
	 * @param string $name The name of the config file.
	 * @param array  $path The path to an individual item within the config array.
	 * @return mixed The config value.
	 */
	public static function get_config( $name, $path = array() ) {
		return array_traverse( self::get_loaded_config( $name ), $path );
	}

	/**
	 *
	 */
	public static function has_config_loaded( $name ) {
		return isset( self::$config[ $name ] );
	}

	/**
	 * Get a config file/directory "just in time".
	 *
	 * @param string $name The name of the config file and/or directory.
	 *
	 * @return mixed The config value.
	 */
	protected static function get_loaded_config( $name ) {

		// Load the requested config if it hasn't already been loaded.
		if ( ! self::has_config_loaded( $name ) ) {
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
			self::$config[ $name ] = $config;
		}
		return self::$config[ $name ];
	}

}
