<?php
/**
 * Includes the class for managing plugin requirements.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use Plugin_Name;
use WP_Error;

/**
 * Handles validating the PHP version, WP version, multisite and installed plugin requirements.
 */
class Requirements {

	/**
	 * Holds the code used to reference any requirement errors that occur.
	 *
	 * @var string The error code.
	 */
	const WP_ERROR_CODE = 'plugin-name-requirement-error';

	/**
	 * Holds the error message for multisite compatibility.
	 *
	 * @var string The multisite compatibility error message.
	 */
	const MULTISITE_COMPATIBLE_ERROR_MESSAGE = 'This plugin is not compatible with multisite environment.';

	/**
	 * Holds the PHP version error message.
	 *
	 * @var string The PHP version error message.
	 */
	const MINIMUM_PHP_VERSION_ERROR_MESSAGE = 'This plugin requires WordPress version %s+ to run.';

	/**
	 * Holds the WordPress version error message.
	 *
	 * @var string The WordPress version error message.
	 */
	const MINIMUM_WP_VERSION_ERROR_MESSAGE = 'This plugin requires WordPress version %s+ to run.';

	/**
	 * Holds the required plugin error message.
	 *
	 * @var string The required plugin error message.
	 */
	const REQUIRED_PLUGIN_ERROR_MESSAGE = 'This plugin requires "%s" to be installed and activated.';

	/**
	 * Stores the option for whether the site is multisite compatible. Default `true`.
	 *
	 * @var bool Whether the plugin is compatible with multisites.
	 */
	protected static $is_multisite_compatible = true;

	/**
	 * Stores the minimum PHP version required to run this plugin. Default `any`.
	 *
	 * @var string The minimum PHP version required to run this plugin.
	 */
	protected static $minimum_php_version = 'any';

	/**
	 * Stores the minimum WordPress version required to run this plugin. Default `any`.
	 *
	 * @var string The minimum WordPress version required to run this plugin.
	 */
	protected static $minimum_wp_version = 'any';

	/**
	 * Stores the plugins that are required to make this plugin function.
	 *
	 * @var array An array of plugins that are required to make this plugin function.
	 */
	protected static $required_plugins = array();

	/**
	 * Stores the WP_Error object containing all of the errors encountered.
	 *
	 * @var WP_Error The WP_Error object containing all of the errors encountered.
	 */
	protected static $requirement_errors;

	/**
	 * Handles the static construction event for this class.
	 */
	public static function static_constructor() {
		self::set_requirements( Config::get_config( 'requirements' ) );
	}

	/**
	 * Sets all of the requirements for this plugin.
	 *
	 * @param array $requirements The requirements for this plugin.
	 */
	protected static function set_requirements( $requirements ) {

		// Sets whether this plugin is multisite compatible.
		if ( isset( $requirements['is_multisite_compatible'] ) ) {
			self::set_is_multisite_compatible( $requirements['is_multisite_compatible'] );
		}

		// Sets the minimum PHP version required.
		if ( isset( $requirements['minimum_php_version'] ) ) {
			self::set_minimum_php_version( $requirements['minimum_php_version'] );
		}

		// Sets the minimum WordPress version required.
		if ( isset( $requirements['minimum_wp_version'] ) ) {
			self::set_minimum_wp_version( $requirements['minimum_wp_version'] );
		}

		// Sets all of the other plugins that need to be installed.
		if ( isset( $requirements['required_plugins'] ) ) {
			self::set_required_plugins( $requirements['required_plugins'] );
		}
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
		self::$is_multisite_compatible = $is_multisite_compatible;
	}

