<?php

namespace Plugin_Name\Core;

use Plugin_Name;
use WP_Error;

/**
 *
 *
 * @package Plugin_Name
 */
class Requirements {

	/**
	 *
	 */
	const WP_ERROR_CODE = 'plugin-name-requirement-error';

	/**
	 *
	 */
	protected static $is_multisite_compatible = true;

	/**
	 *
	 */
	protected static $minimum_php_version = 'any';

	/**
	 *
	 */
	protected static $minimum_wp_version = 'any';

	/**
	 *
	 */
	protected static $required_plugins = array();

	/**
	 *
	 */
	protected static $requirement_errors;

	/**
	 *
	 */
	public static function __constructStatic() {
		self::set_requirements( Config::get_config( 'requirements' ) );
	}

	/**
	 *
	 */
	public static function set_requirements( $requirements ) {

		//
		if ( isset( $requirements['is_multisite_compatible'] ) ) {
			self::set_is_multisite_compatible( $requirements['is_multisite_compatible'] );
		}

		//
		if ( isset( $requirements['minimum_php_version'] ) ) {
			self::set_minimum_php_version( $requirements['minimum_php_version'] );
		}

		//
		if ( isset( $requirements['minimum_wp_version'] ) ) {
			self::set_minimum_wp_version( $requirements['minimum_wp_version'] );
		}

		//
		if ( isset( $requirements['required_plugins'] ) ) {
			self::set_required_plugins( $requirements['required_plugins'] );
		}
	}

	/** =====================================================================
	 * Multisite Compatible
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public static function set_is_multisite_compatible( $is_multisite_compatible ) {
		self::$is_multisite_compatible = $is_multisite_compatible;
	}

	/**
	 *
	 */
	public static function get_is_multisite_compatible() {
		return self::$is_multisite_compatible;
	}

	/**
	 *
	 */
	public static function is_multisite_compatible_requirement_met() {
		return ! is_multisite() || ( is_multisite() && self::get_is_multisite_compatible() );
	}

	/** =====================================================================
	 * Minimum PHP Version
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public static function set_minimum_php_version( $minimum_php_version ) {
		self::$minimum_php_version = $minimum_php_version;
	}

	/**
	 *
	 */
	public static function get_minimum_php_version() {
		return self::$minimum_php_version;
	}

	/**
	 *
	 */
	public static function is_minimum_php_version_requirement_met() {
		return version_compare( PHP_VERSION, self::get_minimum_php_version(), '>=' );
	}

	/** =====================================================================
	 * Minimum WordPress Version
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public static function set_minimum_wp_version( $minimum_wp_version ) {
		self::$minimum_wp_version = $minimum_wp_version;
	}

	/**
	 *
	 */
	public static function get_minimum_wp_version() {
		return self::$minimum_wp_version;
	}

	/**
	 *
	 */
	public static function is_minimum_wp_version_requirement_met() {
		return version_compare( $GLOBALS['wp_version'], self::get_minimum_wp_version(), '>=' );
	}

	/** =====================================================================
	 * WordPress Plugin Dependencies
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public static function set_required_plugins( $required_plugins ) {
		self::$required_plugins = $required_plugins;
	}

	/**
	 *
	 */
	public static function get_required_plugins() {
		return self::$required_plugins;
	}

	/**
	 *
	 */
	public static function is_plugin_active( $required_plugin_path ) {
		return is_plugin_active( $required_plugin_path );
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public static function have_requirements_been_checked() {
		return ! empty( self::$requirement_errors );
	}

	/**
	 *
	 */
	public static function are_requirements_met() {
		return ! self::get_requirement_errors()->has_errors();
	}

	/**
	 *
	 */
	public static function get_requirement_errors() {
		if ( ! self::have_requirements_been_checked() ) {
			self::set_requirement_errors();
		}
		return self::$requirement_errors;
	}

	/**
	 *
	 */
	public static function set_requirement_errors() {
		self::$requirement_errors = new WP_Error();

		//
		if ( ! self::is_multisite_compatible_requirement_met() ) {
			self::$requirement_errors->add( self::WP_ERROR_CODE, __( 'This plugin is not compatible with multisite environment.', Localisation::get_domain() ) );
		}

		//
		if ( ! self::is_minimum_php_version_requirement_met() ) {
			self::$requirement_errors->add( self::WP_ERROR_CODE, __( sprintf( 'This plugin requires PHP version %s+ to run.', self::get_minimum_php_version() ), Localisation::get_domain() ) );
		}

		//
		if ( ! self::is_minimum_wp_version_requirement_met() ) {
			self::$requirement_errors->add( self::WP_ERROR_CODE, __( sprintf( 'This plugin requires WordPress version %s+ to run.', self::get_minimum_wp_version() ), Localisation::get_domain() ) );
		}

		//
		foreach ( self::get_required_plugins() as $required_plugin_path => $required_plugin_name ) {

			//
			if ( ! self::is_plugin_active( $required_plugin_path ) ) {
				self::$requirement_errors->add( self::WP_ERROR_CODE, __( sprintf( 'This plugin requires "%s" to be installed and activated.', $required_plugin_name ), Localisation::get_domain() ) );
			}
		}
	}

	/**
	 *
	 */
	public static function hook_plugin_requirements_check() {
		register_activation_hook( Plugin_Name::get_plugin_basename(), array( self::class, 'wp_action_check_plugin_requirements' ) );
	}

	/**
	 *
	 */
	public static function wp_action_check_plugin_requirements() {
		if ( ! self::are_requirements_met() ) {
			wp_die( self::get_requirement_errors() );
		}
	}

}
