<?php
/**
 * Includes the class for managing localisation.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use Plugin_Name;

/**
 * Handles the translation of the plugin text.
 */
class Localisation {

	/**
	 * The domain specified for this plugin.
	 *
	 * @var string $domain The domain identifier for this plugin.
	 */
	protected static $domain;

	/**
	 * Handle the construction of the static Localisation object.
	 */
	public static function __constructStatic() {
		self::set_domain( Plugin_Name::PLUGIN_ID );
	}

	/**
	 * Set the domain equal to that of the specified domain.
	 *
	 * @param string $domain The domain that represents the locale of this plugin.
	 */
	public static function set_domain( $domain ) {
		self::$domain = $domain;
	}

	/**
	 * Get the domain equal to that of the specified domain.
	 *
	 * @return string The domain that represents the locale of this plugin.
	 */
	public static function get_domain() {
		return self::$domain;
	}

	/**
	 * Hooks the WordPress action used to translate the plugin.
	 */
	public static function hook_plugin_localisation() {
		add_action( 'plugins_loaded', array( self::class, 'wp_action_load_plugin_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public static function wp_action_load_plugin_textdomain() {
		load_plugin_textdomain( self::get_domain(), false, Plugin_Name::get_plugin_path( 'languages' ) );
	}

}