	/**
	 * Retrieves whether this plugin is multisite compatible.
	 *
	 * @return bool Whether this plugin is multisite compatible.
	 */
	public static function get_is_multisite_compatible() {
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
	 * Generates the multisite compatibility error message.
	 *
	 * @return string The multisite compatibility error message.
	 */
	public static function get_multisite_compatible_error_message() {
		return __( self::MULTISITE_COMPATIBLE_ERROR_MESSAGE, Localisation::get_domain() );
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
		self::$minimum_php_version = $minimum_php_version;
	}

	/**
	 * Retrieves the minimum PHP version required to run this plugin.
	 *
	 * @return string The minimum PHP version required to run this plugin.
	 */
	public static function get_minimum_php_version() {
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
	 * Generates the minimum WordPress version error message.
	 *
	 * @return string The minimum WordPress version error message.
	 */
	public static function get_minimum_php_version_error_message() {
		return __( sprintf( self::MINIMUM_PHP_VERSION_ERROR_MESSAGE, self::get_minimum_php_version() ), Localisation::get_domain() );
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
		self::$minimum_wp_version = $minimum_wp_version;
	}

	/**
	 * Retrieves the minimum WordPress version required to run this plugin.
	 *
	 * @return string The minimum WordPress version required to run this plugin.
	 */
	public static function get_minimum_wp_version() {
		return self::$minimum_wp_version;
	}

	/**
	 * Determines whether the minimum WordPress version requirement has been met.
	 *
	 * @return bool Whether the minimum WordPress version requirement has been met.
	 */
	public static function is_minimum_wp_version_requirement_met() {
		return version_compare( $GLOBALS['wp_version'], self::get_minimum_wp_version(), '>=' );
	}

	/**
	 * Generates the minimum PHP version error message.
	 *
	 * @return string The minimum PHP version error message.
	 */
	public static function get_minimum_wp_version_error_message() {
		return __( sprintf( self::MINIMUM_WP_VERSION_ERROR_MESSAGE, self::get_minimum_wp_version() ), Localisation::get_domain() );
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
		self::$required_plugins = $required_plugins;
	}

	/**
	 * Retrieves the plugins that are required to make this plugin function.
	 *
	 * @return array An array of plugins that are required to make this plugin function.
	 */
	public static function get_required_plugins() {
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
	 * Generates an error for a required plugin.
	 *
	 * @param string $plugin_name The name of the plugin that the error message is for.
	 *
	 * @return string The required plugin error message.
	 */
	public static function get_required_plugin_error_message( $plugin_name ) {
		return __( sprintf( self::REQUIRED_PLUGIN_ERROR_MESSAGE, $plugin_name ), Localisation::get_domain() );
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets any errors caused by requirements not being met.
	 */
	public static function set_requirement_errors() {
		self::$requirement_errors = new WP_Error();

		// Adds an error when the multisite requirement has not been met.
		if ( ! self::is_multisite_compatible_requirement_met() ) {
			self::$requirement_errors->add( self::WP_ERROR_CODE, self::get_multisite_compatible_error_message() );
		}

		// Adds an error when the PHP version requirement has not been met.
		if ( ! self::is_minimum_php_version_requirement_met() ) {
			self::$requirement_errors->add( self::WP_ERROR_CODE, self::get_minimum_php_version_error_message() );
		}

		// Adds an error when the WordPress version requirement has not been met.
		if ( ! self::is_minimum_wp_version_requirement_met() ) {
			self::$requirement_errors->add( self::WP_ERROR_CODE, self::get_minimum_wp_version_error_message() );
		}

		// Adds an error when the required plugins are not installed and activated.
		foreach ( self::get_required_plugins() as $required_plugin_path => $required_plugin_name ) {

			// Check whether an error needs to be added for the current plugin.
			if ( ! self::is_plugin_active( $required_plugin_path ) ) {
				self::$requirement_errors->add( self::WP_ERROR_CODE, self::get_multisite_compatible_error_message() );
			}
		}
	}

	/**
	 * Determines whether the requirements have been checked.
	 *
	 * @return bool Whether the requirements have been checked.
	 */
	public static function have_requirements_been_checked() {
		return ! empty( self::$requirement_errors );
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
		if ( ! self::have_requirements_been_checked() ) {
			self::set_requirement_errors();
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
			wp_die( self::get_requirement_errors() );
		}
	}

}
