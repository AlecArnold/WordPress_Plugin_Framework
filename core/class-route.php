<?php

namespace Plugin_Name\Core;

use Plugin_Name\Library\Callable_Reference_Reflector;
use function Plugin_Name\Functions\Array_Utils\array_cast;
use function Plugin_Name\Functions\Array_Utils\array_column_keep_keys;
use function Plugin_Name\Functions\Array_Utils\array_replace_matches;
use function Plugin_Name\Functions\Array_Utils\array_validate_items;

/**
 * Handles individual routes within this plugin.
 *
 * @package Plugin_Name
 */
class Route {

	/**
	 * Stores the route URL path regular expression.
	 *
	 * @var string The route URL path regular expression.
	 */
	protected $url_path_regex;

	/**
	 * Stores the route method/s.
	 *
	 * @var array|string The route method/s.
	 */
	protected $method = 'any';

	/**
	 * Stores whether the route method value has been prepared.
	 *
	 * @var bool Whether the route method has been prepared.
	 */
	protected $is_method_prepared = false;

	/**
	 * Stores the route middleware.
	 *
	 * @var string|array|Callable_Reference_Reflector The route middleware.
	 */
	protected $middleware;

	/**
	 * Stores whether the route middleware value has been prepared.
	 *
	 * @var bool Whether the route middleware has been prepared.
	 */
	protected $is_middleware_prepared = false;

	/**
	 * Stores the route query vars.
	 *
	 * @var array The route query vars.
	 */
	protected $query_vars = array();

	/**
	 * Stores whether the route query_vars value have been prepared.
	 *
	 * @var bool Whether the route query_vars has been prepared.
	 */
	protected $are_query_vars_prepared = false;

	/**
	 * Stores the route callback.
	 *
	 * @var string|array|Callable_Reference_Reflector The route callback.
	 */
	protected $callback;

	/**
	 * Stores whether the route callback reflector has been prepared.
	 *
	 * @var bool Whether the route callback reflector has been prepared.
	 */
	protected $is_callback_prepared = false;

	/**
	 * Handles the construction of the route object.
	 *
	 * @param array $route_options An array of options for the route.
	 */
	public function __construct( $route_options ) {
		$this->set_route_options( $route_options );
	}

	/**
	 * Sets the options for this route.
	 *
	 * @param array $route_options An array of options for the route.
	 */
	public function set_route_options( $route_options ) {

		// Sets the route URL path regular expression when required.
		if ( isset( $route_options['regex'] ) ) {
			$this->set_url_path_regex( $route_options['regex'] );
		}

		// Sets the route method when required.
		if ( isset( $route_options['method'] ) ) {
			$this->set_method( $route_options['method'] );
		}

		// Sets the route middleware when required.
		if ( isset( $route_options['middleware'] ) ) {
			$this->set_middleware( $route_options['middleware'] );
		}

		// Sets the route query vars when required.
		if ( isset( $route_options['query_vars'] ) ) {
			$this->set_query_vars( $route_options['query_vars'] );
		}

		// Sets the route callback when required.
		if ( isset( $route_options['callback'] ) ) {
			$this->set_callback( $route_options['callback'] );
		}

	}

	/** =====================================================================
	 * Regex
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the route URL path regular expression.
	 *
	 * @param string $url_path_regex The route URL path regular expression.
	 */
	public function set_url_path_regex( $url_path_regex ) {
		$this->url_path_regex = '{' . $url_path_regex . '}';
	}

	/**
	 * Determine whether this route has a URL path regular expression.
	 *
	 * @return bool Whether this route has a URL path regular expression.
	 */
	public function has_url_path_regex() {
		return ! empty( $this->url_path_regex );
	}

	/**
	 * Retrieves the route URL path regular expression.
	 *
	 * @return string The route URL path regular expression.
	 */
	public function get_url_path_regex() {
		return $this->url_path_regex;
	}

	/** =====================================================================
	 * Methods
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the route method/s.
	 *
	 * @param array|string $method The route method/s.
	 */
	public function set_method( $method ) {
		$this->method             = $method;
		$this->is_method_prepared = false;
	}

	/**
	 * Prepares the route method/s.
	 */
	public function prepare_method() {
		$this->method             = array_map( 'strtoupper', (array) $this->method );
		$this->is_method_prepared = true;
	}

