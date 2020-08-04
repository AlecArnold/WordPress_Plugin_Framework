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
	 * Stores the text domain specified for this plugin.
	 *
	 * @var string $text_domain The text domain identifier for this plugin.
	 */
	protected static $text_domain;

	/**
	 * Stores whether the plugins text domain been set.
	 *
	 * @var bool Whether the plugins text domain been set.
	 */
	protected static $is_text_domain_set = false;

	/**
	 * Stores the directory containing all of the language conversions.
	 *
	 * @var string The directory containing all of the language conversions.
	 */
	protected static $language_directory;

	/**
	 * Stores whether the directory containing all of the language conversions ahs been set.
	 *
	 * @var bool Whether the directory containing all of the language conversions ahs been set.
	 */
	protected static $is_language_directory_set = false;

	/**
	 * Handles setting up the plugin localisation.
	 */
	public static function localise_plugin() {
		self::hook_plugin_localisation();
	}

	/**
	 * Set the domain equal to that of the specified domain.
	 *
	 * @param string $text_domain The domain that represents the locale of this plugin.
	 */
	public static function set_text_domain( $text_domain ) {
		self::$text_domain        = $text_domain;
		self::$is_text_domain_set = true;
	}

	/**
	 * Get the domain equal to that of the specified domain.
	 *
	 * @return string The domain that represents the locale of this plugin.
	 */
	public static function get_text_domain() {
		if ( ! self::$is_text_domain_set ) {
			self::set_text_domain( Plugin_Name::PLUGIN_ID );
		}
		return self::$text_domain;
	}

	/**
	 * Sets the directory containing all of the language conversions.
	 *
	 * @param string $language_directory The directory containing all of the language conversions.
	 */
	public static function set_language_directory( $language_directory ) {
		self::$language_directory        = $language_directory;
		self::$is_language_directory_set = true;
	}

	/**
	 * Retrieves the default directory that contains the translations for this plugin.
	 *
	 * @return string The default directory that contains the translations.
	 */
	public static function get_default_language_directory() {
		return Plugin_Name::get_plugin_path( 'languages' );
	}

	/**
	 * Retrieves the directory containing all of the language conversions.
	 *
	 * @return string The directory containing all of the language conversions.
	 */
	public static function get_language_directory() {
		if ( ! self::$is_language_directory_set ) {
			self::set_language_directory( self::get_default_language_directory() );
		}
		return self::$language_directory;
	}

	/**
	 * Hooks the WordPress action used to translate the plugin.
	 */
	public static function hook_plugin_localisation() {
		add_action( 'plugins_loaded', array( self::class, 'wp_action_load_plugin_text_domain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public static function wp_action_load_plugin_text_domain() {
		load_plugin_textdomain( self::get_text_domain(), false, self::get_language_directory() );
	}

}
