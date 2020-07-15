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
	protected $controller;

	/**
	 *
	 */
	protected $is_controller_reflector_prepared = false;

	/**
	 *
	 */
	protected $controller_reflector;

	/**
	 *
	 */
	protected $model;

	/**
	 *
	 */
	protected $is_model_reflector_prepared = false;

	/**
	 *
	 */
	protected $model_reflector;

	/**
	 *
	 */
	protected $view;

	/**
	 *
	 */
	protected $is_view_reflector_prepared = false;

	/**
	 *
	 */
	protected $view_reflector;

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
		if ( isset( $route_options['controller'] ) ) {
			$this->set_controller( $route_options['controller'] );
		}

		//
		if ( isset( $route_options['model'] ) ) {
			$this->set_model( $route_options['model'] );
		}

		//
		if ( isset( $route_options['view'] ) ) {
			$this->set_view( $route_options['view'] );
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
				return ! $query_var['middleware']->has_callable_reference() || ( is_callable( $callable ) && call_user_func_array( $callable, array( $this, $query_var['value'] ) ) );
			},
			$this->populate_query_vars_values_using_url_path( $url_path )
		);
	}

	/** =====================================================================
	 * Controller
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function set_controller( $controller ) {
		$this->controller                       = $controller;
		$this->is_controller_reflector_prepared = false;
	}

	/**
	 *
	 */
	public function has_controller() {
		return ! empty( $this->get_controller() );
	}

	/**
	 *
	 */
	public function get_controller() {
		return $this->controller;
	}

	/**
	 *
	 */
	public function prepare_controller_reflector() {
		$this->controller_reflector             = new Callable_Reference_Reflector( $this->get_controller() );
		$this->is_controller_reflector_prepared = true;
	}

	/**
	 *
	 */
	public function has_controller_reflector() {
		return ! empty( $this->get_controller_reflector() );
	}

	/**
	 *
	 */
	public function get_controller_reflector() {
		if ( ! $this->is_controller_reflector_prepared ) {
			$this->prepare_controller_reflector();
		}
		return $this->controller_reflector;
	}

	/**
	 *
	 */
	public function has_controller_class() {
		return $this->get_controller_reflector()->is_class_reference();
	}

	/**
	 *
	 */
	public function get_controller_class_name() {
		return $this->get_controller_reflector()->get_class_name();
	}

	/**
	 *
	 */
	public function has_controller_method() {
		return $this->get_controller_reflector()->has_class_method();
	}

	/**
	 *
	 */
	public function get_controller_method() {
		return $this->get_controller_reflector()->get_class_method();
	}

	/**
	 *
	 */
	public function get_controller_instance() {
		return $this->get_controller_reflector()->get_class_instance();
	}

	/** =====================================================================
	 * Model
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function set_model( $model ) {
		$this->model                       = $model;
		$this->is_model_reflector_prepared = false;
	}

	/**
	 *
	 */
	public function has_model() {
		return ! empty( $this->get_model() );
	}

	/**
	 *
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 *
	 */
	public function prepare_model_reflector() {
		$this->model_reflector             = new Callable_Reference_Reflector( $this->get_model() );
		$this->is_model_reflector_prepared = true;
	}

	/**
	 *
	 */
	public function has_model_reflector() {
		return ! empty( $this->get_model_reflector() );
	}

	/**
	 *
	 */
	public function get_model_reflector() {
		if ( ! $this->is_model_reflector_prepared ) {
			$this->prepare_model_reflector();
		}
		return $this->model_reflector;
	}

	/**
	 *
	 */
	public function has_model_class() {
		return $this->get_model_reflector()->is_class_reference();
	}

	/**
	 *
	 */
	public function get_model_class_name() {
		return $this->get_model_reflector()->get_class_name();
	}

	/**
	 *
	 */
	public function has_model_method() {
		return $this->get_model_reflector()->has_class_method();
	}

	/**
	 *
	 */
	public function get_model_method() {
		return $this->get_model_reflector()->get_class_method();
	}

	/**
	 *
	 */
	public function get_model_instance() {
		return $this->get_model_reflector()->get_class_instance();
	}

	/** =====================================================================
	 * View
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function set_view( $view ) {
		$this->view                       = $view;
		$this->is_view_reflector_prepared = false;
	}

	/**
	 *
	 */
	public function has_view() {
		return ! empty( $this->get_view() );
	}

	/**
	 *
	 */
	public function get_view() {
		return $this->view;
	}

	/**
	 *
	 */
	public function prepare_view_reflector() {
		$this->view_reflector             = new Callable_Reference_Reflector( $this->get_view() );
		$this->is_view_reflector_prepared = true;
	}

	/**
	 *
	 */
	public function has_view_reflector() {
		return ! empty( $this->get_view_reflector() );
	}

	/**
	 *
	 */
	public function get_view_reflector() {
		if ( ! $this->is_view_reflector_prepared ) {
			$this->prepare_view_reflector();
		}
		return $this->view_reflector;
	}

	/**
	 *
	 */
	public function has_view_class() {
		return $this->get_view_reflector()->is_class_reference();
	}

	/**
	 *
	 */
	public function get_view_class_name() {
		return $this->get_view_reflector()->get_class_name();
	}

	/**
	 *
	 */
	public function has_view_method() {
		return $this->get_view_reflector()->has_class_method();
	}

	/**
	 *
	 */
	public function get_view_method() {
		return $this->get_view_reflector()->get_class_method();
	}

	/**
	 *
	 */
	public function get_view_instance() {
		return $this->get_view_reflector()->get_class_instance();
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
	public function dispatch() {

		//
		if ( $this->has_controller_class() ) {
			$controller_instance = $this->get_controller_instance();

			//
			if ( $this->has_model() ) {
				$controller_instance->set_model( $this->get_model_instance() );
			}

			//
			if ( $this->has_view() ) {
				$controller_instance->set_view( $this->get_view_instance() );
			}

			//
			if ( $this->has_controller_method() ) {
				$controller_method = $this->get_controller_method();
				$controller_instance->$controller_method();
			}
		} elseif ( $this->has_model_class() ) {
			$model_instance = $this->get_model_instance();

			//
			if ( $this->has_view() ) {
				$model_instance->set_view( $this->get_view_instance() );
			}

			//
			if ( $this->has_model_method() ) {
				$model_method = $this->get_model_method();
				$model_instance->$model_method();
			}
		} elseif ( $this->has_view_class() ) {
			$view_instance = $this->get_view_instance();

			if ( $this->has_view_method() ) {
				$view_method = $this->get_view_method();
				$view_instance->$view_method();
			}
		}
	}

}
