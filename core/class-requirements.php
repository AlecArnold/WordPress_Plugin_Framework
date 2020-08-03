<?php
/**
 * Includes the class for managing plugin requirements.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use Plugin_Name;
use WP_Error;
use function Plugin_Name\Functions\Array_Utils\array_build_traversable_path;
use function Plugin_Name\Functions\Array_Utils\array_traverse;

/**
 * Handles validating the PHP version, WordPress version, multisite and installed plugin requirements.
 */
class Requirements {

	/**
	 * Holds the code used to reference any requirement errors that occur.
	 *
	 * @var string The error code.
	 */
	const WP_ERROR_CODE = 'plugin-name-requirement-error';

	/**
	 * Stores all of the plugin requirement options.
	 *
	 * @var array An array containing all of the plugin requirement options.
	 */
	protected static $requirements = array();

	/**
	 * Stores whether the plugin requirements have been set.
	 *
	 * @var bool Whether the plugin requirements have been set.
	 */
	protected static $are_requirements_set = false;

	/**
	 * Stores the option for whether the site is multisite compatible. Default `true`.
	 *
	 * @var bool Whether the plugin is compatible with multisites.
	 */
	protected static $is_multisite_compatible = true;

	/**
	 * Stores whether the multisite compatibility have been set.
	 *
	 * @var bool Whether the multisite compatibility have been set.
	 */
	protected static $is_multisite_compatible_set = false;

	/**
	 * Holds the error message for multisite compatibility.
	 *
	 * @var string The multisite compatibility error message.
	 */
	protected static $multisite_compatible_error_message = 'This plugin is not compatible with multisite environment.';

	/**
	 * Stores the minimum PHP version required to run this plugin. Default `any`.
	 *
	 * @var string The minimum PHP version required to run this plugin.
	 */
	protected static $minimum_php_version = 'any';

	/**
	 * Stores whether the minimum PHP version has been set.
	 *
	 * @var bool Whether the minimum PHP version has been set.
	 */
	protected static $is_minimum_php_version_set = false;

	/**
	 * Holds the PHP version error message.
	 *
	 * @var string The PHP version error message.
	 */
	protected static $minimum_php_version_error_message = 'This plugin requires WordPress version %s+ to run.';

	/**
	 * Stores the minimum WordPress version required to run this plugin. Default `any`.
	 *
	 * @var string The minimum WordPress version required to run this plugin.
	 */
	protected static $minimum_wp_version = 'any';

	/**
	 * Stores whether the minimum WordPress version has been set.
	 *
	 * @var bool Whether the minimum WordPress version has been set.
	 */
	protected static $is_minimum_wp_version_set = false;

	/**
	 * Holds the WordPress version error message.
	 *
	 * @var string The WordPress version error message.
	 */
	protected static $minimum_wp_version_error_message = 'This plugin requires WordPress version %s+ to run.';

	/**
	 * Stores the plugins that are required to make this plugin function.
	 *
	 * @var array An array of plugins that are required to make this plugin function.
	 */
	protected static $required_plugins = array();

	/**
	 * Stores whether the required plugins have been set.
	 *
	 * @var bool Whether the required plugins have been set.
	 */
	protected static $are_required_plugins_set = false;

	/**
	 * Holds the required plugin error message.
	 *
	 * @var string The required plugin error message.
	 */
	protected static $required_plugin_error_message = 'This plugin requires "%s" to be installed and activated.';

	/**
	 * Stores the WP_Error object containing all of the errors encountered.
	 *
	 * @var WP_Error The WP_Error object containing all of the errors encountered.
	 */
	protected static $requirement_errors;

	/**
	 * Stores whether the plugin errors have been set.
	 *
	 * @var bool Whether the plugin errors have been set.
	 */
	protected static $are_requirement_errors_set = false;

	/**
	 * Handles the static construction event for this class.
	 */
	public static function enforce_plugin_requirements() {
		self::hook_plugin_requirements_check();
	}

	/**
	 * Sets all of the requirements for this plugin.
	 *
	 * @param array $requirements The requirements for this plugin.
	 */
	protected static function set_requirements( $requirements ) {
		self::$requirements               = $requirements;
		self::$are_requirements_set       = true;
		self::$are_requirement_errors_set = false;
	}

	/**
	 * Retrieves all of the plugin requirement options.
	 *
	 * @return array An array containing all of the plugin requirement options.
	 */
	public static function get_requirements() {
		if ( ! self::$are_requirements_set ) {
			self::set_requirements( Config::get_config( 'requirements' ) );
		}
		return self::$requirements;
	}

	/**
	 * Retrieves a single plugin requirement option.
	 *
	 * @param string $requirement_reference The reference for the requirement that is to be retrieved.
	 * @param mixed  $default               The default value to return when the option isn't set.
	 *
	 * @return mixed A single plugin requirement option value.
	 */
	public static function get_requirement( $requirement_reference, $default = null ) {
		return array_traverse( self::get_requirements(), array_build_traversable_path( $requirement_reference ), $default );
	}

