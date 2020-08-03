<?php
/**
 * Includes the class for managing an individual route.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use Plugin_Name\Library\Callable_Reference_Reflector;
use function Plugin_Name\Functions\Array_Utils\array_build_traversable_path;
use function Plugin_Name\Functions\Array_Utils\array_traverse;
use function Plugin_Name\Functions\Array_Utils\array_validate_items;

/**
 * Handles individual routes within this plugin.
 */
class Route {

	/**
	 * Stores an array of all the options for this route.
	 *
	 * @var array An array of all the options for this route.
	 */
	protected $route_options = array();

	/**
	 * Stores the route URL path regular expression.
	 *
	 * @var string The route URL path regular expression.
	 */
	protected $url_path_regex;

	/**
	 * Stores whether the URL path regex has been set.
	 *
	 * @var bool Whether the URL path regex has been set.
	 */
	protected $is_url_path_regex_prepared = false;

	/**
	 * Stores the route method/s.
	 *
	 * @var array|string The route method/s.
	 */
	protected $method = 'any';

	/**
	 * Stores whether the route method value has been set.
	 *
	 * @var bool Whether the route method has been set.
	 */
	protected $is_method_prepared = false;

	/**
	 * Stores the route middleware.
	 *
	 * @var array|string|Callable_Reference_Reflector The route middleware.
	 */
	protected $middleware;

	/**
	 * Stores whether the route middleware value has been set.
	 *
	 * @var bool Whether the route middleware has been set.
	 */
	protected $is_middleware_prepared = false;

	/**
	 * Stores the priority for this route to be dispatched.
	 *
	 * @var int The priority for this route to be dispatched.
	 */
	protected $priority = 10;

	/**
	 * Stores whether the route priority has been set.
	 *
	 * @var bool Whether the route priority has been set.
	 */
	protected $is_priority_set = false;

	/**
	 * Stores the route callback.
	 *
	 * @var array|string|Callable_Reference_Reflector The route callback.
	 */
	protected $callback;

	/**
	 * Stores whether the route callback reflector has been set.
	 *
	 * @var bool Whether the route callback reflector has been set.
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
	protected function set_route_options( $route_options ) {
		$this->route_options = $route_options;
	}

	/**
	 * Retrieves an array of all the options for this route.
	 *
	 * @return array An array of all the options for this route.
	 */
	public function get_route_options() {
		return $this->route_options;
	}

	/**
	 * Retrieves an individual option for this route.
	 *
	 * @param string $dot_path The dot path to the desired route option.
	 * @param mixed  $default  The default value to return when there isn't a value set on this route.
	 *
	 * @return mixed The value of a specific option for this route.
	 */
	public function get_route_option( $dot_path, $default = null ) {
		return array_traverse( $this->get_route_options(), array_build_traversable_path( $dot_path ), $default );
	}

	/** =====================================================================
	 * Regex
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the route URL path regular expression.
	 *
	 * @param string $url_path_regex    The route URL path regular expression.
	 * @param bool   $is_prepared Whether the provided URL path regular expression has been prepared.
	 */
	public function set_url_path_regex( $url_path_regex, $is_prepared = false ) {
		$this->url_path_regex             = $url_path_regex;
		$this->is_url_path_regex_prepared = $is_prepared;
	}

	/**
	 * Prepares the route URL path regular expression.
	 *
	 * @param string $url_path_regex The route URL path regular expression that needs to be prepared.
	 *
	 * @return string The prepared route URL path regular expression.
	 */
	public function prepare_url_path_regex( $url_path_regex ) {
		return '{' . $url_path_regex . '}';
	}

	/**
	 * Determine whether this route has a URL path regular expression.
	 *
	 * @return bool Whether this route has a URL path regular expression.
	 */
	public function has_url_path_regex() {
		return ! empty( $this->get_url_path_regex() );
	}

