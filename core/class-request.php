<?php
/**
 * Includes the class for managing the request.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use function Plugin_Name\Functions\Array_Utils\array_build_traversable_path;
use function Plugin_Name\Functions\Array_Utils\array_traverse;

/**
 * Handles details associated with the session request.
 */
class Request {

	/**
	 * Stores an array containing both $_GET and $_POST combined.
	 *
	 * @var array An array containing both $_GET and $_POST combined.
	 */
	protected static $input_variables;

	/**
	 * Stores whether the input variables have been set.
	 *
	 * @var bool Whether the input variables have been set.
	 */
	protected static $are_input_variables_set = false;

	/**
	 * Retrieves the current request method for the users session.
	 *
	 * @return string The current request method.
	 */
	public static function get_method() {
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'ANY'; // phpcs:ignore
	}

	/**
	 * Retrieves the URL that is currently being accessed.
	 *
	 * @return string The URL that is currently being accessed. e.g. https://www.example.com/page/
	 */
	public static function get_url() {
		return ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // phpcs:ignore
	}

	/**
	 * Retrieve the current URL path for the users session.
	 *
	 * @return string The requested URL path.
	 */
	public static function get_url_path() {
		return isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null; // phpcs:ignore
	}

	/**
	 * Retrieves all of the global variables within the request.
	 *
	 * @return array All of the global variables with the request.
	 */
	public static function get_global_variables() {
		return $GLOBALS;
	}

	/**
	 * Determines whether a global variable has been provided in this request.
	 *
	 * @param string $dot_path The dot path to the data that is being checked for.
	 *
	 * @return bool Whether a global variable has been provided in this request.
	 */
	public static function has_global_variable( $dot_path ) {
		return ! empty( self::get_global_variable( $dot_path ) );
	}

	/**
	 * Retrieves a variable set within the $GLOBALS request.
	 *
	 * @param string $dot_path The dot path to the desired data.
	 * @param mixed  $default  The default value to return when there isn't a value set on this request.
	 *
	 * @return array|mixed The targeted variables value or the default value.
	 */
	public static function get_global_variable( $dot_path, $default = null ) {
		return array_traverse( self::get_global_variables(), array_build_traversable_path( $dot_path ), $default );
	}

	/**
	 * Sets the input variables stored within this request.
	 *
	 * @param string $input_variables The input variables stored within this request.
	 */
	public static function set_input_variables( $input_variables ) {
		self::$input_variables         = $input_variables;
		self::$are_input_variables_set = true;
	}

	/**
	 * Retrieves all of the input variables.
	 *
	 * @return array An array containing both $_GET and $_POST combined.
	 */
	public static function get_input_variables() {
		if ( ! self::$are_input_variables_set ) {
			self::set_input_variables( array_merge( $_GET, $_POST ) ); // phpcs:ignore
		}
		return self::$input_variables;
	}

	/**
	 * Determines whether an input variable has been provided in this request.
	 *
	 * @param string $dot_path The dot path to the data that is being checked for.
	 *
	 * @return bool Whether an input variable has been provided in this request.
	 */
	public static function has_input_variable( $dot_path ) {
		return ! empty( self::get_input_variable( $dot_path ) );
	}

	/**
	 * Retrieves a variable set within the $_POST or $_GET request.
	 *
	 * @param string $dot_path The dot path to the desired data.
	 * @param mixed  $default  The default value to return when there isn't a value set on this request.
	 *
	 * @return array|mixed The targeted variables value or the default value.
	 */
	public static function get_input_variable( $dot_path, $default = null ) {
		return array_traverse( self::get_input_variables(), array_build_traversable_path( $dot_path ), $default );
	}

	/**
	 * Determines whether a file has been provided in this request.
	 *
	 * @param string $file_reference The reference for the file that is being checked for.
	 *
	 * @return bool Whether the file has been provided in this request.
	 */
	public static function has_file( $file_reference ) {
		return ! empty( self::get_file( $file_reference ) );
	}

	/**
	 * Retrieves all of the files within the request.
	 *
	 * @return array All of the files with the request.
	 */
	public static function get_files() {
		return $_FILES; // phpcs:ignore
	}

	/**
	 * Retrieves an individual file set within the request.
	 *
	 * @param string $file_reference The reference for the file that is to be retrieved.
	 *
	 * @return array An individual file set within the request.
	 */
	public static function get_file( $file_reference ) {
		return array_traverse( self::get_files(), array_build_traversable_path( $file_reference ), array() );
	}

}