	/** =====================================================================
	 * Multisite Compatible
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets whether this plugin is multisite compatible.
	 *
	 * @param bool $is_multisite_compatible Whether this plugin is multisite compatible.
	 */
	public static function set_is_multisite_compatible( $is_multisite_compatible ) {
		self::$is_multisite_compatible     = $is_multisite_compatible;
		self::$is_multisite_compatible_set = true;
		self::$are_requirement_errors_set  = false;
	}

	/**
	 * Retrieves whether this plugin is multisite compatible.
	 *
	 * @return bool Whether this plugin is multisite compatible.
	 */
	public static function get_is_multisite_compatible() {
		if ( ! self::$is_multisite_compatible_set ) {
			self::set_is_multisite_compatible( self::get_requirement( 'is_multisite_compatible' ) );
		}
		return self::$is_multisite_compatible;
	}

	/**
	 * Determines whether the multisite compatibility requirement has been met.
	 *
	 * @return bool Whether the multisite compatibility requirement has been met.
	 */
	public static function is_multisite_compatible_requirement_met() {
		return ! is_multisite() || ( is_multisite() && self::get_is_multisite_compatible() );
	}

	/**
	 * Sets the multisite compatible error message.
	 *
	 * @param string $multisite_compatible_error_message The multisite compatible error message.
	 */
	public static function set_multisite_compatible_error_message( $multisite_compatible_error_message ) {
		self::$multisite_compatible_error_message = $multisite_compatible_error_message;
	}

	/**
	 * Generates the multisite compatibility error message.
	 *
	 * @return string The multisite compatibility error message.
	 */
	public static function get_multisite_compatible_error_message() {
		return __( self::$multisite_compatible_error_message, Localisation::get_text_domain() ); // phpcs:ignore
	}

	/** =====================================================================
	 * Minimum PHP Version
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the minimum PHP version required to run this plugin.
	 *
	 * @param string $minimum_php_version The minimum PHP version required to run this plugin.
	 */
	public static function set_minimum_php_version( $minimum_php_version ) {
		self::$minimum_php_version        = $minimum_php_version;
		self::$is_minimum_php_version_set = true;
		self::$are_requirement_errors_set = false;
	}

	/**
	 * Retrieves the minimum PHP version required to run this plugin.
	 *
	 * @return string The minimum PHP version required to run this plugin.
	 */
	public static function get_minimum_php_version() {
		if ( ! self::$is_minimum_php_version_set ) {
			self::set_minimum_php_version( self::get_requirement( 'minimum_php_version' ) );
		}
		return self::$minimum_php_version;
	}

	/**
	 * Determines whether the minimum PHP version requirement has been met.
	 *
	 * @return bool Whether the minimum PHP version requirement has been met.
	 */
	public static function is_minimum_php_version_requirement_met() {
		return version_compare( PHP_VERSION, self::get_minimum_php_version(), '>=' );
	}

	/**
	 * Sets the minimum PHP version error message. "%s" is replaced with the minimum required version.
	 *
	 * @param string $minimum_php_version_error_message The minimum PHP version error message.
	 */
	public static function set_minimum_php_version_error_message( $minimum_php_version_error_message ) {
		self::$minimum_php_version_error_message = $minimum_php_version_error_message;
	}

	/**
	 * Generates the minimum WordPress version error message.
	 *
	 * @return string The minimum WordPress version error message.
	 */
	public static function get_minimum_php_version_error_message() {
		return __( sprintf( self::$minimum_php_version_error_message, self::get_minimum_php_version() ), Localisation::get_text_domain() ); // phpcs:ignore
	}

	/** =====================================================================
	 * Minimum WordPress Version
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the minimum WordPress version required to run this plugin.
	 *
	 * @param string $minimum_wp_version The minimum WordPress version required to run this plugin.
	 */
	public static function set_minimum_wp_version( $minimum_wp_version ) {
		self::$minimum_wp_version         = $minimum_wp_version;
		self::$is_minimum_wp_version_set  = true;
		self::$are_requirement_errors_set = false;
	}

	/**
	 * Retrieves the minimum WordPress version required to run this plugin.
	 *
	 * @return string The minimum WordPress version required to run this plugin.
	 */
	public static function get_minimum_wp_version() {
		if ( ! self::$is_minimum_wp_version_set ) {
			self::set_minimum_wp_version( self::get_requirement( 'minimum_wp_version' ) );
		}
		return self::$minimum_wp_version;
	}

	/**
	 * Determines whether the minimum WordPress version requirement has been met.
	 *
	 * @return bool Whether the minimum WordPress version requirement has been met.
	 */
	public static function is_minimum_wp_version_requirement_met() {
		return version_compare( Request::get_global_variable( 'wp_version' ), self::get_minimum_wp_version(), '>=' );
	}

	/**
	 * Sets the minimum WordPress version error message. "%s" is replaced with the minimum required version.
	 *
	 * @param string $minimum_wp_version_error_message The minimum WordPress version error message.
	 */
	public static function set_minimum_wp_version_error_message( $minimum_wp_version_error_message ) {
		self::$minimum_wp_version_error_message = $minimum_wp_version_error_message;
	}

