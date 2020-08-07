<?php
/**
 * Includes the class for managing the plugins routes.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use function Plugin_Name\Functions\Array_Utils\array_has_intersect;

/**
 * Handles all of the routes within this plugin.
 */
class Router {

	/**
	 * Stores a list of all of the routes defined within this plugin.
	 *
	 * @var array An array with all of the routes defined within this plugin.
	 */
	protected static $routes = array();

	/**
	 * Defines all of the existing routes for this plugin.
	 */
	public static function route_request() {
		self::add_routes( Config::get_config( 'routes' ) );
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
	 * Adds a single route to this plugin.
	 *
	 * @param string      $route_id The ID for the new route.
	 * @param array|Route $route    The route to add.
	 */
	public static function add_route( $route_id, $route ) {
		self::$routes[ $route_id ] = is_a( $route, Route::class ) ? $route : new Route( $route );
	}

	/**
	 * Removes a single route to this plugin.
	 *
	 * @param string $route_id The ID of the route to remove.
	 */
	public static function remove_route( $route_id ) {
		if ( self::has_route( $route_id ) ) {
			unset( self::$routes[ $route_id ] );
		}
	}

	/**
	 * Checks if a route has been set.
	 *
	 * @param string $route_id The ID of the route to check for.
	 *
	 * @return bool Whether a route with the provided ID is set.
	 */
	public static function has_route( $route_id ) {
		return self::$routes[ $route_id ];
	}

	/**
	 * Retrieves the default route options.
	 *
	 * @return array The default route options.
	 */
	public static function get_default_route_options() {
		return array(
			'url_path'            => null,
			'method'              => 'any',
			'validate_middleware' => false,
			'order_by'            => 'DESC',
			'order'               => null,
		);
	}

	/**
	 * Retrieves routes defined within the system which can also be filtered and ordered using options.
	 *
	 * @param array $options The options to use to filter and order the routes.
	 *
	 * @return array An array containing all of the matching routes.
	 */
	public static function get_routes( $options = array() ) {
		return apply_filters( 'plugin_name_filter_routes', self::$routes, array_merge( self::get_default_route_options(), $options ) );
	}

	/**
	 * Retrieves a single route which best matches the given filtering and ordering options.
	 *
	 * @param array $options The options to use to filter and order the routes.
	 *
	 * @return Route The best matching route.
	 */
	public static function get_route( $options = array() ) {
		$routes = self::get_routes( $options );
		return isset( $routes[0] ) ? $routes[0] : null;
	}

	/**
	 * Retrieves a route by the given ID.
	 *
	 * @param string $route_id The ID of the route to retrieve.
	 *
	 * @return Route|null The route with the provided ID.
	 */
	public static function get_route_by_id( $route_id ) {
		$routes = self::get_routes();
		return isset( $routes[ $route_id ] ) ? $routes[ $route_id ] : null;
	}

	/**
	 * Hooks the filters used to refine the `get_routes` method.
	 */
	public static function hook_plugin_route_filters() {

		// Filtering routes.
		add_filter( 'plugin_name_filter_routes', array( self::class, 'filter_routes_by_method' ), 10, 2 );
		add_filter( 'plugin_name_filter_routes', array( self::class, 'filter_routes_by_url_path' ), 20, 2 );
		add_filter( 'plugin_name_filter_routes', array( self::class, 'filter_routes_by_middleware' ), 30, 2 );

		// Ordering routes.
		add_filter( 'plugin_name_filter_routes', array( self::class, 'order_routes_by_priority' ), 40, 2 );

	}

	/**
	 * Hooks the events used to dispatch the routes.
	 */
	public static function hook_dispatching_plugin_routes() {
		add_action( 'plugins_loaded', array( self::class, 'wp_action_route_request' ) );
	}

	/**
	 * Triggers all of the routes that match the current request.
	 */
	public static function wp_action_route_request() {

		// Get the routes matching the current request.
		$request_routes = self::get_routes(
			array(
				'method'              => Request::get_method(),
				'validate_middleware' => true,
			)
		);

		// Run each method specific route.
		foreach ( $request_routes as $request_route ) {
			$request_route->dispatch();
		}

	}

	/**
	 * Filters routes by method.
	 *
	 * @param array $routes  The routes to filter.
	 * @param array $options The options for filtering the routes.
	 *
	 * @return array The routes that passed the filter.
	 */
	public static function filter_routes_by_method( $routes, $options ) {
		if ( 0 !== strcasecmp( 'any', $options['method'] ) || $options['method'] ) {
			$routes = array_filter(
				$routes,
				/**
				 * Determines whether a route should be filtered or not.
				 *
				 * @param Route $route The route to filter.
				 *
				 * @return bool Whether the route should be filtered out or not.
				 */
				function ( $route ) use ( $options ) {
					return array_has_intersect( array( 'ANY', $options['method'] ), $route->get_method() );
				}
			);
		}
		return $routes;
	}

	/**
	 * Filters routes by URL path.
	 *
	 * @param array $routes  The routes to filter.
	 * @param array $options The options for filtering the routes.
	 *
	 * @return array The routes that passed the filter.
	 */
	public static function filter_routes_by_url_path( $routes, $options ) {
		if ( $options['url_path'] ) {
			$routes = array_filter(
				$routes,
				/**
				 * Determines whether a route should be filtered or not.
				 *
				 * @param Route $route The route to filter.
				 *
				 * @return bool Whether the route should be filtered out or not.
				 */
				function ( $route ) use ( $options ) {
					return $route->has_url_path_regex() && $route->is_url_path_match( $options['url_path'] );
				}
			);
		}
		return $routes;
	}

	/**
	 * Filters routes by middleware.
	 *
	 * @param array $routes  The routes to filter.
	 * @param array $options The options for filtering the routes.
	 *
	 * @return array The routes that passed the filter.
	 */
	public static function filter_routes_by_middleware( $routes, $options ) {
		if ( $options['validate_middleware'] ) {
			$routes = array_filter(
				$routes,
				/**
				 * Determines whether a route should be filtered or not.
				 *
				 * @param Route $route The route to filter.
				 *
				 * @return bool Whether the route should be filtered out or not.
				 */
				function ( $route ) {
					return ! $route->has_middleware() || ( $route->has_middleware() && $route->check_middleware() );
				}
			);
		}
		return $routes;
	}

	/**
	 * Reorders the provided routes by dispatch priority.
	 *
	 * @param array $routes  The routes to reorder.
	 * @param array $options The options for filtering and reordering the routes.
	 *
	 * @return array The reordered routes.
	 */
	public static function order_routes_by_priority( $routes, $options ) {
		if ( 'priority' === $options['order'] ) {
			usort(
				$routes,
				/**
				 * Reorders the routes by their inclusion priority.
				 *
				 * @param Route $route_1 The first route to compare priority with.
				 * @param Route $route_2 The second route to compare priority with.
				 *
				 * @return bool Whether the route should be reordered.
				 */
				function ( $route_1, $route_2 ) use ( $options ) {
					return 'DESC' === $options['order_by'] ?
						$route_1->get_priority() < $route_2->get_priority() :
						$route_1->get_priority() > $route_2->get_priority();
				}
			);
		}
		return $routes;
	}

}
