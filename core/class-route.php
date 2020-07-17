<?php

namespace Plugin_Name\Core;

use Plugin_Name\Library\Callable_Reference_Reflector;
use function Plugin_Name\Functions\Array_Utils\array_cast;
use function Plugin_Name\Functions\Array_Utils\array_replace_matches;
use function Plugin_Name\Functions\Array_Utils\array_validate_items;

/**
 *
 *
 * @package Plugin_Name
 */
class Route {

	/**
	 *
	 */
	protected $url_path_regex;

	/**
	 *
	 */
	protected $method = 'any';

	/**
	 *
	 */
	protected $is_method_prepared = false;

	/**
	 *
	 */
	protected $middleware;

	/**
	 *
	 */
	protected $is_middleware_prepared = false;

	/**
	 *
	 */
	protected $query_vars = array();

	/**
	 *
	 */
	protected $are_query_vars_prepared = false;

	/**
	 *
	 */
	protected $callback;

	/**
	 *
	 */
	protected $is_callback_reflector_prepared = false;

	/**
	 *
	 */
	protected $callback_reflector;

	/**
	 *
	 */
	public function __construct( $route_options ) {
		$this->set_route_options( $route_options );
	}

	/**
	 *
	 */
	public function set_route_options( $route_options ) {

		//
		if ( isset( $route_options['regex'] ) ) {
			$this->set_url_path_regex( $route_options['regex'] );
		}

		//
		if ( isset( $route_options['method'] ) ) {
			$this->set_method( $route_options['method'] );
		}

		//
		if ( isset( $route_options['middleware'] ) ) {
			$this->set_middleware( $route_options['middleware'] );
		}

		//
		if ( isset( $route_options['query_vars'] ) ) {
			$this->set_query_vars( $route_options['query_vars'] );
		}

		//
		if ( isset( $route_options['callback'] ) ) {
			$this->set_callback( $route_options['callback'] );
		}

	}

	/** =====================================================================
	 * Regex
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function set_url_path_regex( $url_path_regex ) {
		$this->url_path_regex = '{' . $url_path_regex . '}';
	}

	/**
	 *
	 */
	public function has_url_path_regex() {
		return ! empty( $this->url_path_regex );
	}

	/**
	 *
	 */
	public function get_url_path_regex() {
		return $this->url_path_regex;
	}

	/** =====================================================================
	 * Methods
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function set_method( $method ) {
		$this->method             = $method;
		$this->is_method_prepared = false;
	}

	/**
	 *
	 */
	public function prepare_method() {
		$this->method             = array_map( 'strtoupper', (array) $this->method );
		$this->is_method_prepared = true;
	}

	/**
	 *
	 */
	public function has_method() {
		return ! empty( $this->get_method() );
	}

	/**
	 *
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
	 *
	 */
	public function set_middleware( $middleware ) {
		$this->middleware             = $middleware;
		$this->is_middleware_prepared = false;
	}

	/**
	 *
	 */
	public function prepare_middleware() {
		$this->middleware             = array_map(
			function( $middleware ) {
				return new Callable_Reference_Reflector( $middleware );
			},
			(array) $this->middleware
		);
		$this->is_middleware_prepared = true;
	}

	/**
	 *
	 */
	public function has_middleware() {
		return ! empty( $this->get_middleware() );
	}

	/**
	 *
	 */
	public function get_middleware() {
		if ( ! $this->is_middleware_prepared ) {
			$this->prepare_middleware();
		}
		return $this->middleware;
	}

	/**
	 *
	 */
	public function check_middleware() {
		return array_validate_items(
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
	 *
	 */
	public function set_query_vars( $query_vars ) {
		$this->query_vars              = $query_vars;
		$this->are_query_vars_prepared = false;
	}

	/**
	 *
	 */
	public function prepare_query_vars() {
		$this->query_vars              = array_map(
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
	 *
	 */
	public function has_query_vars() {
		return ! empty( $this->get_query_vars() );
	}

	/**
	 *
	 */
	public function get_query_vars() {
		if ( ! $this->are_query_vars_prepared ) {
			$this->prepare_query_vars();
		}
		return $this->query_vars;
	}

	/**
	 *
	 */
	public function get_default_query_var_options() {
		return array(
			'value'      => null,
			'middleware' => null,
		);
	}

	/**
	 *
	 */
	public function check_query_vars_middleware( $url_path ) {
		return array_validate_items(
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
	 *
	 */
	public function set_callback( $callback ) {
		$this->callback                       = $callback;
		$this->is_callback_reflector_prepared = false;
	}

	/**
	 *
	 */
	public function has_callback() {
		return ! empty( $this->get_callback() );
	}

	/**
	 *
	 */
	public function get_callback() {
		return $this->callback;
	}

	/**
	 *
	 */
	public function prepare_callback_reflector() {
		$this->callback_reflector             = new Callable_Reference_Reflector( $this->get_callback() );
		$this->is_callback_reflector_prepared = true;
	}

	/**
	 *
	 */
	public function has_callback_reflector() {
		return ! empty( $this->get_callback_reflector() );
	}

	/**
	 *
	 */
	public function get_callback_reflector() {
		if ( ! $this->is_callback_reflector_prepared ) {
			$this->prepare_callback_reflector();
		}
		return $this->callback_reflector;
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function get_url_path_rewrite() {
		return 'index.php?' . http_build_query( array_filter( array_column( $this->get_query_vars(), 'value' ) ) );
	}

	/**
	 *
	 */
	public function populate_query_vars_values_using_url_path( $url_path ) {
		return array_replace_matches( $this->get_query_vars(), $this->get_url_path_regex(), $url_path );
	}

	/**
	 *
	 */
	public function get_query_vars_for_url_path( $url_path ) {
		return array_map(
			function ( $query_var ) {
				return $query_var['value'];
			},
			$this->populate_query_vars_values_using_url_path( $url_path )
		);
	}

	/**
	 *
	 */
	public function is_url_path_match( $url_path ) {
		return preg_match( $this->get_url_path_regex(), $url_path ) && $this->check_query_vars_middleware( $url_path );
	}

	/**
	 *
	 */
	public function get_url_path_score( $url_path ) {
		preg_match( $this->get_url_path_regex(), $url_path, $matches );
		$total_matches = count( $matches );
		return $total_matches > 0 ? ceil( 100 / $total_matches ) : 0;
	}

	/**
	 *
	 */
	public function dispatch( $url_path = null ) {
		return $this->has_callback_reflector() ? call_user_func_array( $this->get_callback_reflector()->get_callable(), $this->get_query_vars_for_url_path( $url_path ) ) : null;
	}

}