	/**
	 * Generates the minimum PHP version error message.
	 *
	 * @return string The minimum PHP version error message.
	 */
	public static function get_minimum_wp_version_error_message() {
		return __( sprintf( self::$minimum_wp_version_error_message, self::get_minimum_wp_version() ), Localisation::get_text_domain() ); // phpcs:ignore
	}

	/** =====================================================================
	 * WordPress Plugin Dependencies
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the plugins that are required to make this plugin function.
	 *
	 * @param array $required_plugins An array of plugins that are required to make this plugin function.
	 */
	public static function set_required_plugins( $required_plugins ) {
		self::$required_plugins           = $required_plugins;
		self::$are_required_plugins_set   = true;
		self::$are_requirement_errors_set = false;
	}

	/**
	 * Retrieves the plugins that are required to make this plugin function.
	 *
	 * @return array An array of plugins that are required to make this plugin function.
	 */
	public static function get_required_plugins() {
		if ( ! self::$are_required_plugins_set ) {
			self::set_required_plugins( self::get_requirement( 'required_plugins' ) );
		}
		return self::$required_plugins;
	}

	/**
	 * Determines whether a provided plugin is installed and activated.
	 *
	 * @param string $required_plugin_path The plugin to confirm whether it is installed and activated.
	 *
	 * @return bool Whether the provided plugin is installed and activated.
	 */
	public static function is_plugin_active( $required_plugin_path ) {
		return is_plugin_active( $required_plugin_path );
	}

	/**
	 * Sets the required plugin error message. "%s" is replaced with the plugin name.
	 *
	 * @param string $required_plugin_error_message The required plugin error message.
	 */
	public static function set_required_plugin_error_message( $required_plugin_error_message ) {
		self::$required_plugin_error_message = $required_plugin_error_message;
	}

	/**
	 * Generates an error for a required plugin.
	 *
	 * @param string $plugin_name The name of the plugin that the error message is for.
	 *
	 * @return string The required plugin error message.
	 */
	public static function get_required_plugin_error_message( $plugin_name ) {
		return __( sprintf( self::$required_plugin_error_message, $plugin_name ), Localisation::get_text_domain() ); // phpcs:ignore
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the WP_Error object containing all of the errors encountered.
	 *
	 * @param WP_Error $requirement_errors The WP_Error object containing all of the errors encountered.
	 */
	public static function set_requirement_errors( $requirement_errors ) {
		self::$requirement_errors         = $requirement_errors;
		self::$are_requirement_errors_set = true;
	}

	/**
	 * Derives the WP_Error object containing all of the errors encountered.
	 *
	 * @return WP_Error The WP_Error object containing all of the errors encountered.
	 */
	public static function derive_requirement_errors() {
		$requirement_wp_error = new WP_Error();

		// Adds an error when the multisite requirement has not been met.
		if ( ! self::is_multisite_compatible_requirement_met() ) {
			$requirement_wp_error->add( self::WP_ERROR_CODE, self::get_multisite_compatible_error_message() );
		}

		// Adds an error when the PHP version requirement has not been met.
		if ( ! self::is_minimum_php_version_requirement_met() ) {
			$requirement_wp_error->add( self::WP_ERROR_CODE, self::get_minimum_php_version_error_message() );
		}

		// Adds an error when the WordPress version requirement has not been met.
		if ( ! self::is_minimum_wp_version_requirement_met() ) {
			$requirement_wp_error->add( self::WP_ERROR_CODE, self::get_minimum_wp_version_error_message() );
		}

		// Adds an error when the required plugins are not installed and activated.
		foreach ( self::get_required_plugins() as $required_plugin_path => $required_plugin_name ) {

			// Check whether an error needs to be added for the current plugin.
			if ( ! self::is_plugin_active( $required_plugin_path ) ) {
				$requirement_wp_error->add( self::WP_ERROR_CODE, self::get_required_plugin_error_message( $required_plugin_name ) );
			}
		}
		return $requirement_wp_error;
	}

	/**
	 * Determines whether all of the plugin requirements have been met.
	 *
	 * @return bool Whether all of the plugin requirements have been met.
	 */
	public static function are_requirements_met() {
		return ! self::get_requirement_errors()->has_errors();
	}

	/**
	 * Retrieves the WP_Error object containing all of the requirement errors.
	 *
	 * @return WP_Error The WP_Error object containing all of the requirement errors.
	 */
	public static function get_requirement_errors() {
		if ( ! self::$are_requirement_errors_set ) {
			self::set_requirement_errors( self::derive_requirement_errors() );
		}
		return self::$requirement_errors;
	}

	/**
	 * Hook the activation event to check the plugin requirements.
	 */
	public static function hook_plugin_requirements_check() {
		register_activation_hook( Plugin_Name::get_plugin_basename(), array( self::class, 'wp_action_check_plugin_requirements' ) );
	}

	/**
	 * Handle the activation event to check the plugin requirements.
	 */
	public static function wp_action_check_plugin_requirements() {
		if ( ! self::are_requirements_met() ) {
			wp_die( self::get_requirement_errors() ); // phpcs:ignore
		}
	}

}
