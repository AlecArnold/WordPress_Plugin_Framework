<?php

namespace Plugin_Name\Core;

use Plugin_Name;
use ReflectionClass;
use ReflectionException;

/**
 * Includes all methods required for loading plugin classes.
 *
 * @package Plugin_Name
 */
class Classes {

	/**
	 * Hooks the event used to autoload plugin class files.
	 */
	public static function autoload_plugin_classes() {
		spl_autoload_register(
			function( $class_path ) {
				if ( self::is_plugin_class( $class_path ) ) {
					self::load_class_file( $class_path );
					self::invoke_static_constructor( $class_path );
				}
			}
		);
	}

	/**
	 * Check if a class path is used within this plugin.
	 *
	 * @param string $class_path The path to check.
	 *
	 * @return bool Whether the class is defined within the plugin.
	 */
	public static function is_plugin_class( $class_path ) {
		return preg_match( '/^' . preg_quote( Plugin_Name::PLUGIN_NAMESPACE, '/' ) . '($|\\\\)/', $class_path );
	}

	/**
	 * Loads a class file.
	 *
	 * @param string $class_path The class to load.
	 */
	public static function load_class_file( $class_path ) {
		$file_path = self::get_file_path_for_class( $class_path );

		// Check that a file was found.
		if ( $file_path ) {
			require_once $file_path;
		}
	}

	/**
	 * Attempt to invoke a static constructor for the given class.
	 *
	 * @param string $class_path The class to attempt to invoke a static constructor for.
	 */
	protected static function invoke_static_constructor( $class_path ) {

		// Handle any errors caused by the attempt to load the constructor.
		try {
			$reflection_class  = new ReflectionClass( $class_path );
			$reflection_method = $reflection_class->getMethod( '__constructStatic' ); // Could use $reflection_class->getShortName() to copy c# and java.

			// Check whether the method meets the requirements for a static constructor.
			if ( $reflection_method->isStatic() && 0 === $reflection_method->getNumberOfParameters() && ! $reflection_method->isAbstract() ) {
				$reflection_method->invoke( null );
			}
		} catch ( ReflectionException $exception ) {
			return;
		}
	}

	/**
	 *
	 */
	protected static function get_file_prefixes() {
		return array(
			'class-',
			'abstract-class-',
			'trait-',
			'interface-',
		);
	}

	/**
	 * Attempts to derive the file path for a class.
	 *
	 * @param string $class_path The class to get the file path for.
	 *
	 * @return string|null The file path for the class.
	 */
	public static function get_file_path_for_class( $class_path ) {
		$file_path     = null;
		$file_prefixes = self::get_file_prefixes();

		// Convert the class path to the file format.
		$class_path = str_replace( '_', '-', strtolower( $class_path ) );

		// Break the class down into parts.
		$file_path_array = explode( '\\', $class_path );

		// Remove the plugin local namespace.
		array_shift( $file_path_array );

		// Determine the file suffix.
		$file_suffix = array_pop( $file_path_array );

		// Reconstruct the array into the file path.
		$file_path = implode( '/', $file_path_array );

		// Loop through each of the file prefixes looking for an existing file.
		foreach ( $file_prefixes as $file_prefix ) {
			$possible_file_path = Plugin_Name::get_plugin_path( $file_path . '/' . $file_prefix . $file_suffix . '.php' );

			// Check if the file exists.
			if ( file_exists( $possible_file_path ) ) {
				$file_path = $possible_file_path;
				break;
			}
		}
		return $file_path;
	}

	/**
	 * Determines the class for a provided file or directory path.
	 *
	 * @param string $path The path of the class to be determined.
	 * @param string $prefix The file prefix. Default: `class-`.
	 *
	 * @return string The class for the provided path.
	 */
	public static function get_class_for_file_path( $path, $prefix = 'class-' ) {
		$class = str_replace( Plugin_Name::get_plugin_path(), '', $path ); // Remove the base plugin path.
		$class = str_replace( $prefix, '', $class ); // Remove the file extension.
		$class = str_replace( '.php', '', $class ); // Remove the file extension.
		$class = ucwords( $class, '-/\\' ); // Capitalise the first letter of each word.
		$class = str_replace( '-', '_', $class ); // Replace dashes with underscores.
		$class = str_replace( '/', '\\', $class ); // Replace forward slashes with back slashes.
		$class = Plugin_Name::PLUGIN_NAMESPACE . '\\' . $class; // Prepend the plugin namespace.
		return $class;
	}

	/**
	 *
	 */
	public static function get_class_key_for_file_path( $path, $prefix = 'class-' ) {
		$class_key = basename( $path, '.php' );
		$class_key = str_replace( $prefix, '', $class_key ); // Remove the file extension.
		$class_key = str_replace( '-', '_', $class_key ); // Replace dashes with underscores.
		return $class_key;
	}

	/**
	 * Generate a list of all the classes within a directory.
	 *
	 * @param string $directory The directory to search for classes within.
	 * @param bool   $traverse Whether to search subdirectories. Default: `false`.
	 * @param string $prefix The file prefix. Default: `class-`.
	 *
	 * @return array All of the classes within the given directory.
	 */
	public static function get_classes_in_directory( $directory, $traverse = false, $prefix = 'class-' ) {
		$classes = array();

		// Define the anonymous function used for adding classes.
		$add_directory_classes = function( $directory ) use ( $traverse, $prefix, &$classes, &$add_directory_classes ) {

			// Add the classes for the provided directory.
			foreach ( glob( $directory . '/' . $prefix . '*.php' ) as $class_file_path ) {
				$classes[ self::get_class_key_for_file_path( $class_file_path, $prefix ) ] = self::get_class_for_file_path( $class_file_path, $prefix );
			}

			// Handle adding subdirectory classes when required.
			if ( $traverse ) {
				foreach ( glob( $directory . '/*', GLOB_ONLYDIR ) as $directory ) {
					$add_directory_classes( $directory );
				}
			}
		};

		// Begin adding classes for the provided directory.
		$add_directory_classes( $directory );
		return $classes;
	}

}
