<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Plugin Framework
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Your Name or Your Company
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Cheatin\' uh?' );
}

use Plugin_Name\Core\Classes;
use Plugin_Name\Core\Functions;
use Plugin_Name\Core\Localisation;
use Plugin_Name\Core\Requirements;
use Plugin_Name\Core\Router;

/**
 * Ensure that this plugin isn't already active.
 */
if ( ! class_exists( 'Plugin_Name' ) ) {

	/**
	 * The main class for helping develop websites.
	 *
	 * @package Plugin_Name
	 */
	class Plugin_Name {

		/**
		 * The unique identifier of this plugin.
		 *
		 * @var string The plugin identifier.
		 */
		const PLUGIN_ID = 'plugin-name';

		/**
		 * The name identifier of this plugin.
		 *
		 * @var string The plugin name.
		 */
		const PLUGIN_NAME = 'WordPress Plugin Framework';

		/**
		 * The namespace used within this plugin.
		 *
		 * @var string The plugin namespace.
		 */
		const PLUGIN_NAMESPACE = 'Plugin_Name';

		/**
		 * The current version of the plugin.
		 *
		 * @var string The plugin version.
		 */
		const PLUGIN_VERSION = '1.0.0';

		/**
		 * Main plugin path /wp-content/plugins/<plugin-folder>/.
		 *
		 * @var string $plugin_path Main plugin path.
		 */
		protected static $plugin_path;

		/**
		 * The plugin's basename to /wp-content/plugins/<plugin-folder>/<base-plugin-file>.php.
		 *
		 * @var string $plugin_basename The plugin's basename.
		 */
		protected static $plugin_basename;

		/**
		 * Absolute plugin url <wordpress-root-folder>/wp-content/plugins/<plugin-folder>/.
		 *
		 * @var string $plugin_url Absolute plugin url.
		 */
		protected static $plugin_url;

		/**
		 * Define the core functionality of the plugin.
		 */
		public static function boot() {

			// Set the plugin path to /wp-content/plugins/<plugin-folder>/.
			self::set_plugin_path( plugin_dir_path( __FILE__ ) );

			// Set the plugin basename to /wp-content/plugins/<plugin-folder>/<base-plugin-file>.php.
			self::set_plugin_basename( plugin_basename( __FILE__ ) );

			// Set the plugin URL to <wordpress-root-folder>/wp-content/plugins/<plugin-folder>/.
			self::set_plugin_url( plugin_dir_url( __FILE__ ) );

			// Handle autoloading the classes defined within this plugin.
			require_once self::get_plugin_path( 'core/class-classes.php' );
			Classes::autoload_plugin_classes();

			// Handle loading the functions defined within this plugin.
			Functions::load_plugin_functions();

			// Localise the wording used within this plugin.
			Requirements::hook_plugin_requirements_check();

			// Localise the wording used within this plugin.
			Localisation::hook_plugin_localisation();

			// Run the routes that match the current session.
			Router::hook_plugin_routes();

		}

		/**
		 * Activates the plugin.
		 */
		public static function activate() {
			activate_plugins( self::get_plugin_basename() );
		}

		/**
		 * Deactivates the plugin.
		 */
		public static function deactivate() {
			deactivate_plugins( self::get_plugin_basename() );
		}

		/**
		 * Set plugin's main path.
		 *
		 * @param string $plugin_path Main plugin path.
		 */
		protected static function set_plugin_path( $plugin_path ) {
			self::$plugin_path = $plugin_path;
		}

		/**
		 * Get plugin's main path.
		 *
		 * @param string $path The path to append to the plugin path.
		 *
		 * @return string Main plugin path.
		 */
		public static function get_plugin_path( $path = '' ) {
			return self::$plugin_path . $path;
		}

		/**
		 * Set plugin's basename.
		 *
		 * @param string $plugin_basename The plugin's basename.
		 */
		protected static function set_plugin_basename( $plugin_basename ) {
			self::$plugin_basename = $plugin_basename;
		}

		/**
		 * Get plugin's basename.
		 *
		 * @return string The plugin's basename.
		 */
		public static function get_plugin_basename() {
			return self::$plugin_basename;
		}

		/**
		 * Set plugin's absolute url.
		 *
		 * @param string $plugin_url Absolute plugin url.
		 */
		protected static function set_plugin_url( $plugin_url ) {
			self::$plugin_url = $plugin_url;
		}

		/**
		 * Get plugin's absolute url.
		 *
		 * @param string $path The path to append to the plugin URL.
		 *
		 * @return string Absolute plugin url.
		 */
		public static function get_plugin_url( $path = '' ) {
			return self::$plugin_url . $path;
		}

	}

	// Boot the plugin.
	Plugin_Name::boot();

}
