<?php

namespace Plugin_Name\Core;

use WP;

/**
 * Handles all of the routes within this plugin.
 *
 * @package Plugin_Name
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
	public static function __constructStatic() {
		self::add_routes( Config::get_config( 'routes' ) );
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
	 * Adds a single route to this this plugin.
	 *
	 * @param string      $route_id The ID for the new route.
	 * @param array|Route $route    The route to add.
	 */
	public static function add_route( $route_id, $route ) {
		self::$routes[ $route_id ] = is_a( $route, 'Route' ) ? $route : new Route( $route ); // todo test
	}

	/**
	 * Retrieves routes defined within the system which can also be filtered and ordered using options.
	 *
	 * @param array $options The options to use to filter and order the routes.
	 *
	 * @return array An array containing all of the matching routes.
	 */
	public static function get_routes( $options = array() ) {
		$routes = self::$routes;

		// Filter by method.
		if ( isset( $options['method'] ) ) {
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
					return in_array( $options['method'], $route->get_method(), true );
				}
			);
		}

		// Filter by whether the route has a URL path.
		if ( isset( $options['has_url_path'] ) ) {
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
					return $options['has_url_path'] === $route->has_url_path_regex();
				}
			);
		}

		// Filter by URL path.
		if ( isset( $options['url_path'] ) ) {
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

		// Filter by middleware.
		if ( isset( $options['validate_middleware'] ) && $options['validate_middleware'] ) {
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

		// Order the routes.
		if ( isset( $options['order'] ) ) {
			$order_by = isset( $options['order_by'] ) ? $options['order_by'] : 'DESC';

			// Order by URL path score.
			if ( 'url_path_score' === $options['order'] && isset( $options['url_path'] ) ) {
				usort(
					$routes,
					/**
					 * Determines whether a route should be filtered or not.
					 *
					 * @param Route $route_1 The route to compare URL path scores with.
					 * @param Route $route_2 The route to compare URL path scores with.
					 *
					 * @return bool Whether the route should be filtered out or not.
					 */
					function ( $route_1, $route_2 ) use ( $order_by, $options ) {
						return 'DESC' === $order_by ?
							$route_1->get_url_path_score( $options['url_path'] ) < $route_2->get_url_path_score( $options['url_path'] ) :
							$route_1->get_url_path_score( $options['url_path'] ) > $route_2->get_url_path_score( $options['url_path'] );
					}
				);
			}
		}
		return $routes;
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
	 * Hooks the events used to dispatch the routes.
	 */
	public static function hook_plugin_routes() {
		add_action( 'parse_request', array( self::class, 'wp_action_route_page' ) );
		add_action( 'parse_request', array( self::class, 'wp_action_route_session' ) );
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
	 * Triggers all of the matching routes that are for pages.
	 */
	public static function wp_action_route_session() {
		$session_routes = self::get_routes(
			array(
				'has_url_path'        => false,
				'method'              => Request::get_method(),
				'validate_middleware' => true,
			)
		);

		// Run each method specific route.
		foreach ( $session_routes as $session_route ) {
			$session_route->dispatch();
		}
	}

}
