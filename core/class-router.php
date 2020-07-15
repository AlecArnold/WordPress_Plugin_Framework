<?php

namespace Plugin_Name\Core;

/**
 *
 *
 * @package Plugin_Name
 */
class Router {

	/**
	 *
	 *
	 * @var array
	 */
	protected static $routes = array();

	/**
	 *
	 */
	public static function __constructStatic() {
		self::add_routes( Config::get_config( 'routes' ) );
	}

	/**
	 *
	 */
	public static function add_routes( $routes ) {
		foreach ( $routes as $route_id => $route ) {
			self::add_route( $route_id, $route );
		}
	}

	/**
	 *
	 */
	public static function add_route( $route_id, $route ) {
		self::$routes[ $route_id ] = is_array( $route ) ? new Route( $route ) : $route;
	}

	/**
	 *
	 */
	public static function get_routes( $options = array() ) {
		$routes = self::$routes;

		//
		if ( isset( $options['method'] ) ) {
			$routes = array_filter(
				$routes,
				function ( $route ) use ( $options ) {
					return in_array( $options['method'], $route->get_method(), true );
				}
			);
		}

		//
		if ( isset( $options['has_url_path'] ) ) {
			$routes = array_filter(
				$routes,
				function ( $route ) use ( $options ) {
					return $options['has_url_path'] === $route->has_url_path_regex();
				}
			);
		}

		//
		if ( isset( $options['url_path'] ) ) {
			$routes = array_filter(
				$routes,
				function ( $route ) use ( $options ) {
					return $route->has_url_path_regex() && $route->is_url_path_match( $options['url_path'] );
				}
			);
		}

		//
		if ( isset( $options['validate_middleware'] ) && $options['validate_middleware'] ) {
			$routes = array_filter(
				$routes,
				function ( $route ) {
					return ! $route->has_middleware() || ( $route->has_middleware() && $route->check_middleware() );
				}
			);
		}

		//
		if ( isset( $options['order'] ) ) {
			$order_by = isset( $options['order_by'] ) ? $options['order_by'] : 'DESC';

			//
			if ( 'url_path_score' === $options['order'] && isset( $options['url_path'] ) ) {
				usort(
					$routes,
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
	 *
	 */
	public static function get_route( $options = array() ) {
		$routes = self::get_routes( $options );
		return isset( $routes[0] ) ? $routes[0] : null;
	}

	/**
	 *
	 */
	public static function get_route_by_id( $route_id ) {
		$routes = self::get_routes();
		return isset( $routes[ $route_id ] ) ? $routes[ $route_id ] : null;
	}

	/**
	 *
	 */
	public static function hook_plugin_routes() {
		add_action( 'parse_request', array( self::class, 'wp_action_route_page' ) );
		add_action( 'parse_request', array( self::class, 'wp_action_route_session' ) );
	}

	/**
	 *
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
			$wp->matched_query = $page_route->get_url_path_rewrite();
			$page_route->dispatch();
		}
	}

	/**
	 *
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
