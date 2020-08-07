<?php
/**
 * Includes the class for managing an individual page routes.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Module\Page_Routing;

use Plugin_Name\Core\Route;
use Plugin_Name\Library\Callable_Reference_Reflector;
use function Plugin_Name\Functions\Array_Utils\array_cast;
use function Plugin_Name\Functions\Array_Utils\array_column_keep_keys;
use function Plugin_Name\Functions\Array_Utils\array_replace_matches;
use function Plugin_Name\Functions\Array_Utils\array_validate_items;

/**
 * Handles individual page routes within this plugin.
 */
class Page_Route extends Route {

	/**
	 * Stores the route query vars.
	 *
	 * @var array The route query vars.
	 */
	protected $query_vars = array();

	/**
	 * Stores whether the route query_vars value have been set.
	 *
	 * @var bool Whether the route query_vars has been set.
	 */
	protected $are_query_vars_prepared = false;

	/** =====================================================================
	 * Query Vars
	 * ---------------------------------------------------------------------- */

	/**
	 * The variables to set as WordPress's query_vars when the route is matched.
	 *
	 * @param array $query_vars  The route query vars.
	 * @param bool  $is_prepared Whether the provided query vars have been prepared.
	 */
	public function set_query_vars( $query_vars, $is_prepared = false ) {
		$this->query_vars              = $query_vars;
		$this->are_query_vars_prepared = $is_prepared;
	}

	/**
	 * Prepare the query vars to include all required parameters.
	 *
	 * @param array $query_vars The query vars to prepare.
	 *
	 * @return array The query vars prepared to include all required parameters.
	 */
	public function prepare_query_vars( $query_vars ) {
		return array_map(
		/**
		 * Prepares query var array items.
		 *
		 * @param array $query_var The query var to prepare.
		 *
		 * @return array The set version of the query var array.
		 */
			function( $query_var ) {
				$query_var               = array_merge( $this->get_default_query_var_options(), array_cast( $query_var, 'value' ) );
				$query_var['middleware'] = new Callable_Reference_Reflector( $query_var['middleware'] );
				return $query_var;
			},
			$query_vars
		);
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
			$prepared_query_vars = $this->prepare_query_vars( $this->get_route_option( 'query_vars', $this->query_vars ) );
			$this->set_query_vars( $prepared_query_vars, true );
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
				return ! $query_var['middleware']->has_callable_reference() || ( is_callable( $callable ) && call_user_func_array( $callable, array( $this, $query_var['value'] ) ) );
			},
			$this->populate_query_vars_values_using_url_path( $url_path )
		);
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
		return $this->has_callback() ? call_user_func_array( $this->get_callback()->get_callable(), array( $this, $this->get_query_vars_for_url_path( $url_path ) ) ) : null;
	}

}
