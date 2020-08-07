<?php
/**
 * Includes the class for managing the plugins routes.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Module\Page_Routing;

use Plugin_Name\Core\Config;
use Plugin_Name\Core\Request;
use Plugin_Name\Core\Route;
use Plugin_Name\Core\Router;
use WP;

/**
 * Handles all of the routes within this plugin.
 */
class Page_Router extends Router {

	/**
	 * Stores a list of all of the page routes defined within this plugin.
	 *
	 * @var array An array with all of the page routes defined within this plugin.
	 */
	protected static $routes = array();

	/**
	 * Defines all of the existing routes for this plugin.
	 */
	public static function route_page() {
		self::add_routes( Config::get_config( 'page-routes' ) );
		self::hook_plugin_route_filters();
		self::hook_dispatching_plugin_routes();
	}

	/**
	 * Adds multiple routes to this plugin.
	 *
	 * @param array $routes An array of all the routes to add to this plugin.
	 */
	public static function add_routes( $routes ) {
		foreach ( $routes as $route_id => $route ) {
			self::add_route( $route_id, $route );
		}
	}

	/**
	 * Adds a single page route to this this plugin.
	 *
	 * @param string           $route_id The ID for the new route.
	 * @param array|Page_Route $route    The page route to add.
	 */
	public static function add_route( $route_id, $route ) {
		self::$routes[ $route_id ] = is_a( $route, Page_Route::class ) ? $route : new Page_Route( $route );
	}

	/**
	 * Retrieves routes defined within the system which can also be filtered and ordered using options.
	 *
	 * @param array $options The options to use to filter and order the routes.
	 *
	 * @return array An array containing all of the matching routes.
	 */
	public static function get_routes( $options = array() ) {
		return apply_filters( 'plugin_name_filter_page_routes', self::$routes, array_merge( self::get_default_route_options(), $options ) );
	}

	/**
	 * Retrieves a single route which best matches the given filtering and ordering options.
	 *
	 * @param array $options The options to use to filter and order the routes.
	 *
	 * @return Page_Route The best matching route.
	 */
	public static function get_route( $options = array() ) {
		$routes = self::get_routes( $options );
		return isset( $routes[0] ) ? $routes[0] : null;
	}

	/**
	 * Hooks the events used to dispatch the routes.
	 */
	public static function hook_dispatching_plugin_routes() {
		add_action( 'parse_request', array( self::class, 'wp_action_route_page' ) );
	}

	/**
	 * Hooks the filters used to refine the `get_routes` method.
	 */
	public static function hook_plugin_route_filters() {

		// Filtering routes.
		add_filter( 'plugin_name_filter_page_routes', array( self::class, 'filter_routes_by_method' ), 10, 2 );
		add_filter( 'plugin_name_filter_page_routes', array( self::class, 'filter_routes_by_url_path' ), 20, 2 );
		add_filter( 'plugin_name_filter_page_routes', array( self::class, 'filter_routes_by_middleware' ), 30, 2 );

		// Ordering routes.
		add_filter( 'plugin_name_filter_page_routes', array( self::class, 'order_routes_by_priority' ), 40, 2 );
		add_filter( 'plugin_name_filter_page_routes', array( self::class, 'order_routes_by_url_path_score' ), 50, 2 );

	}

	/**
	 * Sets the best making route as the current page.
	 *
	 * @param WP $wp Current WordPress environment instance.
	 */
	public static function wp_action_route_page( $wp ) {
		$page_route = self::get_route(
			array(
				'method'              => Request::get_method(),
				'url_path'            => Request::get_url_path(),
				'validate_middleware' => true,
				'order'               => 'url_path_score',
			)
		);

		// Handle a matched URL path route.
		if ( $page_route ) {
			$wp->query_vars    = $page_route->get_query_vars_for_url_path( Request::get_url_path() );
			$wp->matched_rule  = $page_route->get_url_path_regex();
			$wp->matched_query = $page_route->get_url_path_rewrite( Request::get_url_path() );
			$page_route->dispatch( Request::get_url_path() );
		}
	}

	/**
	 * Reorders the provided routes by URL path score.
	 *
	 * @param array $routes  The routes to reorder.
	 * @param array $options The options for filtering and reordering the routes.
	 *
	 * @return array The reordered routes.
	 */
	public static function order_routes_by_url_path_score( $routes, $options ) {
		if ( 'url_path_score' === $options['order'] ) {
			usort(
				$routes,
				/**
				 * Reorders the routes by their URL path score.
				 *
				 * @param Route $route_1 The first route to compare URL path scores with.
				 * @param Route $route_2 The second route to compare URL path scores with.
				 *
				 * @return bool Whether the route should be reordered.
				 */
				function ( $route_1, $route_2 ) use ( $options ) {
					return 'DESC' === $options['order_by'] ?
						$route_1->get_url_path_score( $options['url_path'] ) < $route_2->get_url_path_score( $options['url_path'] ) :
						$route_1->get_url_path_score( $options['url_path'] ) > $route_2->get_url_path_score( $options['url_path'] );
				}
			);
		}
		return $routes;
	}

}