	/**
	 * Retrieves the route URL path regular expression.
	 *
	 * @return string The route URL path regular expression.
	 */
	public function get_url_path_regex() {
		if ( ! $this->is_url_path_regex_prepared ) {
			$prepared_url_path_regex = $this->prepare_url_path_regex( $this->get_route_option( 'regex' ) );
			$this->set_url_path_regex( $prepared_url_path_regex, true );
		}
		return $this->url_path_regex;
	}

	/** =====================================================================
	 * Methods
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the route method/s.
	 *
	 * @param array|string $method      The route method/s.
	 * @param bool         $is_prepared Whether the provided method has been prepared.
	 */
	public function set_method( $method, $is_prepared = false ) {
		$this->method             = $method;
		$this->is_method_prepared = $is_prepared;
	}

	/**
	 * Prepares the route method/s.
	 *
	 * @param array|string $method The method/s that needs to be prepared.
	 *
	 * @return array The prepared method/s.
	 */
	public function prepare_method( $method ) {
		return array_map( 'strtoupper', (array) $method );
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
			$prepared_method = $this->prepare_method( $this->get_route_option( 'method' ) );
			$this->set_method( $prepared_method, true );
		}
		return $this->method;
	}

	/** =====================================================================
	 * Middleware
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the route middleware.
	 *
	 * @param array|string $middleware  The route middleware.
	 * @param bool         $is_prepared Whether the provided middleware has been prepared.
	 */
	public function set_middleware( $middleware, $is_prepared = false ) {
		$this->middleware             = $middleware;
		$this->is_middleware_prepared = $is_prepared;
	}

	/**
	 * Prepares the route middleware.
	 *
	 * @param array $middleware The middleware that needs to be prepared.
	 *
	 * @return array The prepared middleware.
	 */
	public function prepare_middleware( $middleware ) {
		return array_map(
			/**
			 * Wraps the middleware within a `Callable_Reference_Reflector` object.
			 *
			 * @param array|string $middleware The middleware to prepare.
			 *
			 * @return Callable_Reference_Reflector The prepared middleware.
			 */
			function( $middleware ) {
				return new Callable_Reference_Reflector( $middleware );
			},
			(array) $middleware
		);
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
	 * @return array An array with all of the route middleware.
	 */
	public function get_middleware() {
		if ( ! $this->is_middleware_prepared ) {
			$prepared_middleware = $this->prepare_middleware( $this->get_route_option( 'middleware' ) );
			$this->set_middleware( $prepared_middleware, true );
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
	 * Priority
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the route dispatch priority.
	 *
	 * @param int $priority The route dispatch priority.
	 */
	public function set_priority( $priority ) {
		$this->priority        = $priority;
		$this->is_priority_set = true;
	}

	/**
	 * Retrieves the priority for this route to be dispatched.
	 *
	 * @return int The priority for this route to be dispatched.
	 */
	public function get_priority() {
		if ( ! $this->is_priority_set ) {
			$this->set_priority( $this->get_route_option( 'priority', 10 ) );
		}
		return $this->priority;
	}

	/** =====================================================================
	 * Callback
	 * ---------------------------------------------------------------------- */

	/**
	 * Sets the callback for when this route is matched.
	 *
	 * @param array|string $callback    The route callback.
	 * @param bool         $is_prepared Whether the provided callback has been prepared.
	 */
	public function set_callback( $callback, $is_prepared = false ) {
		$this->callback             = $callback;
		$this->is_callback_prepared = $is_prepared;
	}

	/**
	 * Prepare the callback by wrapping it within a `Callable_Reference_Reflector` object.
	 *
	 * @param array|string $callback The callback that needs to be prepared.
	 *
	 * @return Callable_Reference_Reflector The prepared callback.
	 */
	public function prepare_callback( $callback ) {
		return new Callable_Reference_Reflector( $callback );
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
			$prepared_callback = $this->prepare_callback( $this->get_route_option( 'callback' ) );
			$this->set_callback( $prepared_callback );
		}
		return $this->callback;
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Dispatches the route callback.
	 *
	 * @return mixed|null The callback response.
	 */
	public function dispatch() {
		return $this->has_callback() ? call_user_func_array( $this->get_callback()->get_callable(), array( $this ) ) : null;
	}

}
