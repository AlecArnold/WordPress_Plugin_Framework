<?php

namespace Plugin_Name\Core;

/**
 * Handles details associated with the session request.
 *
 * @package Plugin_Name
 */
class Request {

	/**
	 * Retrieves the current request method for the users session.
	 *
	 * @return string The current request method.
	 */
	public static function get_method() {
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'ANY';
	}

	/**
	 * Retrieve the current URL path for the users session.
	 *
	 * @return string The requested URL path.
	 */
	public static function get_url_path() {
		return isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
	}

}