	/**
	 * Determines whether this route has a method/s defined.
	 *
	 * @return bool Whether this route has a method/s defined.
	 */
	public function has_method() {
		return ! empty( $this->get_method() );
	}

	/**
	 * Retrieves the route method/s.
	 *
	 * @return array The route method/s.
	 */
	public function get_method() {
		if ( ! $this->is_method_prepared ) {
			$this->prepare_method();
		}
		return $this->method;
	}

	/** =====================================================================
	 * Middleware
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the route middleware.
	 *
	 * @param string|array $middleware The route middleware.
	 */
	public function set_middleware( $middleware ) {
		$this->middleware             = $middleware;
		$this->is_middleware_prepared = false;
	}

	/**
	 * Converts the provided middleware to be wrapped within a `Callable_Reference_Reflector` object.
	 */
	public function prepare_middleware() {
		$this->middleware = array_map(
			/**
			 * Wraps the middleware within a `Callable_Reference_Reflector` object.
			 *
			 * @param string|array $middleware The middleware to prepare.
			 *
			 * @return Callable_Reference_Reflector The prepared middleware.
			 */
			function( $middleware ) {
				return new Callable_Reference_Reflector( $middleware );
			},
			(array) $this->middleware
		);
		$this->is_middleware_prepared = true;
	}

	/**
	 * Determines whether this route has middleware.
	 *
	 * @return bool Whether this route has middleware.
	 */
	public function has_middleware() {
		return ! empty( $this->get_middleware() );
	}

	/**
	 * Retrieves the route middleware.
	 *
	 * @return Callable_Reference_Reflector The route middleware.
	 */
	public function get_middleware() {
		if ( ! $this->is_middleware_prepared ) {
			$this->prepare_middleware();
		}
		return $this->middleware;
	}

	/**
	 * Determines whether the route middleware is valid.
	 *
	 * @return bool Whether the route middleware is valid.
	 */
	public function check_middleware() {
		return array_validate_items(
			/**
			 * Determines whether the middleware passes.
			 *
			 * @param Callable_Reference_Reflector $middleware The middleware to check.
			 *
			 * @return bool Whether the middleware passes.
			 */
			function( $middleware ) {
				$callable = $middleware->get_callable();
				return is_callable( $callable ) && call_user_func_array( $callable, array( $this ) );
			},
			$this->get_middleware()
		);
	}

	/** =====================================================================
	 * Query Vars
	 * ---------------------------------------------------------------------- */

	/**
	 * The variables to set as WordPress's query_vars when the route is matched.
	 *
	 * @param array $query_vars The route query vars.
	 */
	public function set_query_vars( $query_vars ) {
		$this->query_vars              = $query_vars;
		$this->are_query_vars_prepared = false;
	}

	/**
	 * Prepare the query vars to include all required parameters.
	 */
	public function prepare_query_vars() {
		$this->query_vars = array_map(
			/**
			 * Prepares query var array items.
			 *
			 * @param array $query_var The query var to prepare.
			 *
			 * @return array The prepared version of teh query var array.
			 */
			function( $query_var ) {
				$query_var               = array_merge( $this->get_default_query_var_options(), array_cast( $query_var, 'value' ) );
				$query_var['middleware'] = new Callable_Reference_Reflector( $query_var['middleware'] );
				return $query_var;
			},
			$this->query_vars
		);
		$this->are_query_vars_prepared = true;
	}

	/**
	 * Determines whether this route has query vars.
	 *
	 * @return bool Whether the route has query vars.
	 */
	public function has_query_vars() {
		return ! empty( $this->get_query_vars() );
	}

	/**
	 * Retrieves all of the query vars for this route.
	 *
	 * @return array An array containing all of the query vars.
	 */
	public function get_query_vars() {
		if ( ! $this->are_query_vars_prepared ) {
			$this->prepare_query_vars();
		}
		return $this->query_vars;
	}

	/**
	 * Retrieves the default query var options.
	 *
	 * @return array An array containing all of the default query var options.
	 */
	public function get_default_query_var_options() {
		return array(
			'value'      => null,
			'middleware' => null,
		);
	}

	/**
	 * Determines whether the query var middleware is all valid.
	 *
	 * @param string $url_path The URL path to use when checking the query var middleware.
	 *
	 * @return bool Whether the query var middleware is all valid.
	 */
	public function check_query_vars_middleware( $url_path ) {
		return array_validate_items(
			/**
			 * Check the middleware for a query var.
			 *
			 * @param array $query_var The query var to check.
			 *
			 * @return bool Whether the query var middleware is valid.
			 */
			function( $query_var ) {
				$callable = $query_var['middleware']->get_callable();
				return ! $query_var['middleware']->has_callable_reference() || ( is_callable( $callable ) && call_user_func_array( $callable, array( $query_var['value'], $this ) ) );
			},
			$this->populate_query_vars_values_using_url_path( $url_path )
		);
	}

	/** =====================================================================
	 * Callback
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the callback for when this route is matched.
	 *
	 * @param string|array $callback The route callback.
	 */
	public function set_callback( $callback ) {
		$this->callback             = $callback;
		$this->is_callback_prepared = false;
	}

	/**
	 * Prepare the callback by wrapping it within a `Callable_Reference_Reflector` object.
	 */
	public function prepare_callback() {
		$this->callback             = new Callable_Reference_Reflector( $this->get_callback() );
		$this->is_callback_prepared = true;
	}

	/**
	 * Determines whether this route has a callback.
	 *
	 * @return bool Whether this route has a callback.
	 */
	public function has_callback() {
		return ! empty( $this->get_callback() );
	}

	/**
	 * Retrieves the callback for this route.
	 *
	 * @return Callable_Reference_Reflector The callback for this route.
	 */
	public function get_callback() {
		if ( ! $this->is_callback_prepared ) {
			$this->prepare_callback();
		}
		return $this->callback;
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Builds the WordPress rewrite for this route.
	 *
	 * @param string $url_path The URL path used to derive values from for the rewrite.
	 *
	 * @return string The rewrite for this route/URL path.
	 */
	public function get_url_path_rewrite( $url_path ) {
		return 'index.php?' . http_build_query( $this->get_query_vars_for_url_path( $url_path ) );
	}

	/**
	 * Retrieves a populated version of the query vars using a URL path to populate it.
	 *
	 * @param string $url_path The URL path used to derive values from for the query vars.
	 *
	 * @return array An array containing all of the query vars populated with values from the provided URL path.
	 */
	public function populate_query_vars_values_using_url_path( $url_path ) {
		return array_replace_matches( $this->get_query_vars(), $this->get_url_path_regex(), $url_path );
	}

	/**
	 * Retrieve all of the query var values for a given URL path.
	 *
	 * @param string $url_path The URL path used to retrieve values from.
	 *
	 * @return array An array of all the query var values.
	 */
	public function get_query_vars_for_url_path( $url_path ) {
		return array_filter( array_column_keep_keys( $this->populate_query_vars_values_using_url_path( $url_path ), 'value' ) );
	}

	/**
	 * Check whether a provided URL path matches the URL path regular expression for this route.
	 *
	 * @param string $url_path The URL path used to compare with this route.
	 *
	 * @return bool Whether the provided URL path matches this route.
	 */
	public function is_url_path_match( $url_path ) {
		return preg_match( $this->get_url_path_regex(), $url_path ) && $this->check_query_vars_middleware( $url_path );
	}

	/**
	 * Determine the best fit score out of 100(where 100 is a perfect score) for the provided URL path. This is used as
	 * a tie breaker for page routes that both a given URL.
	 *
	 * @param string $url_path The URL path to generate a score for.
	 *
	 * @return float The score for the given URL path.
	 */
	public function get_url_path_score( $url_path ) {
		preg_match( $this->get_url_path_regex(), $url_path, $matches );
		$total_matches = count( $matches );
		return $total_matches > 0 ? ceil( 100 / $total_matches ) : 0;
	}

	/**
	 * Dispatches the route callback.
	 *
	 * @param null|string $url_path The URL path to convert to arguments for the callback.
	 *
	 * @return mixed|null The callback response.
	 */
	public function dispatch( $url_path = null ) {
		return $this->has_callback() ? call_user_func_array( $this->get_callback()->get_callable(), $this->get_query_vars_for_url_path( $url_path ) ) : null;
	}

}
